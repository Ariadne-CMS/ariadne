<?php

    /*
     * This file is part of the Ariadne Component Library.
     *
     * (c) Muze <info@muze.nl>
     *
     * For the full copyright and license information, please view the LICENSE
     * file that was distributed with this source code.
     */

    class TestFileStore extends PHPUnit\Framework\TestCase
    {
        function filestoreHelper()
        {
            return new \arc\cache\FileStore(sys_get_temp_dir());
        }

        function testgetput()
        {
            $fs = self::filestoreHelper();
            $fs->putVar('test','test');
            $this->assertEquals('test', $fs->getvar('test'));
        }

        function testgetInfo()
        {
            $fs = self::filestoreHelper();
            $fs->putVar('test','test');
            $info = $fs->getInfo('test');
            $this->assertIsArray($info);
            foreach( ['mtime','ctime','size'] as $key) {
                $this->assertArrayHasKey($key, $info);
            }
        }

        function testpurge()
        {
            $fs = self::filestoreHelper();
            $fs->putVar('test','test');
            $fs->purge('test');
            $this->assertNotEquals('test', $fs->getvar('test'));
        }
    }
