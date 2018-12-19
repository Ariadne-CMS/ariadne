arc/grants: access control management
=====================================

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Ariadne-CMS/arc-grants/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Ariadne-CMS/arc-grants/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/arc/grants/v/stable.svg)](https://packagist.org/packages/arc/grants)
[![Total Downloads](https://poser.pugx.org/arc/grants/downloads.svg)](https://packagist.org/packages/arc/grants)
[![Latest Unstable Version](https://poser.pugx.org/arc/grants/v/unstable.svg)](https://packagist.org/packages/arc/grants)
[![License](https://poser.pugx.org/arc/grants/license.svg)](https://packagist.org/packages/arc/grants)

How it works
------------

arc/grants is a simple and extensible system to manage access in a hierarchical system like a filesystem. At each node
in the tree you can assign access grants for a specific user or group. There are no predefined grants, a grant can be
any word. e.g.

    \arc\grants::cd('/foo/')->setForUser('public', 'read');

This assigns the grant 'read' for the user 'public' on the path '/foo/'. This grant is automatically set for any path
under '/foo/' as well. e.g:

    $hasReadAccess = \arc\grants::cd('/foo/bar/')->checkForUser('public', 'read'); // returns true

You can assign multiple grants as a space-seperated string:

    \arc\grants::setForUser('public', 'read add edit delete');

Grants for users do not stack. If you assign grants for a user somewhere, those will be the only grants available
starting at that node. Previous grants are overwritten. If you want to revoke grants, simply set the grants again,
without the grant you want revoked. To revoke all grants, set a 'marker' grant that is never checked, e.g:

    \arc\grants::cd('/foo/bar/')->setForUser('public', 'none');

The grants string is a simple DSL (Domain Specific Language) that allows you to set grants that do not 'trickle down' or
to set grants that only apply on child nodes:

- '=read' only assigns the grant on this node. Child nodes won't have the read grant.
- '>delete' assigns the grant only on child nodes, not on the current node.

Default user and path
---------------------

You can assign a default user for the grants system to use:

    \arc\grants::switchUser('admin');

Then you can assign grants and check them, without specifying the user:

    \arc\grants::set('read add edit >delete');

    $hasReadAccess = \arc\grants::check('read');

If you don't set a specific path with the cd() method, grants will be set and checked for the 'default' path. This
is the path that is last set using \arc\grants::cd( $path ), e.g.:

    \arc\grants::cd('/foo/');
    \arc\grants::switchUser('admin', ['administrators','public']);

    $hasReadAccess = \arc\grants::check('read');

This checks the grants for user 'admin' at '/foo/'. Any subsequent calls to \arc\grants assume this is the user and path
you want to use. Untill you again call either cd() or switchUser() on the \arc\grants class (statically).

Groups
------

If a user is member of one or more groups, you can set it like this:

    \arc\grants::setForGroup('administrators', 'read add edit delete');

And check it like this:

    $hasReadAccess = \arc\grants::switchUser('admin', ['administrators','public'])->check('read');

Group grants are added to any user grants previously set. If you set user grants at a node, any group grants set
at parent nodes are ignored. Only group grants under that node are added to the user grants.

If you override grants for a specific group, only the later group grants are applied, but only for that specific group.

    \arc\grants::cd('/')
    ->setForUser('mike', 'read edit')
    ->setForGroup('editors', 'read add edit >delete')
    ->cd('/foo/')
    ->setForGroup('editors', 'read');

Because mike has the 'edit' grant on '/', the group grants cannot remove it, so this works:

    $hasEditAccess = \arc\grants::switchUser('mike', ['editors'])->cd('/foo/')->check('edit'); // => true

Because the group 'editors' grants with 'read add edit >delete' are set at the same node as Mike's personal grants,
they are overruled by Mike's personal more restrictive grants, so this returns false:

    $hasAddAccess = \arc\grants::switchUser('mike', ['editors'])->cd('/')->check('add'); // => false

Finally the group grants for 'editors' is overridden at '/foo/', so this also returns false:

    $hasAddAccess = \arc\grants::switchUser('mike', ['editors'])->cd('/foo/')->check('add'); // => false


Listing grants
--------------

This will return a list of all user grants at a specific node. It will only return the grants set at this specific
node, not a list of all currently applicable grants:

    $userGrantsList = \arc\grants::cd('/foo/')->grantsForAllUsers();

    $groupGrantsList = \arc\grants::grantsForAllGroups();

Both will return an array with the user or group as the key and the grants string as the value or an empty string if no
grants are set.


Todo
----
- add support for 'owner' group, need to have a link to an owner tree, otherwise grants tree gets too large
