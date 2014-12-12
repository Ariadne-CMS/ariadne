muze.namespace('vedor.editor.bookmarks');

muze.require('vedor.dom.selection');
muze.require('vedor.dom.nesting');

vedor.editor.bookmarks = ( function() {

	var win = window;
	var doc = window.document;
	var selection = vedor.dom.selection;
	var nesting = vedor.dom.nesting;
	
	var TEXT_NODE = 3;
	var ELEMENT_NODE = 1;
	
	var bookmarks = {
	
		init : function( useWin ) {
			win = useWin;
			doc = useWin.document;
		},
	
		normalize : function(bookmark, side) {
			// this method moves the left and right bookmarks so that they best enclose the selection
			function moveLeft() {
				do {
					movedDown = false;
					while (nesting.isEmpty(bookmark.previousSibling)) {
						bookmark.swapNode(bookmark.previousSibling);
					}
					if (bookmark.previousSibling
						&& bookmark.previousSibling.nodeType==ELEMENT_NODE
						&& nesting.canHaveContent(bookmark.previousSibling.tagName)) {
				
						bookmark.previousSibling.appendChild(bookmark);
						movedDown = true;
					}
				} while (movedDown);
			}

			function moveRight() {
				do {
					movedDown = false;
					while (nesting.isEmpty(bookmark.nextSibling)) {
						bookmark.swapNode(bookmark.nextSibling);
					}
					if (bookmark.nextSibling 
						&& bookmark.nextSibling.nodeType==ELEMENT_NODE 
						&& nesting.canHaveContent(bookmark.nextSibling.tagName)) {
						
						bookmark.nextSibling.insertBefore(bookmark, bookmark.nextSibling.firstChild);
						movedDown = true;
					} else if (!bookmark.nextSibling && bookmark.parentNode && bookmark.parentNode.parentNode ) {
						if (bookmark.parentNode.nextSibling) {
							bookmark.parentNode.parentNode.insertBefore(bookmark, bookmark.parentNode.nextSibling);
						} else {
							bookmark.parentNode.parentNode.appendChild(bookmark);
						}
						movedDown = true;
					}
				} while (movedDown);
			}
			
			if (side=='left') {
				moveRight();
			} else {
				moveLeft();
			}
		},
		getTag : function(side) {
			var span = doc.createElement('SPAN');
			if (side=='right') {
				span.id='vdBookmarkRight';
			} else {
				span.id='vdBookmarkLeft';
			}
			return span;
		},
		findTag : function(side) {
			if (side=='right') {
				return doc.getElementById('vdBookmarkRight');
			} else {
				return doc.getElementById('vdBookmarkLeft');
			}
		},
		set : function(sel) {
			// this method inserts the left and right bookmarks based on the given selection.
			var left = selection.clone(sel);
			var right = selection.clone(sel);
			// now remove any stray bookmarks left from an interrupted script
			bookmarks.remove();

			selection.collapse(right, false);
			var rightTag = bookmarks.getTag('right');
			selection.replace(right, rightTag);

			selection.collapse(left);
			var leftTag = bookmarks.getTag('left');
			selection.replace(left, leftTag);

			//FIXME: this is an IE only fix, check behaviour of other browsers, we may need to add extra cases
			if( !window.getSelection ) {		
				bookmarks.normalize(leftTag, 'left');
				bookmarks.normalize(rightTag, 'right');
			}
		},
		select : function(el) {
			// this method turns the bookmarks back into a selection
			if (!el) {
				el = doc;
			}
			var leftTag = bookmarks.findTag('left');
			var rightTag = bookmarks.findTag('right');
			var leftSel = selection.clone(selection.get(win));
			var rightSel = selection.clone(selection.get(win));
			var leftRange = selection.collapse(selection.selectNode(leftSel, leftTag, true));
			var rightRange = selection.collapse(selection.selectNode(rightSel, rightTag, true), true);
			var totalSel = selection.clone(selection.get(win));
			return selection.selectRange(totalSel, leftRange, rightRange, true);
		},
		remove : function() {
			var leftTag = bookmarks.findTag('left');
			while (leftTag) {
				while (leftTag.firstChild) {
					leftTag.parentNode.insertBefore(leftTag.firstChild, leftTag);
				}
				leftTag.parentNode.removeChild(leftTag);
				leftTag = bookmarks.findTag('left');
			}
			var rightTag = bookmarks.findTag('right');
			while (rightTag) {
				while (rightTag.firstChild) {
					rightTag.parentNode.insertBefore(rightTag.firstChild, rightTag);
				}
				rightTag.parentNode.removeChild(rightTag);
				rightTag = bookmarks.findTag('right');
			}
		}
	}
	return bookmarks;
})();