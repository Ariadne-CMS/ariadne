muze.namespace('vedor.editor.compose');

muze.require('muze.event');

vedor.editor.compose = ( function() {

	var event = muze.event;

	var compose = {
		table: {
			'i': -1,
			'i!': "iexcl",
			'$': -1,
			'$c': "cent",
			'$p': "pound",
			'$#': "curren",
			'$y': "yen",
			'$e': "euro",
			'$f': "fnof",
			'|': "brvbar",
			'#': "sect",
			'"': "uml",
			'c': -1,
			'c@': "copy",
			'c=': "euro",
			'o': -1,
			'of': "ordf",
			'<': -1,
			'<<': "laquo",
			'!': "not",
			'-': "shy",
			'r': -1,
			'r@': "reg",
			'_': "macr",
			'd': -1,
			'dg': "deg",
			'+': -1,
			'+-': "plusmn",
			'^': -1,
			'^1': "sup1",
			'^2': "sup2",
			'^3': "sup3",
			"'": "acute",
			'm': "micro",
			'p': -1,
			'pp': "para",
			'.': "middot",
			',': "cedil",
			'o': -1,
			'om': "ordm",
			'>': -1,
			'>>': "raquo",
			'1': -1,
			'1/': -1,
			'1/4': "frac14",
			'1/2': "frac12",
			'3': -1,
			'3/': -1,
			'3/4': "frac34",
			'i': -1,
			'i?': "iquest",
			'A': -1,
			'A`': "Agrave",
			"A'": "Aacute",
			'A^': "Acirc", 
			'A~': "Atilde",
			'A"': "Auml",  
			'Ao': "Aring", 
			'AE': "AElig", 
			'C': -1,
			'C,': "Ccedil",
			'E': -1,
			'E`': "Egrave",
			"E'": "Eacute",
			'E^': "Ecirc", 
			'E"': "Euml",  
			'ET': -1,   
			'ETH': "ETH",   
			'I': -1,
			'I`': "Igrave",
			"I'": "Iacute",
			'I^': "Icirc", 
			'I"': "Iuml",  
			'N': -1,   
			'N~': "Ntilde",
			'O': -1,   
			'O`': "Ograve",
			"O'": "Oacute",
			'O^': "Ocirc", 
			'O~': "Otilde",
			'O"': "Ouml",  
			'O/': "Oslash",
			'*': "times", 
			'U': -1,
			'U`': "Ugrave",
			"U'": "Uacute",
			'U^': "Ucirc", 
			'U"': "Uuml",  
			'Y': -1,
			"Y'": "Yacute",
			'T': -1,
			'TH': "THORN", 
			's': -1,
			'sz': "szlig", 
			'a': -1,
			'a`': "agrave",
			"a'": "aacute",
			'a^': "acirc", 
			'a~': "atilde",
			'a"': "auml",  
			'ao': "aring", 
			'ae': "aelig", 
			'c': -1,
			'c,': "ccedil",
			'e': -1,
			'e`': "egrave",
			"e'": "eacute",
			'e^': "ecirc", 
			'e"': "euml",  
			'i': -1,
			'i`': "igrave",
			"i'": "iacute",
			'i^': "icirc", 
			'i"': "iuml",  
			'et': -1,
			'eth': "eth",   
			'n': -1,
			'n~': "ntilde",
			'o': -1,
			'o`': "ograve",
			"o'": "oacute",
			'o^': "ocirc", 
			'o~': "otilde",
			'o"': "ouml",  
			'/': "divide",
			'o/': "oslash",
			'u': -1,
			'u`': "ugrave",
			"u'": "uacute",
			'u^': "ucirc", 
			'u"': "uuml",  
			'y': -1,
			"y'": "yacute",
			't': -1,
			'th': "thorn", 
			'tm': "trade",
			'y"': "yuml",  
			'l': -1,
			'l/': "lstrok",
			'L': -1,
			'L/': "Lstrok",  
			'#': -2,
			'&': -3	
		},

		key: 19,				// compose start key, default is pause/break
								// override this to use a different key
		active: false,			// whether or not the composer is active
		buffer: '',				// buffer string where current entry is kept
		isNumeric: false,		// whether or not a numeric entity is entered, e.g. '#1234;'
		isSymbolic: false,		// whether or not a symbolic entity is entered, e.g. '&euro;'

		init: function( eventEl, popup, onComplete ) {
			compose.eventEl = eventEl;
			compose.popup = popup;
			compose.onComplete = onComplete;

			event.attach( eventEl, 'keydown', compose.keydown);
			event.attach( eventEl, 'keypress', compose.keypress);
		},

		start: function() {
			compose.active = true;
			compose.isNumeric = false;
			compose.isSymbolic = false;
			compose.buffer = '';
			compose.update();
		},
		
		stop: function( nocomplete ) {
			if( !nocomplete && compose.onComplete && compose.buffer != '') {
				compose.onComplete( compose.buffer);
			}
			compose.active=false;
			compose.isNumeric=false;
			compose.buffer='';
			compose.update();
		},

		isActive: function() {
			return compose.active;
		},


		clickSymbol: function(buffer) {
			compose.buffer = buffer;
			var value = compose.table[buffer];
			if( value < 0 ) {
				compose.update();
			} else {
				compose.buffer = '&' + value + ';';
				compose.stop();
			}
		},

		newSymbol: function(buffer, key, token) {
			var keylink = compose.popup.ownerDocument.createElement('A');
			keylink.href='#';
			keylink.className='vdComposeKey';
			keylink.unselectable='on';
			keylink.onclick=function() {
				compose.clickSymbol(buffer);
			}
			if (token) {
				var tokenspan = compose.popup.ownerDocument.createElement('SPAN');
				tokenspan.className ='vdComposeToken';
				tokenspan.innerHTML = token;
				tokenspan.unselectable = 'on';
				keylink.appendChild(tokenspan);
			}
			var keytext = compose.popup.ownerDocument.createTextNode(key);
			keylink.appendChild(keytext);
			return keylink;
		},

		show: function() {
			if( !compose.popup ) {
				return;
			}
			compose.popup.style.display = 'block';
			
			if( !compose.popuphints ) {
				compose.popuphints = compose.popup.ownerDocument.createElement("div");
				compose.popuphints.id = compose.popup.id + 'Hints';
				compose.popup.appendChild(compose.popuphints);
			}
			if( !compose.popuppreview ) {
				compose.popuppreview = compose.popup.ownerDocument.createElement("div");
				compose.popuppreview.id = compose.popup.id + 'Preview';
				compose.popup.appendChild(compose.popuppreview);
			}
			compose.popuphints.style.display='none';
			compose.popuphints.innerHTML = '';

			var keys = compose.popup.ownerDocument.createElement("div");
			var symbols = compose.popup.ownerDocument.createElement("div");
			if( !compose.buffer ) {
				for (var i in compose.table) {
					if( i.length==1 && compose.table[i] != -3 && compose.table[i] != -2 ) {
						if( compose.table[i] == -1 ) {
							keys.appendChild( compose.newSymbol(i, i) );
						} else {
							symbols.appendChild( compose.newSymbol(i, i, '&'+compose.table[i]+';') );
						}
					}
				}
			} else {
				for (var i in compose.table) {
					if ((i.substr(0, compose.buffer.length) == compose.buffer) && compose.table[i] != -1 && compose.table[i] != -2 && compose.table[i] != -3) {
						var key = i.substr(compose.buffer.length);
						symbols.appendChild( compose.newSymbol(i, key, '&'+compose.table[i]+';') );
					}
				}
			}
			compose.popuphints.appendChild(symbols);
			compose.popuphints.appendChild(keys);
			compose.popuphints.style.display='block';			
			compose.popuppreview.innerHTML = compose.buffer;
		},

		hide: function() {
			if( !compose.popup ) {
				return;
			}
			compose.popup.style.display = 'none';
		},

		update: function() {
			if( !compose.popup ) {
				return;
			}
			if (compose.active) {
				compose.show();
			} else {
				compose.hide();
			}
		},

		// keydown handler is needed for IE, it doesn't trigger keypress events for pause break esc etc
		keydown: function(e) {
			var keycode = e.keyCode; 

			if (keycode==compose.key) {
				if (!compose.active) {
					compose.start();
				} else {
					compose.stop( true );
				}
				return event.cancel(e);
			}
			if (compose.active) {
				if (keycode==27) { // esc
					compose.stop( true );
					return event.cancel(e);
				} else if (keycode==8) { // backspace
					compose.buffer=compose.buffer.substr(0, compose.buffer.length-1);
					compose.update();
					return event.cancel(e);
				}
			}
			return true;
		},

		keypress: function(e) {
			var keycode = e.keyCode;
			var charcode = event.getCharCode(e);
			var key=String.fromCharCode(charcode);

			if (keycode==compose.key) {
				return event.cancel(e);
			}
			if (compose.active) {
				if (keycode==27) { // esc
					return event.cancel(e);
				} else if( keycode==8) { // backspace
					return event.cancel(e);
				}
				compose.buffer+=key;
				var value = compose.table[compose.buffer];
				if (value) {
					if (value==-3) {
						compose.isSymbolic=true;
					} else if (value==-2) {
						compose.isNumeric=true;
					} else if (value!=-1) {
						compose.buffer='&'+compose.table[compose.buffer]+';';
						compose.stop();
					}
				} else if (compose.isSymbolic) {
					if ((keycode==13) || (key==';')) {
						compose.buffer='&'+compose.buffer.substr(1, compose.buffer.length-2)+';';
						compose.stop();
					}
				} else if (compose.isNumeric) {
					if ((charcode>=48) && (charcode<=57)) {
						// nothing to do, the number is already added to the buffer
					} else if (keycode==13) {
						compose.buffer='&'+compose.buffer.substr(0, compose.buffer.length-1)+';';
						compose.stop();
					} else {
						// not a number, so stop.
						compose.stop();
					}
				} else {
					compose.stop();
				}
				compose.update();
				return event.cancel(e);
			} else {
				return true;
			}
		}

	}
	return compose;
})(); 
