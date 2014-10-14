muze.namespace("muze.ariadne.cookie", function() {
	return {
		set : function(name, value) {
			var today = new Date();
			var expiry = new Date(today.getTime() + 365 * 24 * 60 * 60 * 10000);
			var s = '';
			s += escape(name) + '=' + escape(value) + ';'
			s += 'path=/;';
			s += 'expires='+expiry.toGMTString();
			document.cookie = s;
		},
		get : function(name) {
			var result;
			var cookie = muze.ariadne.cookie.getarray();
			if (cookie[name]) {
				result=cookie[name];
			} else {
				result=0;
			}
			return result;
		},
		getarray : function() {
			var cookies = document.cookie.split(";");
			var result = { }
			for( i=0; i<cookies.length; i++) {
				var cookie = cookies[i];
				cookie = cookie.replace(/^\s+/, '');
				var cookie_array = cookie.split('=');
				result[unescape(cookie_array[0])] = unescape(cookie_array[1]);
			}
/*
			var s = '';
			for( n in result ) {
				s += n + '='+result[n] + '\n';
			}
			alert(s);
*/
			return result;
		}
	}
});
