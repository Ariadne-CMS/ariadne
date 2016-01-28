<?php

    /*
     * This file is part of the Ariadne Component Library.
     *
     * (c) Muze <info@muze.nl>
     *
     * For the full copyright and license information, please view the LICENSE
     * file that was distributed with this source code.
     */


    class TestGrants extends PHPUnit_Framework_TestCase
    {
        function testGrantsSetGet()
        {
            \arc\grants::switchUser('test')->setUserGrants('read =add >edit >delete');
            $this->assertTrue( \arc\grants::check('read') );
            $this->assertTrue( \arc\grants::check('add') );
            $this->assertFalse( \arc\grants::check('edit') );
            $this->assertFalse( \arc\grants::check('foo') );
        }

        function testGrantsOnPath()
        {
            //\arc\grants::switchUser('test')->setUserGrants('read =add >edit >delete');
            \arc\grants::cd('/test/');
            $this->assertTrue( \arc\grants::check('read') );
            $this->assertFalse( \arc\grants::check('add') );
            $this->assertTrue( \arc\grants::check('edit') );
        }


    }
