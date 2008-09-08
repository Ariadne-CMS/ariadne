/*
	core javascript library for muze modules
	----------------------------------------

	object namespace(string module)
		This method checks if the namespace 'module' is available, and if not
		creates it and registers it. It returns the object the namespace points
		to, so you can create a shorthand for it.
		examples:
			muze.namespace('muze.test');
			
			var myMod = muze.namespace('muze.temp.my.module.with.a.long.name');

	object require(string module) 
		This method only checks if the given module is available (registered).
		If not, it will alert() an error with the missing module, and return
		false. 
		If it is available, it will return the module object.
		
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
			muze.include('muze.test.js', 'muze.test').onload(function() {
				muze.test.run();
			});

	object load(string url, bool waitforme, bool cached)
		This method allows you to easily do ajax calls. If 'waitforme' is true,
		the ajax call is done synchronously, and load will return the responseText.
		Otherwise the call is done asynchronously, and load will return an onload
		handler object, just like include, only in this case the function you
		specify in onload will be called with one argument, namely the responseText.
		If you set 'cached' to true, the url won't be extended with a timestamp,
		allowing the browser to cache the response.
		examples:
			var response = muze.load('ajax.call.html', true);

			muze.load('ajax.call.html').onload(function(response) {
				myDiv.innerHTML = response;
			});
			
*/

var muze = function() {

	/* private methods */

	function getHTTPObject() {
		var xmlhttp;
		/*@cc_on
		@if (@_jscript_version >= 5)
			try {
				xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
			} catch (e) {
				try {
					xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
				} catch (E) {
					xmlhttp = false;
				}
			}
		@else
		xmlhttp = false;
		@end @*/
		if (!xmlhttp && typeof XMLHttpRequest != 'undefined') {
			try {
				xmlhttp = new XMLHttpRequest();
			} catch (e) {
				xmlhttp = false;
			}
		}
		return xmlhttp;
	}

	/* private variables */

	var included={};
	var registered={};

	return {
		namespace : function(module) {
			var rest = new String(module);
			var name = '';
			var temp = window;
			var i = rest.indexOf('.');
			while (i != -1) {
				name = rest.substring(0, i);
				if (!temp[name]) {
					temp[name] = {};
				}
				temp = temp[name];
				rest = rest.substring(i+1);
				i = rest.indexOf('.');
			}
			if (rest) {
				if (!temp[rest]) {
					temp[rest] = {};
				}
				temp = temp[rest];
			}
			registered[module]=true;
			return temp;
		},

		require : function(module) {
			var rest = new String(module);
			var name = '';
			var temp = window;
			var i = rest.indexOf('.');
			while (i != -1) {
				name = rest.substring(0, i);
				if (!temp[name]) {
					alert( 'namespace ' + module + ' not found ' );
					return false;
				}
				temp = temp[name];
				rest = rest.substring(i+1);
				i = rest.indexOf('.');
			}
			if (rest) {
				if (!temp[rest]) {
					alert( 'namespace ' + module + ' not found ' );
					return false;
				}
				temp = temp[rest];
			}
			return temp;
		},

		include : function(url, module) {
			var onload_handler = function() { }
			this.onload = function(continuation) {
				// run the method in continuation when the url is loaded
				onload_handler = continuation;
			}
			this._onload = function() {
				onload_handler();
				onload_handler=null;
			}
			// load a javascript library from the given url
			if (!included[url] && (!module || !registered[module])) {
				var script = document.createElement('SCRIPT');
				script.src = url;
				try {
					script.addEventListener('load', function() {
						onload_handler();
						onload_handler = null;
					}, false);
				} catch(e) {
					script.onreadystatechange = function() { 
						if (script.readyState == 'loaded' || script.readyState == 'complete') {
							onload_handler();
							onload_handler = null;
							script.onreadystatechange = null;
						}
					}
				}
				document.getElementsByTagName('HEAD')[0].appendChild(script);
			} else {
				// setTimeout is not optional here, since we have to return
				// (this) first, before the _onload method is called, otherwise
				// there is no way for a user to change 'onload_handler'.
				setTimeout(this._onload, 1);
			}
			return this;
		},

		load : function(url, waitforme, cached) {
			var onload_handler = function() { }
			this.onload = function(continuation) {
				// run the method in continuation when the url is loaded
				onload_handler = continuation;
			}
			// get content from url
			if (!cached) {
				var timestamp=new Date();
				if (url.match(/\?/)) {
					timestamp='&t='+timestamp.getTime();
				} else {
					timestamp='?t='+timestamp.getTime();
				}
			} else {
				var timestamp='';
			}
			var http=getHTTPObject();
			http.open('GET',url+timestamp,!waitforme);
			if (!waitforme) {
				http.onreadystatechange = function() {
					if (http.readyState == 4) {
						var response = http.responseText;
						onload_handler(response);
					}
				}
			}
			http.send(null);
			if (waitforme) {
				return http.responseText;
			} else {
				return this;
			}
		}
	}
}();
