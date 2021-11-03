muze.namespace('vedor.editor.keepalive');

muze.require('muze');

vedor.editor.keepalive = ( function() {
	var keepaliveTimer = null;
	var keepalive = {
		start : function() {
			this.keepaliveTimer = window.setInterval(this.keepalive, 30*60*1000);
		},
		stop : function() {
			clearInterval(this.keepaliveTimer);
		},
		keepalive : function() {
			var result = muze.load(objectURL + 'show.html', true, false);
		}
	}

	return keepalive;
})();
