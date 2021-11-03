/*
	FIXME:
		+ needs support for calling callbacks multiple times - some dialogs dont close e.g. the new dialog.
			so make the purge explicit.
		+ closing the dialog is the task of the script that opened it, so should be done in this code, not in the dialog itself
		+ let op: gebruik GEEN showModalDialog, teveel bugs in Chrome en ook in IE.


	in your application:

		muze.dialog.open( url, 'browse', { windowFeatures: 'width=600,height=450', createNewWindow: false })
		.on('submit', function( args ) {
			// browse to args['path']
		})
		.onUndefined( function( action, args ) {
			alert('action '+action+' is not handled');
		})
		.always( function( action, args, result ) { 
			this.close();
		});

	in the browse dialog:
	
		openButton.onClick= function() {
			window.opener.muze.dialog.callback( window.name, 'submit', { 'path': path } )
		}
*/
muze.require('muze.event', function() {

	muze.namespace('muze.dialog', function() {
		var self = {};
		var callbackRegistry = {};
		var optionsRegistry = {};
		var windowRegistry = {};

		/* 
			This object contains the methods to chain all the different handlers for the actions from the dialog.
			It only needs a unique id linked to the specific dialog - the window.name in muze.dialog.open
		*/
		var callbackHandler = function( id ) {
			this.id = id;
			this.dialog = windowRegistry[ id ];
			var handler = this;

			/*
				This method adds a callback for the given action. The callback function is called with just one argument.
				The result of the callback function is returned by muze.dialog.callback()
			*/
			this.on = function( action, callback ) {
				callbackRegistry[id][action] = function( args ) {
					callback.call( handler, args );
				};
				return this;
			};

			/*
				This method adds a callback function for all actions which have no specific callback specified.
				The callback has an extra first argument which specifies which action was called from the dialog.
			*/
			this.onUndefined = function( callback ) {
				callbackRegistry[id]['_default'] = function( action, args ) {
					callback.call( handler, action, args );
				};
				return this;
			};

			/*
				This method removes all callback functions for this dialog.
			*/
			this.remove = function() {
				delete callbackRegistry[this.id];
			};

			/*
				This method closes the dialog and removes all callback functions for it.
			*/
			this.close = function() {
				self.close( this.id );
			};

			/*
				This method adds a callback function that is called for each action, after any matching callback
				functions are called. The callback function is passed three arguments, the action, the argument for
				that action and the result - if any - of the previous handler. The result is returned by muze.dialog.callback()
			*/
			this.always = function( callback ) {
				callbackRegistry[id]['_always'] = function( action, args, result ) {
					callback.call( handler, action, args, result );
				};
				return this;
			}
		};
		
		/*
			This method opens a dialog window or loads a dialog in a frame. It returns a new callbackHandler for this dialog.
		*/
		self.open = function( url, name, options ) {
			if ( options['frame'] ) {
				// use an existing frame - e.g. for a lightbox
				options['frame'].src = url;
				options['frame'].contentWindow.name = name;
			} else {
				if ( options['createNewWindow'] ) {
					do {
						var id = '_'+Math.floor((Math.random() * 100000)+1);
					} while ( callbackRegistry[name+id] );
					name = name + id;
				}
				if ( options['windowFeatures'] && !options['openInTab'] ) {
					var dialogWindow = window.open( url, name, options['windowFeatures'] );
				} else {
					var dialogWindow = window.open( url, name );
				}
				dialogWindow.focus();
				windowRegistry[name] = dialogWindow;
			}
			callbackRegistry[name] = {};
			optionsRegistry[name] = options;
			return new callbackHandler( name );
		};
		
		/*
			This method is meant to be used by the dialog's own javascript. It will call any registered callback functions
			for this dialog and the given action.
			e.g.: window.opener.muze.dialog.callback( window.name, 'cancel' ); 
		*/
		self.callback = function( windowName, action, args ) {
			if ( callbackRegistry[windowName] ) {
				var callbackList = callbackRegistry[windowName];
				if ( callbackList[action] ) {
					var result = callbackList[action].call( callbackList, args );
				} else if ( callbackList['_default'] ) {
					var result = callbackList['_default'].call( callbackList, action, args );
				}
				if ( callbackList['_always'] ) {
					result = callbackList['_always'].call( callbackList, action, args, result );
				}
				return result;
			}
		};

		/*
			This method can be used by the child window to check if the parent has a callback expected by the child.
			e.g.: window.opener.muze.dialog.hasCallback( window.name, 'submit' );
		*/

		self.hasCallback = function( windowName, action) {
			if ( callbackRegistry[windowName] ) {
				var callbackList = callbackRegistry[windowName];
				if (action) {
					if ( callbackList[action] ) {
						return true;
					}
				} else {
					return true;
				}
			}
			return false;
		}

		/* 
			This method is meant to be run from inside the dialog window. 
			It will trigger the callback in the opener
			It will return the result from the callback if it succeeded, null otherwise.
		*/
		self.return = function( action, values ) {
			if (window.opener && window.opener.muze && window.opener.muze.dialog) {
				return window.opener.muze.dialog.callback( window.name, action, values );
			} else if ( window.parent && window.parent.muze && window.parent.muze.dialog) {
				return window.parent.muze.dialog.callback( window.name, action, values );
			}
			return null;
		}

		/*
			This method is meant to be run from inside the dialog window. It will return a parameter passed
			by the opener to muze.dialog.open.
		*/
		self.getvar = function( name ) {
			if (window.opener && window.opener.muze && window.opener.muze.dialog ) {
				return window.opener.muze.dialog.getOption(window.name, name);
			} else {
				var p = window.parent;
				while ( p ) {
					if ( p.muze && p.muze.dialog ) {
						var option = p.muze.dialog.getOption(window.name, name);
						if ( typeof option != 'undefined' ) {
							return option;
						}
					}
					p = p.parent;
				}
			}
		}


		/*
			This method is used by a dialog, through the muze.dialog.getvar() method.
		*/
		self.getOption = function( windowName, name ) {
			if ( typeof optionsRegistry[ windowName ] != 'undefined' ) {
				return optionsRegistry[ windowName ][ name ];
			}
		}

		/*
			This method allows you to check whether a dialog exists.
		*/
		self.exists = function( windowName ) {
			return ( windowName in callbackRegistry );
		};

		/*
			This method tries to close the dialog window and removes all callback functions for it.
		*/
		self.close = function( windowName ) {
			if ( typeof windowRegistry[ windowName ] == 'object' ) {
				try {
					windowRegistry[ windowName ].close();
				} catch( e ) {
				}
			}
			delete windowRegistry[ windowName ];
			delete callbackRegistry[ windowName ];
		};

		return self;

	});

});