/*
	core javascript library for muze modules
	----------------------------------------

	object namespace(string module, function implementaion)
		This method checks if the namespace 'module' is available, and if not
		creates it and registers it. It returns the object the namespace points
		to, so you can create a shorthand for it.
		If you specify an implementation method, this method will be called with
		'this' pointing to the namespace object.
		examples:
			muze.namespace('muze.test');	
			
			var myMod = muze.namespace('muze.temp.my.module.with.a.long.name');
			
			muze.namespace('muze.test', function() { this.test = function() { alert 'test'; } }  );

	object require(string modules, function continuation) 
		This method only checks if the given module is available (registered).
		If not, it will throw an error with the missing module, and return
		false. 
		If it is available, it will return the module object.
		require does not attempt to load the required module, you must make sure you do
		that yourself. You can either load each script seperately, or in one go through
		Ariadne's ariadne.load.js template
		If a continuation function is supplied, that function will only be called if the
		required namespaces are available. If not, no errors will be thrown.
		If you require multiple modules, seperate them with a ','. Extra spaces will be trimmed of.
		
		muze.require('module.not.available');
		
		will throw an error, so you can do this:
		
		try {
			muze.require('muze.event');
			// do stuff
		} catch(e) {
			// module is not available
		}

		or:
		
		muze.require('muze.event, muze.env', function() {
			// do stuff, this function is called in the global scope
		});
		
	object include(string url, string namespace)
		This method checks whether the given namespace is already registered. If
		so it doesn't do anything.
		If the namespace is not registered (or not entered), it dynamically loads
		the url as a javascript object (script tag).
		In both cases the method returns the onload handler object. This object
		has one method 'onload', which allows you to specify a function that should
		be run when the javascript is loaded. This function is also run if the
		javascript was already loaded and include didn't actually do anything.
		examples:
			// this will only load muze.test.js if namespace muze.test isn't already
			// loaded and muze.test.js isn't already loaded
			muze.include('muze.test.js', 'muze.test').onload(function() {
				muze.test.run();
			});
			
			// this will load muze.test.js, even if muze.test is already available
			// if muze.test.js uses the muze.namespace() method correctly, it
			// can then extend muze.test
			muze.include('muze.test.js').onload(...);
			
			// this will load the script if the url isn't already loaded
			muze.include('random.script.js').onload(function() {
				// script is available but is not registered with a namespace
			})

	mixed load(string url, bool waitforme, bool cached)
		This method allows you to easily do ajax calls. If 'waitforme' is true,
		the ajax call is done synchronously, and load will return the responseText.
		Otherwise the call is done asynchronously, and load will return an onload
		handler object, just like include, only in this case the function you
		specify in onload will be called with one argument, namely the responseText.
		If you set 'cached' to true, the url won't be extended with a timestamp,
		allowing the browser to cache the response.
		examples:
			var response = muze.load('ajax.call.html', true);

			muze.load('ajax.call.html')
			.onload(function(response) {
				myDiv.innerHTML = response;
			})
			.ontimeout(function() {
				myDiv.innerHTML = 'timed out';
				this.clear();
			});
			
	object loader(object)
		This method allows you to easily implement your own loader handler, with onload and
		ontimeout methods. If you pass an object to loader, the onload handler will be called
		with that object defined as this. The ontimeout handler won't, it will allways use the loader as this.
		You must keep an internal reference to the loader object and call loader.loaded() manually
		to trigger the onload. Any arguments passed to loaded() will be passed on to an onload handler
		set throught loader.onload.
		If a timeout handler is set through loader.ontimeout(timer, method) than it will be called if
		the loader.loaded() method isn't called before the timeout.
		example:
			function myAjaxyThing() {
				var loader = muze.loader();
				// do some stuff
				mything.onload = function() {
					loader.loaded(response);
				}
				return loader;
			}
		methods:
			onload(callback)
			ontimeout(timer, callback)
			loaded()
			clear()
*/

var muze = this.muze = {};
muze.global = this;

