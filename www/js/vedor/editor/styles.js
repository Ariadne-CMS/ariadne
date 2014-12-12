muze.namespace('vedor.editor.styles');

muze.require('vedor.dom.selection');
muze.require('vedor.dom.nesting');
muze.require('vedor.editor.bookmarks');

vedor.editor.styles = ( function() {

	var win = window;
	var doc = window.document;
	var selection = vedor.dom.selection;
	var nesting = vedor.dom.nesting;
	var bookmarks = vedor.editor.bookmarks;

	function isArray( arr ) {
		return ( arr instanceof Array );
	}

	var TEXT_NODE = 3;
	var ELEMENT_NODE = 1;

	function TreeWalker(root, start, end, tagName) {
		// start must be a valid child of tagName for now
		this.root = root;
		this.start = start;
		this.end = end;
		this.currentNode = start;
		this.tagName = tagName;
		var obligChild = nesting.obligatoryChild(tagName); // check for LI instead of OL/UL with allowChild
		if (obligChild) {
			this.tagName = obligChild;
		}
		this.containsEndNode = function(el) {
			if (el==end) {
				return true;
			} else {
				var current = this.end;
				while (current && current!=el && current!=this.root) {
					current = current.parentNode;
				}
				return current==el;
			}
		},
		this.next = function() {
			var nodeBag = new Array();
			var lastNode = null;
			while (this.currentNode && !this.containsEndNode(this.currentNode) && 
					( nesting.isTextNode(this.currentNode) || 
					  nesting.allowChild(this.tagName, this.currentNode) ) ) {
				nodeBag.push(this.currentNode);
				lastNode = this.currentNode;
				this.currentNode = this.currentNode.nextSibling;
			}
			if (!this.currentNode && lastNode) {
				this.currentNode = this.findNextParentSibling(lastNode);
			}
			if (this.currentNode && this.containsEndNode(this.currentNode)) {
				// if the currentNode is allowed as a child of the new tag, split the node 
				// otherwise, simply move the currentNode inside it and return the nodeBag.
				if (!nesting.allowChild(this.tagName, this.currentNode)) {
					this.currentNode = this.findValidChildNode(this.currentNode)
				} else {
					var parent = this.end.parentNode;
					var split = this.end;
					while (parent!=this.currentNode.parentNode) {
						DOM.split(parent, 'after', split);
						split = parent;
						parent = parent.parentNode;
					}
					nodeBag.push(this.currentNode);
					this.currentNode = null;
				}
			} else if (this.currentNode) {
				this.currentNode = this.findNextValidNode(this.currentNode);
			}
			if (nodeBag.length) {
				return nodeBag;
			} else {
				return null;
			}
		}
		this.findNextParentSibling = function(el) {
			var currentNode = el;
			var parent = el.parentNode;
			while (parent && parent != this.root && !parent.nextSibling) {
				parent = parent.parentNode;
			}
			if (parent && parent != this.root ) {
				return parent.nextSibling;
			} else {
				return null;
			}
		}
		this.findValidNode = function(el) {
			var currentNode = el;
			var nextNode = this.findValidChildNode(currentNode);
			while (currentNode && !nextNode ) {
				if (currentNode!=this.root) {
					currentNode = currentNode.parentNode;
					nextNode = this.findValidChildNode(currentNode);
				}
			}
			return nextNode;
		}
		this.findNextValidNode = function(el) {
			var currentNode = el;
			var nextNode = this.findValidChildNode(currentNode);
			while (currentNode && !nextNode ) {
				if (currentNode!=this.root) {
					currentNode = this.findNextParentSibling(currentNode);
					if (currentNode) {
						nextNode = this.findValidChildNode(currentNode);
					}
				}
			}
			return nextNode;
		}
		this.findValidChildNode = function(el) {
			var currentNode = el;
			var validNode = null;
			if (el && el != this.end) {
				if (nesting.isTextNode(currentNode) || nesting.allowChild(this.tagName, currentNode.tagName)) {
					validNode = currentNode;
				} else if (currentNode.firstChild) {
					validNode = this.findValidChildNode(currentNode.firstChild);
				}
				if (!validNode && currentNode.nextSibling) {
					validNode = this.findValidChildNode(currentNode.nextSibling);
				}
			}
			return validNode;
		}
	}


	var DOM = function() {
		return {
			walk : function(root, start, end, tagName) {
				return new TreeWalker(root, start, end, tagName);
			},
			split : function(parent, direction, referenceChild) {
				// Bij direction = 'before' een nieuwe parent toevoegen voor de huidige
				// bij direction='after' een nieuwe toevoegen na de huidige. Hiermee wordt de 'huidige' parent nooit direct leeg
				
				var newParent = parent.cloneNode(false); // shallow copy
				if (direction=='after') {
					while(referenceChild.nextSibling) {
						newParent.appendChild(referenceChild.nextSibling);
					}
					if (newParent.firstChild) { // only add the new parent to the DOM if it has content
						if (parent.nextSibling) {
							parent.parentNode.insertBefore(newParent, parent.nextSibling);
						} else {
							parent.parentNode.appendChild(newParent);
						}
					} else {
						newParent = null;
					}
				} else { // before
					while (referenceChild.previousSibling) {
						newParent.insertBefore(referenceChild.previousSibling, newParent.firstChild);
					}
					if (newParent.firstChild && !nesting.isEmpty(newParent.firstChild)) { // only add the new parent to the DOM if it has content
						parent.parentNode.insertBefore(newParent, parent);
					} else {
						newParent = null;
					}
				}
				return newParent;
			},
			wrap : function(nodes, wrapper, root) {
				if (!root) {
					root = doc.body;
				}
				var clonedWrapper = wrapper.cloneNode(false);
				var obligChild = nesting.obligatoryChild(clonedWrapper);
				if (obligChild) {
					// special case, we must wrap the nodes in two tags, e.g. <oL><li>
					var obligChildEl = doc.createElement(obligChild);
					clonedWrapper.appendChild(obligChildEl);
					var newParent = obligChildEl;
				} else {
					var newParent = clonedWrapper;
				}
				if (!isArray(nodes)) {
					nodes = new Array(nodes);
				}
				var firstNode = nodes[0];
				var firstParentNode = firstNode.parentNode;
				var currentParentNode = firstParentNode;
				while (!nesting.allowChild(currentParentNode, wrapper) && currentParentNode!=root) {
					// split the parent if needed
					if (currentParentNode.firstChild!=firstNode) {
						DOM.split(currentParentNode, 'before', firstNode);
					}
					firstNode = currentParentNode;
					currentParentNode = currentParentNode.parentNode;
				}
				if (nesting.allowChild(currentParentNode, wrapper)) {
					// insert the wrapper
					currentParentNode.insertBefore(clonedWrapper, firstNode);
					// move the nodes to the wrapper
					for (var i=0; i<nodes.length; i++) {
						newParent.appendChild(nodes[i]);
					}
					// remove any empty elements caused by moving all child nodes out of parent nodes
					var tempNode = null;
					// FIXME: firstChild isEmpty checken is niet goed genoeg, hij moet ook geen nextSiblings hebben die niet empty zijn
					// FIXME: dit geld voor webkit browsers
					while (firstParentNode!=root && (!firstParentNode.firstChild || nesting.isEmpty(firstParentNode.firstChild))) {
						if ( !firstParentNode.firstChild ) {
							if ( firstParentNode.tagName!='TD' && firstParentNode.tagName!='TH' ) {
								tempNode = firstParentNode;
								firstParentNode = firstParentNode.parentNode;
								firstParentNode.removeChild(tempNode);
							} else {
								break;
							}
						} else if ( nesting.isEmpty(firstParentNode.firstChild ) ) {
							firstParentNode.removeChild(firstParentNode.firstChild );
						}
					}
				}
				// if we added an obligatory child (li), check if the previous sibling is the same tagName+className as the current wrapper
				// if so, the li should probably be appended to it instead of our new wrapper.
				if (obligChild) {
					var previousEl = clonedWrapper.previousSibling;
					if (previousEl && previousEl.tagName==clonedWrapper.tagName && 
						clonedWrapper.className==previousEl.className) {
						previousEl.appendChild(newParent);
						clonedWrapper.parentNode.removeChild(clonedWrapper);
					}
				}
			},
			unwrap : function(root, start, end, wrapper) {
				// TODO: remove all occurances of the wrapper tag inside the root, between the start and end nodes
			}
		}
	}();


	function setFormat(styleInfo, root) {
		function getFittingParent(sel, tagName, root) {
			var parent = selection.parentNode(sel);
			if (nesting.isBlock(tagName)) {
				while (!nesting.isBlock(parent.tagName) && parent!=root) {
					parent = parent.parentElement;
				}
			}
			return parent;
		}
		
		if (styleInfo=='.') { // clear all styles
			var sel = selection.get(win);
			sel.execCommand('RemoveFormat');
		} else {
			var tagName = styleInfo.split('.')[0];
			var className = styleInfo.split('.')[1];
			var sel = selection.clone(selection.get(win));
			if (selection.isEmpty(sel)) {
				var parent = getFittingParent(sel, tagName, root);
				if (!tagName || tagName=='*') {
					tagName = parent.tagName;
				}
				sel = selection.selectNode(selection.clone(sel), parent, true);
			}
			if (!tagName || tagName=='*') {
				tagName = 'span';
			}
			bookmarks.set(sel);
			applyStyle(tagName, className, root);
			bookmarks.select();
			bookmarks.remove();
		}
	}

	function applyStyle(tagName, className, root) {
		var leftTag = bookmarks.findTag('left', root);
		var rightTag = bookmarks.findTag('right', root);
		var treeWalker = DOM.walk(root, leftTag, rightTag, tagName);
		var wrapper = doc.createElement(tagName);
		if (className) {
			wrapper.className = className;
		}
		DOM.unwrap(root, wrapper); // make sure we don't nest the same tagName/className tags
	//	alert( 'BEFORE: ' + root.parentNode.innerHTML );
		
		var nodeBag = null;
		while (nodeBag = treeWalker.next()) {
			DOM.wrap(nodeBag, wrapper, root);
		}
	}
	
	return {
		format : function(styleInfo, root) {
			return setFormat(styleInfo, root);
		},
		init : function( useWin ) {
			win = useWin;
			doc = useWin.document;
			bookmarks.init(useWin);
		}
	}

})();