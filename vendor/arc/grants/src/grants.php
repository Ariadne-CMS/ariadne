<?php

/*
 * This file is part of the Ariadne Component Library.
 *
 * (c) Muze <info@muze.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace arc;

/**
 * @package arc-grants
 * @requires \arc\path;
 * @requires \arc\tree;
 * @requires \arc\context;
 * @method static GrantsTree cd(string $path)
 * @method static GrantsTree switchUser(string $user, array $groups)
 * @method static GrantsTree setUserGrants(string $grants)
 * @method static GrantsTree setGroupGrants(string $group, string $grants)
 * @method static bool check(string $grant)
 * @method static array ls()
 */

final class grants
{
    public static function getGrantsTree()
    {
        $context = \arc\context::$context;
        if (!$context->arcUser) {
            $context->arcUser = 'public';
        }
        if (!$context->arcGroups) {
            $context->arcGroups  = [ 'public' ];
        }
        if (!$context->arcGrants) {
            $context->arcGrants = new grants\GrantsTree( \arc\tree::expand()->cd( $context->arcPath ), $context->arcUser, $context->arcGroups );
        }

        return $context->arcGrants;
    }

    public static function cd($path=null) {
        \arc\context::$context->arcGrants = self::getGrantsTree()->cd($path);
        return \arc\context::$context->arcGrants;
    }

    public static function switchUser($user) {
        \arc\context::$context->arcGrants = self::getGrantsTree()->switchUser($user);
        return \arc\context::$context->arcGrants;
    }

    public static function __callStatic($name, $params) {
        return call_user_func_array( [self::getGrantsTree(), $name], $params );
    }
}
