<?php

    /*
     * This file is part of the Ariadne Component Library.
     *
     * (c) Muze <info@muze.nl>
     *
     * For the full copyright and license information, please view the
     * LICENSE
     * file that was distributed with this source code.
     */

    class TestHash extends PHPUnit_Framework_TestCase
    {
        function testHashGet()
        {
            $hash = [
                'foo' => [
                    'bar' => 'This is a bar'
                ]
            ];
            $result = \arc\hash::get( '/foo/bar/', $hash );
            $this->assertEquals( $result, $hash['foo']['bar'] );

            $result = \arc\hash::get( '/foo/baz/', $hash );
            $this->assertTrue( $result === null );

            $result = \arc\hash::get( '/foo/baz/', $hash, 'default' );
            $this->assertTrue( $result === 'default' );

        }

        function testHashExists()
        {
            $hash = [
                'foo' => [
                    'bar' => 'This is a bar'
                ]
            ];
            $result = \arc\hash::exists( '/foo/bar/', $hash );
            $this->assertTrue( $result );
            $result = \arc\hash::exists( '/foo/baz/', $hash );
            $this->assertFalse( $result );
        }

        function testHashCompile()
        {
            $path = '/foo/bar/0/';
            $result = \arc\hash::compileName( $path );
            $this->assertEquals( $result, 'foo[bar][0]' );
        }

        function testHashParse()
        {
            $name = 'foo[bar][0]';
            $result = \arc\hash::parseName( $name );
            $this->assertEquals( $result, '/foo/bar/0/' );
        }

        function testHashWithSlash()
        {
            $name = 'foo[bar/baz]';
            $result = \arc\hash::parseName($name);
            $this->assertEquals( $result, '/foo/bar%2Fbaz/' );
            $result = \arc\hash::compileName($result);
            $this->assertEquals( $result, 'foo[bar/baz]');
        }

        function testTree()
        {
            $hash = [
                'foo' => [
                    'bar' => 'This is a bar'
                ]
            ];
            $node = \arc\hash::tree( $hash );
            $tree = \arc\tree::collapse( $node );
            $this->assertEquals( $tree, [ '/foo/bar/' => 'This is a bar' ] );
        }

    }
