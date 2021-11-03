	//
	// Utility functions
	//

	function setFormat(command, value) {
		var blockRe=new RegExp('(H[1-7])|P');
		var skipExecCommand=false;
		var field=getEditableField();
		if (!field) {
			return;
		}
		registerChange(field.id);
		
		var sel = vdSelectionState.get();
		
		var target = vdEditPane.contentWindow.document;
		if( !window.getSelection && target.selection.type != "None" ) { // make sure we execCommand on the selection for IE.
			target = sel;
		}

		target.execCommand(command, false, value);

		vdSelectionState.restore();

		vdStoreUndo();
		vdEditPane_DisplayChanged();
		return true;
	}

	function setFormatStyle(styleInfo) {
		var field=getEditableField();
		if (!field) {
			return false;
		}

		vedor.editor.styles.init(vdEditPane.contentWindow);
		vedor.editor.styles.format(styleInfo, field);

		vdStoreUndo();

		vdEditPane_DisplayChanged();
		return true;
	}

	function getBlock(el, BlockElements) {
		if (!BlockElements) {
			BlockElements="|H1|H2|H3|H4|H5|H6|P|PRE|LI|TD|DIV|BLOCKQUOTE|DT|DD|TABLE|HR|IMG|";
		}
		while ((el!=null) && (BlockElements.indexOf("|"+el.tagName+"|")==-1)) {
			el=el.parentNode;
		}
		return el;
	}

	function getBlockFormat() {
		var result='';
		var sel = vdSelectionState.get();
		if ( sel && !vdSelectionState.getControlNode(sel) ) {
			var parentBlock=getBlock(vdSelection.parentNode(sel));
			if (parentBlock) {
				switch(parentBlock.tagName) {
					case 'LI':
						result=parentBlock.parentNode.tagName;
						if (parentBlock.parentNode.className) {
							result+='.'+parentBlock.parentNode.className;
						}
					default:
						result=parentBlock.tagName;
						if (parentBlock.className) {
							result+='.'+parentBlock.className;
						}
				}
			}
		}
		return result;
	}


	function getSize(size) {
		if (size) {
			var sizeRE=new RegExp("([0-9]*[.]?[0-9]+)(%|em|ex|px|in|cm|mm|pi|pt)?","i");
			var sizeString=new String(size);
			var results=sizeString.match(sizeRE);
			if (results && results.length) {
				if (results.length==1) {
					results[1]='px';
				}
			}
		} else {
			var results=false;
		}
		return results;
	}

