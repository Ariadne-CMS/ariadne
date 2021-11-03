muze.namespace('vedor.widgets.properties');

vedor.widgets.properties = ( function() { 

	var self = {
		get : function( id ) {
			var value='';
			var input;
			if ((typeof id == "string") || (typeof id == "text") ) {
				input = document.getElementById(id);
			} else {
				input = id;
			}

			if (input) {
				if (!input.type && input.getAttribute("data-type")) {
					input.type = input.getAttribute("data-type");
				}

				switch (input.type) {
					case 'checkbox' :
						if (input.checked) {
							value=input.value;
						}
						break;
					case 'radio' :
						var radio=input.form[input.name];
						if (radio) { 
							for (var i=0; i<radio.length; i++) {
								if (radio[i].checked) {
									value=radio[i].value;
									break;
								}
							}
						}
						break;
					case 'hidden' :
					case 'password' :
					case 'text' :
						value=input.value;
						break;
					case 'select-one' :
						value=input.options[input.selectedIndex].value;
						break;
					case 'select-multiple' :
						value=new Array();
						for (var i=0; i<input.length; i++) {
							if (input.options[i].selected) {
								value[value.length]=input.options[i].value;
							}
						}
						break;
					case 'vedor-buttongroup-radio' : 
						var values = input.querySelectorAll("button.vedor-selected");
						for (var i=0; i<values.length; i++) {
							value = values[i].getAttribute("data-value");
						}
						break;
					default :
						value=input.innerHTML;
						break;
				}
				return value;
			} else {
				return '';
			}

		},

		set : function( id, value ) {
			var input;

			if ((typeof id == "string") || (typeof id == "text") ) {
				input = document.getElementById(id);
			} else {
				input = id;
			}
			if (input) {
				if (!input.type && input.getAttribute("data-type")) {
					input.type = input.getAttribute("data-type");
				}
				self.enable(id);
				switch (input.type) {
					case 'checkbox' :
						if (input.value==value) {
							input.checked=true;
						} else {
							input.checked=false;
						}
						break;
					case 'radio' :
						var radio=input.form[input.name];
						if (radio) { 
							for (var i=0; i<radio.length; i++) {
								radio[i].checked=false;
							}
							for (var i=0; i<radio.length; i++) {
								if (radio[i].value==value) {
									radio[i].checked=true;
									break;
								}
							}
						}
						break;
					case 'hidden' :
					case 'password' :
					case 'text' :
						input.value = value;
						break;
					case 'select-one' :
						for (var i=0; i<input.length; i++) {
							if (input.options[i].value==value) {
								input.options[i].selected=true;
								break;
							}
						}
						break;
					case 'select-multiple' :
						for (var i=0; i<input.length; i++) {
							input.options[i].selected=false;
						}
						for (var i=0; i<value.length; i++) {
							for (var ii=0; ii<input.length; ii++) {
								if (input.options[ii].value==value[i]) {
									input.options[ii].selected=true;
								}
							}
						}
						break;
					case 'vedor-buttongroup-radio':
						var values = input.querySelectorAll("button");
						for (var i=0; i<values.length; i++) {
							values[i].className = values[i].className.replace(/\bvedor-selected\b/, '');
							if (values[i].getAttribute("data-value") == value) {
								values[i].className += " vedor-selected";
							}
						}
						break;
					default :
						break;
				}
			}		
		},

		enable : function() {
			for( var i = 0; i < arguments.length; i++ ) {
				var input = document.getElementById( arguments[i] );
				if (input) {
					input.className = input.className.replace(/\bvdDisabled\b/g, '');
					input.disabled = false;
				}
			}
		},

		disable : function() {
			for( var i = 0; i < arguments.length; i++ ) {
				var input = document.getElementById( arguments[i] );
				if (input) {
					input.className += ' vdDisabled';
					input.disabled = true;
				}
			}
		},
		
		isEnabled : function( id ) {
			var input=document.getElementById(id);
			if (input) {
				return !input.className.match(/\bvdDisabled\b/);
			}
		}
	}


	return self;

})();
