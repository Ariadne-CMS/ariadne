
muze.namespace('muze.html', function() {
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
			if ( name.substr(0,5)=='data-' ) {
				el.setAttribute(name, value);
			} else {
				el[ name ] = value;
			}
		}
	}
	
	this.el = function(tagName) { //, attributes, children) {
		var el = muze.global.document.createElement(tagName);
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
					el = muze.global.document.createElement(elString);
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
				subEl = muze.global.document.createTextNode(subEl);
			}
			el.appendChild( subEl );
		}
		return el;
	}

	this.element = this.el;

});