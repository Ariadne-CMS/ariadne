muze.namespace('vedor.widgets.fieldsets');

muze.require('muze.event');

vedor.widgets.fieldsets = ( function() { 

	var event = muze.event;

	var fieldsets = {

		show : function( fs ) {
			if( document.all ) {
				fs.style.height = '';
			} else {
				fs.style.height = '';
				fs.style.marginBottom = '';
			}
			fs.className = 'open';
			fieldsets.hideHandle(fs);
			fieldsets.showHandle(fs);
		},

		hide : function( fs ) {
			if( document.all ) {
				fs.style.height = '16px';
			} else {
				fs.style.marginBottom = '-11px';
				fs.style.height = '0px';
			}
			fs.style.overflow = 'hidden';
			fs.className = 'closed';
			fieldsets.hideHandle(fs);
			fieldsets.showHandle(fs);
		},

		showHandle : function( fs ) {
			if (fs.style.height) {
				if( !fs.closeImage ) {
					image = fieldsets.getCloseHandle(fs);
					fs.closeImage = image;
					fs.openImage = null;
				} else{
					image = fs.closeImage;
				}
			} else {
				if( !fs.openImage ) {
					image = fieldsets.getOpenHandle(fs);
					fs.openImage = image;
					fs.closeImage = null;
				} else {
					image = fs.openImage;
				}
				fs.style.overflow = 'hidden';
			}
			image.unselectable='on';
			fs.insertBefore(image, fs.firstChild);
		},

		hideHandle : function( fs ) {
			var el = fs.firstChild;
			while (el && el.className=='vdOpenClose') {
				fs.removeChild(el);
				el = fs.firstChild;
			}
			fs.openImage = null;
			fs.closeImage = null;
		},

		getOpenHandle : function( fs ) { // FIXME: should not be using an im, but a div with a background via css
			var image = fs.ownerDocument.createElement('IMG');
			image.src = fs.upImgSrc;
			image.title = 'Hide'; // FIXME: nls
			image.className = 'vdOpenClose';
			image.onclick = function() { 
				fieldsets.hide(fs); 
			}
			return image;
		},

		getCloseHandle : function( fs ) { // FIXME: should not be using an img, but a div with background via css
			var image = fs.ownerDocument.createElement('IMG');
			image.src = fs.downImgSrc;
			image.title = 'Show'; // FIXME: nls
			image.className = 'vdOpenClose';
			image.onclick = function() { 
				fieldsets.show(fs); 
			}
			return image;
		},


		init : function( doc, upImgSrc, downImgSrc ) {
			// add hide/show buttons to all fieldsets
			var fslist = doc.getElementsByTagName('FIELDSET');
			var legend = null;
			for (var i=fslist.length-1; i>=0; i--) {
				var fs = fslist[i];
				if (!fs.className.match(/\bvdFixed\b/)) {
					var hiding = [];
					var showing = [];
					fs.upImgSrc = upImgSrc;
					fs.downImgSrc = downImgSrc;
					event.attach(fs, 'mouseover', function(fs, i) {
						return function() {
							showing[i] = true;
							hiding[i] = false;
							window.setTimeout(function() {
								if (showing[i]) {
									fieldsets.showHandle(fs);
									showing[i] = false;
								}
							}, 500);
						}
					}(fs, i));
					event.attach(fs, 'mouseout', function(fs, i) {
						return function() {
							if (!fs.style.height) {
								hiding[i] = true;
								showing[i] = false;
								window.setTimeout(function() {
									if (hiding[i]) {
										fieldsets.hideHandle(fs);
										hiding[i] = false;
									}
								}, 500);
							}
						}
					}(fs, i));
					try {
						legend = fs.getElementsByTagName('LEGEND')[0];
						event.attach(legend, 'click', function(fs, i) {
							return function(evt) {
								if (!fs.style.height) {
									fieldsets.hide(fs);
								} else {
									fieldsets.show(fs);
								}
								return event.cancel(evt);
							}
						}(fs, i));
						legend.style.cursor = 'pointer';
					} catch(e) {
					}
				}
			}
		}

	}
	return fieldsets;
})();
