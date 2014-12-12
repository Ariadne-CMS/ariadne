muze.namespace('vedor.editor.paste');

muze.require('vedor.dom.cleaner');
muze.require('vedor.dom.selection');
muze.require('muze.event');


vedor.editor.paste = ( function() {

	var cleaner = vedor.dom.cleaner;
	var event = muze.event;
	var selection = vedor.dom.selection;
	var win = window;

	var w3c = window.getSelection ? true : false;
	var beforeCopy = false; 

	var paste = {
		init : function(useWin) {
			win = useWin;
		},
		attach : function(el, callback) {
			if( w3c ) { // only IE has beforepaste
				return;
			}

			// this trick with beforeCopy is because it fires upon rightclick but no actual paste is done.
			event.attach(el, 'beforecopy', function() {
				beforeCopy = true;
			});

			event.attach(el, 'copy', function() {
				beforeCopy = false;
			});

			event.attach(el, 'beforepaste', function() {
				if( beforeCopy ) {
					beforeCopy = false;
					return;
				}
				var inDiv=document.createElement('div');
				inDiv.style.width='1px';
				inDiv.style.height='1px';
				inDiv.style.overflow='hidden';
				inDiv.style.position='absolute';
				inDiv.style.top='0px';
				inDiv.setAttribute('contentEditable',true);
				document.body.appendChild(inDiv);
				var range = selection.get(win);
				inDiv.focus();
				window.setTimeout( function() {
					var inHtml=inDiv.innerHTML;
					if( inHtml == "" ) {
						selection.select(range);
						return;
					}
					if (cleaner.check(inHtml)) {
						inHtml = cleaner.clean(inHtml, 'full');
					}
					selection.select(range);
					selection.setHTMLText(range, inHtml);
					inDiv.parentNode.removeChild(inDiv);
					if (callback) {
						callback();
					}
				}, 200);
			});
		}
	}

	return paste;
})();
