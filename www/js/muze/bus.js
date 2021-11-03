/*

	muze.bus - a cross window, single domain, message bus

	This library makes it easier to create an application that uses multiple windows or iframes.
	It creates a single message bus across all windows/iframes.
	Messages can contain anything, but references to other objects may be lost when windows
	are closed. So it is best to treat message data as very temporary.
	
	Currently there is no provision to directly message only specific windows, all messages are
	broadcast to all windows. A private channel feature may be added in the future.

	This bus is not designed to be used cross domain, that is not its goal, there are many good
	tools for that already. In addition it is also net designed as a remote communication tool.

	What this bus does allow is to share resources and connections across windows. You could
	add a remote bus connection in one window and connect it to muze.bus, so you only need one
	connection for all windows.

	public API:

	Start a bus
		var bus = muze.bus.open('name');
	
	Add a listener
		var listener = bus.listen('message.type',callback);
	
	Send a message
		bus.send('message.type', messageData);

	Remove a listener	
		listener.remove();
	
	Close the bus
		bus.close();

*/


muze.namespace('muze.bus', function() {

	var busses = {};
	var self = {};

	function Bus(id) {
		this.id = id;
		this.windows = [];
		this.names = {};
		this.listeners = {}
	}


	var getBus = function(w, id) {
		try {
			if ( w && w.muze && w.muze.bus ) {
				return w.muze.bus.get(id);
			}
		} catch(e) {
			return null;
		}
	}

	Bus.prototype.send = function(type, message) {
		var deliveredLocal = false;
		for ( var i=0,l=this.windows.length; i<l; i++ ) {
			try {
				var b = getBus(this.windows[i], this.id);
				if (b) {
					b.deliver(type, message);
					if ( b === this ) {
						deliveredLocal = true;
					}
				}
			} catch(e) {
				console.log(e);
			}
		}
		if ( !deliveredLocal ) {
			this.deliver(type, message);
		}
	};

	Bus.prototype.close = function() {
		// informational only, tabs can be reopened, possibly without
		// their opener being open, so keep reference to the windows
		// even after close message
		this.send('muze.bus.close', {
			windowName: window.name,
			window: window
		});
	};

	Bus.prototype.listen = function(type, callback) {
		if ( typeof this.listeners[type] == 'undefined' ) {
			this.listeners[type] = [];
		}
		this.listeners[type].push(callback);
		var index = this.listeners[type][this.listeners[type].length]-1;
		var bus = this;
		return {
			remove: function() {
				bus.listeners[type][index] = undefined;
			}
		};
	};

	Bus.prototype.deliver = function(type, message) {	
		if ( typeof this.listeners[type] != 'undefined' ) {
			for ( var i=0,l=this.listeners[type].length; i<l; i++ ) {
				if ( typeof this.listeners[type][i] != 'undefined' ) {
					this.listeners[type][i].call(this, message);
				}
			}
		}
	};

	self.get = function(id) {
		if ( busses[id] != 'undefined' ) {
			return busses[id];
		}
		return null;
	};

	self.open = function(id, options) {
		var bus = this.get(id);
		if (bus) {
			return bus;
		}
	
		// initialization
		bus = new Bus(id);
		busses[id] = bus;

		// first listen to all 'open' messages
		bus.listen('muze.bus.open', function(message) {
			if ( message.windowName ) {
				bus.names[message.windowName] = message.window;
			}
			// only add the window if it isn't there already
			// reloading a window would otherwise trigger
			// an avalanche of windows
			var found = false;
			for ( var i=0,l=bus.windows.length; i<l; i++ ) {
				if ( bus.windows[i] == message.window ) {
					found = true;
					break;
				}
			}
			if ( !found ) {
				bus.windows.push(message.window);
			}
		});

		// connect with other windows
		var parentBus = getBus(window.opener || window.parent, bus.id);
		if ( parentBus ) {
			bus.windows = parentBus.windows;
		}

		// then send the open message
		bus.send('muze.bus.open', {
			windowName: window.name,
			window: window
		});

		// and trigger a close message on window unload
		muze.event.attach(window, 'unload', function() {
			bus.close();
		});

		return bus;
	};

	return self;
});