(function() {

	/* private methods */

	function _getHTTPObject() { //FIXME: check if rearranged thing work 
		var xmlhttp = null;
		if (typeof XMLHttpRequest == 'undefined') {
			if (typeof ActiveXObject != 'undefined') {
				try {
					xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
				} catch (e) {
					try {
						xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
					} catch (E)  {
						xmlhttp = null;
					}
				}
			}
		} else {
			try {
				xmlhttp = new XMLHttpRequest();
			} catch (e) {
				xmlhttp = null;
			}
		}
		return xmlhttp;
	}

	function _namespaceWalk( module, handler ) {
		var rest	= module.replace(/^\s+|\s+$/g, ''); //trim
		var name	= '';
		var temp	= muze.global;
		var i 		= rest.indexOf( '.' );
		while ( i != -1 ) {
			name	= rest.substring( 0, i );
			if ( !temp[name])  {
				temp = handler(temp, name);
				if (!temp) {
					return temp;
				}
			}
			temp	= temp[name];
			rest	= rest.substring( i + 1 );
			i		= rest.indexOf( '.' );
		}
		if ( rest ) {
			if ( !temp[rest] ) {
				temp = handler(temp, rest);
				if (!temp) {
					return temp;
				}
			}
			temp	= temp[rest];
		}
		return temp;
	}
	
	/* private variables */

	var included	= {};
	var registered	= {};
	
	muze.namespace = function( module, implementation ) {
		var moduleInstance = _namespaceWalk( module, function(ob, name) {
			ob[name] = {};
			return ob;
		});
		registered[module]=true;
		if (typeof implementation == 'function') {
			implementation.call(moduleInstance);
		}
		return moduleInstance;
	};

	muze.require = function( modules, continuation ) {
		// the continuation is a function which is only run if all requirements are met
		if (typeof modules == 'string') {
			var modulesList = (/,/.test(modules)) ? modules.split(',') : [ modules ];
		} else if (typeof modules.length != 'undefined') {
			var modulesList = modules;
		} else {
			throw('Incorrect argument 1 (required modules): '+modules);
			return false;
		}
		for (var i=0; i<modulesList.length; i++) {
			var moduleInstance = _namespaceWalk( modulesList[i], function(ob, name) {
				if (typeof continuation == 'undefined') {
					throw 'namespace ' + module + ' not found ';
				} else {
					continuation = false;
				}
			});
		}
		if (typeof continuation == 'function') {
			continuation.call(muze.global);
		}
		return moduleInstance;
	};

	muze.include = function(url, module) {
		var loader = muze.loader();
		if (!included[url] && (!module || !registered[module])) {
			var script = document.createElement('SCRIPT');
			script.src = url;
			try {
				script.addEventListener('load', loader.loaded, false);
			} catch(e) {
				script.onreadystatechange = function() { 
					if (script.readyState == 'loaded' || script.readyState == 'complete') {
						loader.loaded();
						script.onreadystatechange = null;
					}
				};
			}
			document.getElementsByTagName('HEAD')[0].appendChild(script); // FIXME: make this more cross browser
		} else {
			// setTimeout is not optional here, since we have to return
			// (this) first, before the _onload method is called, otherwise
			// there is no way for a user to change 'onload_handler'.
			setTimeout(loader.loaded, 1);
		}
		return loader;
	};
	
	muze.load = function(url, waitforme, cached) {
		var loader = muze.loader();
		var timestamp = null;
		// get content from url
		if (!cached) {
			timestamp = new Date();
			if ( url.match( /\?/ ) ) {
				timestamp = '&t=' + timestamp.getTime();
			} else {
				timestamp = '?t=' + timestamp.getTime();
			}
		}
		var http = _getHTTPObject();
		http.open( 'GET', url + timestamp, !waitforme );
		if ( !waitforme ) {
			http.onreadystatechange = function() {
				if (http.readyState == 4) {
					loader.loaded( http.responseText );
				}
			};
		}
		http.send( null );
		if ( waitforme ) {
			return http.responseText;
		} else {
			return loader;
		}
	};
	
	muze.loader = function() {
		var _continue = function( continuation ) {
			return function() {
				if (typeof continuation == 'function') {
					continuation.apply( this, arguments );
					continuation = null;
				}
			};
		};
		var loaded = false;
		var onload_handler = null;
		var ontimeout_handler = null;
		var loader = {};
		loader.onload = function(handler) {
			onload_handler = handler;
			return this;
		};
		loader.ontimeout = function(timer, handler) {
			muze.global.setTimeout(timer, function() {
				if (!loaded) {
					_continue(handler)();
				}
			});
			return this;
		};
		loader.loaded = function() {
			loaded = true;
			_continue(onload_handler).apply(this, arguments);
		};
		loader.clear = function() {
			onload_handler = null;
			ontimeout_handler = null;
		};
		return loader;
	};
	
})();
