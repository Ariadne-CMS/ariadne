muze.namespace('vedor.editor.selection');

muze.require('vedor.dom.selection');

vedor.editor.selection = ( function() {
	
	var win = window;
	var savedRange = null;
	var domSelection = vedor.dom.selection;

	var controlTags = {
		IMG : true,
		OBJECT : true,
		EMBED : true
	}

	function isUneditable( node ) {
		return node.getAttribute('contentEditable') == 'false';
	}

	function isControlTag( node ) {
		var tag = node.tagName.toUpperCase();
		return ( controlTags[tag] ? true : false );
	}

	var self = {
		init : function( useWin ) {
			win = useWin;
		},
		save : function( range ) {
			if( !range ) {
				range = domSelection.get(win);
			}
			savedRange = range;
		},
		restore : function( range ) {
			if( !range ) {
				range = savedRange;
			}
			if( range ) {
				domSelection.select(range);
			}
		},
		get : function() {
			return savedRange ? savedRange : domSelection.get(win);
		},
		getControlNode : function( range ) {
			if( !range ) {
				range = self.get();
			}
			if( range ) {
				var node = domSelection.getNode(range);
				if( isControlTag(node) || isUneditable(node) ) { // this could use a lot more probably
					return node;
				}
			}
			return false;
		},
		remove : function() {
			savedRange = null;
		}
	}

	return self;

})();
