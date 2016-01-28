<?php

    /*
     * This file is part of the Ariadne Component Library.
     *
     * (c) Muze <info@muze.nl>
     *
     * For the full copyright and license information, please view the LICENSE
     * file that was distributed with this source code.
     */

    require_once( __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php' );

    class TestLambda extends PHPUnit_Framework_TestCase
    {
        function testPrototype()
        {
            $view = \arc\lambda::prototype( [
                'foo' => 'bar',
                'bar' => function () {
                    return $this->foo;
                }
            ] );
            $this->assertTrue( $view->foo === 'bar' );
            $this->assertTrue( $view->bar() === 'bar' );
        }

        function testPrototypeInheritance()
        {
            $foo = \arc\lambda::prototype( [
                'foo' => 'bar',
                'bar' => function () {
                    return $this->foo;
                }
            ]);
            $bar = $foo->extend( [
                'foo' => 'rab'
            ]);
            $this->assertTrue( $foo->foo === 'bar' );
            $this->assertTrue( $bar->foo === 'rab' );
            $this->assertTrue( $foo->bar() === 'bar' );
            $this->assertTrue( $bar->bar() === 'rab' );
            $this->assertTrue( $bar->hasOwnProperty('foo') );
            $this->assertFalse( $bar->hasOwnProperty('bar') );

        }

        function testPrototypeInheritance2()
        {
            $foo = \arc\lambda::prototype([
                'bar' => function () {
                    return 'bar';
                }
            ]);
            $bar = $foo->extend([
                'bar' => function () use ($foo) {
                    return 'foo'.$foo->bar();
                }
            ]);
            $this->assertTrue( $bar->bar() === 'foobar' );
        }

        function testPrototypeInheritance3()
        {
            $foo = \arc\lambda::prototype([
                'bar' => function () {
                    return 'bar';
                },
                'foo' => function () {
                    return '<b>'.$this->bar().'</b>';
                }
            ]);
            $bar = $foo->extend([
                'bar' => function () use ($foo) {
                    return 'foo'.$foo->bar();
                }
            ]);
            $this->assertTrue( $bar->foo() === '<b>foobar</b>' );
        }

        function testSingleton()
        {
            $bar = \arc\lambda::singleton( function () {
                return 'bar' . time();
            } );
            $baz = \arc\lambda::singleton( function () {
                return 'baz';
            } );
            $test1 = $bar();
            sleep(1);
            $test2 = $bar();
            $this->assertTrue( $test1 == $test2 );
            $this->assertTrue( $baz() == 'baz' );
        }

        function testPartial()
        {
            $bar = function ($x, $y, $z, $q=1) {
                return [ 'x' => $x, 'y' => $y, 'z' => $z, 'q' => $q];
            };
            $baz = \arc\lambda::partial( $bar, [ 0 => 'x', 2 => 'z' ] );
            $result = $baz( 'y' );
            $this->assertTrue( $result == [ 'x' => 'x', 'y' => 'y', 'z' => 'z', 'q' => 1 ] );
        }

        function testPartialPartial()
        {
            $bar = function ($x, $y, $z='z', $q=1) {
                return [ 'x' => $x, 'y' => $y, 'z' => $z, 'q' => $q];
            };
            $baz = \arc\lambda::partial( $bar, [ 0 => 'x', 3 => 'q' ], [ 2 => 'z' ] );
            $result = $baz( 'y' );
            $this->assertTrue( $result == [ 'x' => 'x', 'y' => 'y', 'z' => 'z', 'q' => 'q' ] );
        }

        function testPepper()
        {
            $f = function($peppered, $reallypeppered) {
                return isset($peppered) && isset($reallypeppered) && $peppered==$reallypeppered;
            };
            $p = \arc\lambda::pepper( $f, [ 'peppered' => null, 'reallypeppered' => null] );
            $result = $p(['reallypeppered' => 1, 'peppered' => 1]);
            $this->assertTrue($result);
        }

        function testToString()
        {
            $foo = \arc\lambda::prototype([
                'foofoo' => function () {
                    return 'foofoo';
                },
                '__toString' => function () {
                    return 'foobar';
                },
            ]);
            $tst = (string)$foo;
            $this->assertTrue( 'foobar' === $tst);
        }
    }
