arc/events
==========

This component implements an event system very similar to events in modern browsers. Events have a seperate capture and listen phase. Events are fired and listened to on a path - like a filesystem path - instead of an object in the DOM. 

In the capture phase listeners are called in order starting with listeners on the root path '/'. Then - if the event has not been cancelled - in the listen phase listeners are called in the reverse order - with the root path being called last.

Examples:
---------

    \arc\events::listen( 'onbeforesave', function( $event ) { 
        if ( $event->data['name'] == 'Foo' ) {
            $event->data['name'] = 'Bar';
        }
    });

    $eventData = \arc\events::fire( 'onbeforesave', array( 'name' => 'Foo' ) );
    if ( $eventData ) {
        $name = $eventData['name'];
        // ... actually save something here ...
        \arc\events::fire( 'onsave', array( 'name' => $name ) );
    }

Or an example using paths:

    \arc\events::cd('/foo/')->listen( 'onbeforesave', function( $event ) {
        return $event->preventDefault(); // don't allow saves in /foo/
    });

    $eventData = \arc\events::cd('/foo/bar/')->fire( 'onbeforesave' );
    if ( $eventData ) {
         // save something, but alas - it has been prevented by a listener
    }

