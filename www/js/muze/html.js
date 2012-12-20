
muze.namespace('muze.html');

muze.html = (function() {
	var global = this;
	var html = {};
	var getType = function(obj) {
		return ({}).toString.call(obj).match(/\s([a-z|A-Z]+)/)[1].toLowerCase()
	}
	
	var setAttr = function(el, name, value) {
		if ( name == 'style' ) {
			for (var ii in value ) {
				el.style[ii] = value[ii];
			}
		} else {
			switch(name) {
				case 'class': 
					name = 'className';
				break;
				case 'for':
					name = 'htmlFor';
				break;
			}
			el[ name ] = value;
		}
	}
	
	html.el = function(tagName) { //, attributes, children) {
		var el = global.document.createElement(tagName);
		var next = 1;
		var attributes = arguments[1];
		if (attributes && getType(attributes)=='object') {
			next = 2;
			try {
				for (var i in attributes ) {
					setAttr(el, i, attributes[i]);
				}
			} catch(e) {
				if ( /input/i.test(tagName) ) {
					var elString = '<'+tagName;
					for ( var i in attributes ) {
						if ( getType(attributes[i])=='string' ) {
							elString += ' '+i+'="'+escape(attributes[i])+'"';
						}
					}
					elString += '>';
					el = global.document.createElement(elString);
					for ( var i in attributes ) {
						if ( getType(attributes[i])!='string' ) {
							setAttr(el, i, attributes[i]);
						}
					}
				}
			}
		}
		for (var i=next, l=arguments.length; i<l; i++) {
			var subEl = arguments[i];
			if (getType(subEl)=='string') {
				subEl = global.document.createTextNode(subEl);
			}
			el.appendChild( subEl );
		}
		return el;
	}

	html.element = html.el;

	return html;
})();