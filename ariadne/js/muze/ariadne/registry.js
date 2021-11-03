muze.namespace("muze.ariadne.registry", function() {
	var	ARregistry = { };
	return {
		set : function(name, value) {
			//alert('set ' + name + ":" + value);
			ARregistry[name] = new String(value);
		},
		get : function(name) {
			var result;
			if (ARregistry[name]) {
				result=ARregistry[name];
			} else {
				result=0;
			}
			return result;
		}
	}
});
