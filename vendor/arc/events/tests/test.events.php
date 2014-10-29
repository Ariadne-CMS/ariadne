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

    class TestEvents extends UnitTestCase
    {
        function testFireListenRemove()
        {
            // test default event stack, with \arc\context
            $result = \arc\events::fire( 'testEvent', array( 'seen' => false ) );
            if ($result) {
                $this->assertFalse( $result['seen'] );
            } else {
                $this->assertTrue( false );
            }

            $listener = \arc\events::listen( 'testEvent', function ($event) {
                $event->data['seen'] = true;
            });
            $result = \arc\events::fire( 'testEvent', array( 'seen' => false ) );
            if ($result) {
                $this->assertTrue( $result['seen'] );
            } else {
                $this->assertTrue( false );
            }

            $listener->remove();
            $result = \arc\events::fire( 'testEvent', array( 'seen' => false ) );
            if ($result) {
                $this->assertFalse( $result['seen'] );
            } else {
                $this->assertTrue( false );
            }

        }

        function testCaptureListenOrder()
        {
            $listener1 = \arc\events::listen( 'testEvent', function ($event) {
                $event->data[] = 'listener1';
            } );
            $capturer1 = \arc\events::capture( 'testEvent', function ($event) {
                $event->data[] = 'capturer1';
            } );
            $listener2 = \arc\events::listen( 'testEvent', function ($event) {
                $event->data[] = 'listener2';
            } );
            $capturer2 = \arc\events::capture( 'testEvent', function ($event) {
                $event->data[] = 'capturer2';
            } );
            $result = \arc\events::fire( 'testEvent', array( 'seen' => false ) );
            if ($result) {
                $this->assertFalse( $result['seen'] ); // make sure no spurious old listeners are left
                $this->assertTrue( $result[0] == 'capturer1' );
                $this->assertTrue( $result[1] == 'capturer2' );
                $this->assertTrue( $result[2] == 'listener1' );
                $this->assertTrue( $result[3] == 'listener2' );
            } else {
                $this->assertTrue( false );
            }
            $listener1->remove();
            $capturer1->remove();
            $listener2->remove();
            $capturer2->remove();
        }

        function testPathVisibility()
        {
            $listener = \arc\events::cd( '/test/' )->listen( 'testEvent', function ($event) {
                $event->data['test'] = '/test/listener';
            } );
            $result = \arc\events::fire( 'testEvent', array( 'seen' => false ) );
            if ($result) {
                $this->assertFalse( isset( $result['test'] ) ); // means the event listener didn't fire, which is correct
            } else {
                $this->assertTrue( false );
            }

            $result = \arc\events::cd( '/test/' )->fire( 'testEvent', array( 'seen' => false ) );
            if ($result) {
                $this->assertTrue( $result['test'] == '/test/listener' );
            } else {
                $this->assertTrue( false );
            }

            $result = \arc\events::cd( '/test/child/' )->fire( 'testEvent', array( 'seen' => false ) );
            if ($result) {
                $this->assertTrue( $result['test'] == '/test/listener' );
            } else {
                $this->assertTrue( false );
            }
            $listener->remove();

        }

        function testPathOrder()
        {
            $listener1 = \arc\events::listen( 'testEvent', function ($event) {
                $event->data[] = 'listener1';
            } );
            $capturer1 = \arc\events::capture( 'testEvent', function ($event) {
                $event->data[] = 'capturer1';
            } );
            $listener2 = \arc\events::cd('/test/')->listen( 'testEvent', function ($event) {
                $event->data[] = 'listener2';
            } );
            $capturer2 = \arc\events::cd('/test/')->capture( 'testEvent', function ($event) {
                $event->data[] = 'capturer2';
            } );
            $result = \arc\events::cd('/test/')->fire( 'testEvent', array( 'seen' => false ) );
            if ($result) {
                $this->assertFalse( $result['seen'] ); // make sure no spurious old listeners are left
                $this->assertTrue( $result[0] == 'capturer1' );
                $this->assertTrue( $result[1] == 'capturer2' );
                $this->assertTrue( $result[2] == 'listener2' );
                $this->assertTrue( $result[3] == 'listener1' );
            } else {
                $this->assertTrue( false );
            }
            $listener1->remove();
            $capturer1->remove();
            $listener2->remove();
            $capturer2->remove();
        }

        function testCancel()
        {
            $listener1 = \arc\events::listen( 'testEvent', function ($event) {
                return false;
            } );
            $listener2 = \arc\events::listen( 'testEvent', function ($event) {
                $event->data['seen'] = true;
            } );
            $result = \arc\events::cd('/test/')->fire( 'testEvent', array( 'seen' => false ) );
            $this->assertFalse( $result['seen'] );
            $listener1->remove();
            $listener2->remove();
        }

        function testPreventDefault()
        {
            $listener = \arc\events::listen( 'testEvent', function ($event) {
                $event->preventDefault();
            } );
            $result = \arc\events::cd('/test/')->fire( 'testEvent', array( 'seen' => false ) );
            $this->assertFalse( $result );
            $listener->remove();
        }

    }
