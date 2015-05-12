muze.namespace('vedor.dom.selection');

vedor.dom.selection = ( function() {

	var win = window;
	var w3c = window.getSelection ? true : false;

	var self = { // lets name ourselves self to not conflict that easy with range or selection
		get : function( useWin ) {
			if( !useWin ) {
				useWin = win;
			}
			if( w3c ) {
				if( useWin.getSelection().rangeCount > 0 ) {
					return useWin.getSelection().getRangeAt(0);
				} else {
					return useWin.document.createRange();
				}
			} else {
				return useWin.document.selection.createRange();
			}
		},
		backwards : function( useWin ) {
			if ( !useWin ) {
				useWin = win;
			}
			if (w3c) {
				var sel = useWin.getSelection();
				if (!sel.anchorNode) {
					return false;
				}
				var position = sel.anchorNode.compareDocumentPosition(sel.focusNode);
				if (position == 0) {
					return (sel.anchorOffset > sel.focusOffset);
				} else if (position == 4) { // Node.DOCUMENT_POSITION_PRECEDING) {
					return false;
				} else {
					return true;
				}
			} else {
				// FIXME: Old IE compat goes here;
				return false;
			}
		},
		collapse : function(range, left) {
			if (left!==false) {
				left=true;
			}
			range.collapse(left);
			return range;
		},

		clone : function(range) {
			if (w3c) {
				return range.cloneRange();
			} else {
				return range.duplicate();
			}
		},

		select : function(range) { 
			if( w3c ) {
				var node = self.getNode(range);
				if (node && node.ownerDocument) {
					var sel = node.ownerDocument.defaultView.getSelection();
					sel.removeAllRanges();
					sel.addRange(range);
				}
			} else {
				try { 
					range.select(); // IE is sometimes buggy and likes to barf on you
				} catch( e ) { }
			}
			return range;
		},
		
		selectNode : function(range, el, select) {
			if( w3c ) {
				range.selectNodeContents(el);
			} else {
				range.moveToElementText(el);
			}
			if( select ) {
				self.select(range);
			}
			return range;
		},

		selectRange : function(range, left, right, select) {
			if( w3c ) {
				range.setStart(left.startContainer, left.startOffset);
				if(!right) {
					range.setEnd(left.endContainer, left.endOffset);
				} else {
					range.setEnd(right.startContainer, right.startOffset);
				}
			} else {
				range.setEndPoint('StartToStart', left);
				if (!right) {
					range.setEndPoint('EndToEnd', left);
				} else {
					range.setEndPoint('EndToStart', right);
				}
			}
			if( select ) {
				self.select(range);
			}
			return range;
		},
		
		parentNode : function(range) {
			if( w3c ) {
				var parent = false;
				if( range.collapsed || range.startContainer == range.endContainer ) {
					parent = range.startContainer;
				} else {
					parent = range.commonAncestorContainer;
				}	
				while( parent.nodeType == 3 ) { // text node
					parent = parent.parentNode;
				}
				return parent; 
			} else {
				return range.item ? range.item(0) : range.parentElement();
			}
		},
		
		getNode : function(range) {
			if( w3c ) {
				var node = range.commonAncestorContainer;
				if( !range.collapsed ) {
					/*
						|    <textnode><selected node><textnode>    |
						|    Beide textnodes zijn optioneel, die hoeven er niet te staan.
					*/

					if( range.startContainer == range.endContainer && range.startOffset - range.endOffset < 2 && range.startContainer.hasChildNodes() ) {
						// Case 1: geen tekstnodes.
						// start en eind punt van selectie zitten in dezelfde node en de node heeft kinderen - dus geen text node - en de offset verschillen
						// precies 1 - dus er is exact 1 node geselecteerd.
						node = range.startContainer.childNodes[range.startOffset]; // image achtige control selections.
					} else if ( range.startContainer.nodeType == 3 && range.startOffset == range.startContainer.data.length && 
						range.endContainer.nodeType != 3 && range.endContainer == range.startContainer.parentNode ) 
					{
						// Case 2: tekstnode er voor, niet er achter.
						// start punt zit in een tekst node maar wel helemaal aan het eind. eindpunt zit in dezelfde container waar de textnode ook in zit.
						node = range.endContainer.childNodes[ range.endOffset - 1 ];
					} else if ( range.endContainer.nodeType == 3 && range.endOffset == 0 && 
						range.startContainer.nodeType != 3 && range.startContainer == range.endContainer.parentNode ) 
					{
						// Case 3: tekstnode er achter, niet er voor;
						// Eindpunt zit in een textnode helemaal aan het begin. Startpunt zit in dezelfde container waar de eindpunt-textnode ook in zit
						node = range.startContainer.childNodes[ range.startOffset ];
					} else if ( range.startContainer.nodeType == 3 && range.endContainer.nodeType == 3 
						&& range.startOffset == range.startContainer.data.length && range.endOffset == 0 &&
						range.startContainer.nextSibling == range.endContainer.previousSibling ) 
					{
						// Case 4: tekstnode voor en achter
						// start zit in een tekstnode helemaal aan het eind. eind zit in een tekstnode helemaal aan het begin.
						node = range.startContainer.nextSibling;
					} else if( range.startContainer == range.endContainer ) { 
						// Case 5: bijv. 1 tekstnode met geselecteerde tekst - hierna wordt dan de parentNode als node gezet
						node = range.startContainer;
					}
				}
				while( node && node.nodeType == 3 ) {
					node = node.parentNode;
				}
				return node;
			} else {
				var node = range.item ? range.item(0) : range.parentElement();
				while (node && node.nodeType == 3) {
					node = node.parentNode;
				}
				return node;
			}
		},

		isEmpty : function(range) {
			return self.getHTMLText(range) == '';
		},
		
		getHTMLText : function(range) {
			if( w3c ) {
				var frag = range.cloneContents();
				var div = range.startContainer.ownerDocument.createElement('div');
				div.appendChild(frag);
				var result = div.innerHTML;
				div = null;
				return result;
			} else {
				if( range.item ) {
					var control = range.item(0);
					var textrange = control.ownerDocument.body.createTextRange();
					textrange.moveToElementText(control);
					return textrange.htmlText;
				} else {
					return range.htmlText;
				}
			}
		},

		setHTMLText : function(range, htmltext) {
			if( w3c ) {
				var div = range.startContainer.ownerDocument.createElement('div');
				div.innerHTML = htmltext;
				var frag = range.startContainer.ownerDocument.createDocumentFragment();
				for (var i=0; i < div.childNodes.length; i++) {
					var node = div.childNodes[i].cloneNode(true);
					frag.appendChild(node);
				}
				div = null;
				range = self.replace(range, frag);
			} else {
				if( range.item ) { // control range 
					var control = range.item(0);
					var textrange = control.ownerDocument.body.createTextRange();
					textrange.moveToElementText(control);
					range.execCommand('delete', false);
					range = textrange;
				}
				range.pasteHTML(htmltext);
			}
			return range;
		},

		replace : function(range, el) {
			if( w3c ) {
				range.deleteContents();
				range.insertNode(el);
				// FIXME: definately betere check gebruiken waar de cursor moet komen, gaat mis als je over meer dan 1 textnode een selectie hebt
				if( range.startContainer && range.startContainer.nextSibling ) { // ie behaviour simulatie
					range.selectNode(range.startContainer.nextSibling);
				}
				range.collapse(false);
			} else {
				self.setHTMLText(range, el.outerHTML);
			}
			return range;
		}

	}
	return self;

})();

/* random documentatie





full selectie onder ie ff en chrome shizzle

var range = document.body.createTextRange();
range.moveToElementText(myDiv);
range.select();


Firefox, Opera, WebKit nightlies:

var selection = window.getSelection();
var range = document.createRange();
range.selectNodeContents(myDiv);
selection.removeAllRanges();
selection.addRange(range);


Safari:

var selection = window.getSelection();
selection.setBaseAndExtent(myDiv, 0, myDiv, 1);

*/