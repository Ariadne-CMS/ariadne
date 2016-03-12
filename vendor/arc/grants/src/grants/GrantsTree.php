<?php

/*
 * This file is part of the Ariadne Component Library.
 *
 * (c) Muze <info@muze.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace arc\grants;

/**
 * Class GrantsTree
 * @package arc\grants
 */
final class GrantsTree
{
    use \arc\traits\Proxy {
        \arc\traits\Proxy::__construct as private ProxyConstruct;
    }

    private $tree = null;
    private $user   = null;
    private $groups = array();

    /**
     * @param \arc\tree\NamedNode $tree The tree storage for event listeners.
     * @param string $user
     * @param array $groups
     */
    public function __construct( $tree, $user, $groups = array() )
    {
        $this->ProxyConstruct( $tree );
        $this->tree = $tree;
        $this->user = $user;
        $this->groups = $groups;
    }

    /**
     * Change to a different node
     * @param $path
     * @return GrantsTree
     */
    public function cd($path)
    {
        return new GrantsTree( $this->tree->cd( $path ), $this->user );
    }

    /**
     * Switch default user to check and set grants.
     * @param $user
     * @param array $groups
     * @return GrantsTree this
     */
    public function switchUser( $user, $groups = [] )
    {
        return new GrantsTree( $this->tree, $user, $groups );
    }

    /**
     * Set new grants for the current user.
     * @param null $grants
     * @return GrantsTree this
     */
    public function set($grants = null)
    {
        if ( isset( $grants ) ) {
            if ( !isset($this->tree->nodeValue['users'])) {
                $this->tree->nodeValue['users'] = [];
            }
            $this->tree->nodeValue['users'][$this->user ] = ' ' . trim( $grants ) . ' ';
        } else {
            unset( $this->tree->nodeValue['users'][$this->user ] );
        }
        return $this;
    }


    /**
     * Deprecated. Use set() instead.
     * @param null $grants
     */
    public function setUserGrants( $grants = null ){
        $this->set($grants);
    }

    /**
     * Deprecated. Use setForGroup instead.
     * @param $group
     * @param null $grants
     */
    public function setGroupGrants( $group, $grants = null ){
        $this->setForGroup($group, $grants);
    }

    /**
     * Set new grants for the given user.
     * @param $user
     * @param null $grants
     * @return GrantsTree this
     */
    public function setForUser( $user, $grants = null ) {
        $this->switchUser($user)->set($grants);
        return $this;
    }

    /**
     * Set new grants for the given group.
     * @param $group
     * @param null $grants
     * @return GrantsTree this
     */
    public function setForGroup( $group, $grants = null ) {
        if ( isset( $grants ) ) {
            if ( !isset($this->tree->nodeValue['groups'])) {
                $this->tree->nodeValue['groups'] = [];
            }
            $this->tree->nodeValue['groups'][$group ] = ' ' . trim( $grants ) . ' ';
        } else {
            unset( $this->tree->nodeValue['groups'][$group ] );
        }
        return $this;
    }

    /**
     * Return the grants for the current user.
     * @return mixed
     */
    public function grants() {
        return \arc\hash::get('/users/'.$this->user.'/', $this->tree->nodeValue, '');
    }

    /**
     * Return the grants for a specific user.
     * @param $user
     * @return mixed
     */
    public function grantsForUser($user, $groups = []) {
        return $this->switchUser($user, $groups)->grants();
    }

    /**
     * Returns an array with the grants for all users.
     * @return array
     */
    public function grantsForAllUsers() {
        return \arc\hash::get('/users/', $this->tree->nodeValue, array() );
    }

    /**
     * Return the grants for a specific group.
     * @param $group
     * @return mixed
     */
    public function grantsForGroup($group) {
        return \arc\hash::get("/groups/$group/", $this->tree->nodeValue, '');
    }

    /**
     * Returns an array with the grants for all groups.
     * @return array
     */
    public function grantsForAllGroups() {
        return \arc\hash::get('/groups/', $this->tree->nodeValue, array() );
    }

    /**
     * @param $grant
     * @return bool
     */
    public function check($grant)
    {
        // uses strpos since it is twice as fast as preg_match for the most common cases
        $grants = $this->fetchGrants();

        if ( strpos( $grants, $grant.' ' ) === false ) { // exit early if no possible match is found
            return false;
        }

        return ( strpos( $grants, ' '.$grant.' ') !== false
            || strpos( $grants, ' ='.$grant.' ') !== false );
    }

    /**
     * Check grant for a specific user.
     * @param $user
     * @param $grant
     * @return bool
     */
    public function checkForUser($grant, $user, $groups = []) {
        return $this->switchUser($user, $groups)->check($grant);
    }

    /**
     * @return string
     */
    private function fetchGrants()
    {
        $user = $this->user;
        $groups = array_fill_keys( $this->groups, 1 );
        $grants = (string) \arc\tree::dive(
            $this->tree,
            function ($node) use ($user) {
                if ( isset( $node->nodeValue['users'][$user] ) ) {
                    return $node->nodeValue['users'][$user];
                }
            },
            function ($node, $grants) use (&$user, $groups) {
                if (!$user) { // don't do this for user grants the first time
                    $grants = preg_replace(
                        array( '/\=[^ ]*/', '/\>([^ ]*)/' ),
                        array( '', '$1' ),
                        $grants
                    );
                }
                $user = false;
                foreach ($groups as $group) {
                    if ( isset( $node->nodeValue['groups'][$group] ) ) {
                        $grants .= $node->nodeValue['groups'][$group];
                    }
                }

                return $grants;
            }
        );

        return $grants;
    }
}
