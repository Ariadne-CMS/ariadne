muze.namespace('vedor.dom.nesting');

vedor.dom.nesting = ( function() {

	function isArray( arr ) {
		return ( arr instanceof Array );
	}

	function inArray(arr, el) {
		for (var i=0; i<arr.length; i++) {
			if (arr[i]==el) {
				return true;
			}
		}
		return false;
	}

	var TEXT_NODE = 3;
	var ELEMENT_NODE = 1;

	var nesting_sets = {
		'inline'	: [ 'TT', 'I', 'B', 'U', 'S', 'STRIKE', 'BIG', 'SMALL', 'FONT', 'EM', 'STRONG', 'DFN', 'CODE', 'SAMP', 'KBD', 'VAR', 'CITE', 'ABBR', 'ACRONYM', 'SUB', 'SUP', 'Q', 'SPAN', 'BDO', 'A', 'OBJECT', 'APPLET', 'IMG', 'BASEFONT', 'BR', 'SCRIPT', 'MAP', 'INPUT', 'SELECT', 'TEXTAREA', 'LABEL', 'BUTTON', 'INS', 'DEL'],
		'inline2'	: [ 'TT', 'I', 'B', 'U', 'S', 'STRIKE', 'EM', 'STRONG', 'DFN', 'CODE', 'SAMP', 'KBD', 'VAR', 'CITE', 'ABBR', 'ACRONYM', 'Q', 'SPAN', 'BDO', 'A', 'BR', 'SCRIPT', 'MAP', 'INPUT', 'SELECT', 'TEXTAREA', 'LABEL', 'BUTTON', 'INS', 'DEL'],
		'inline3'	: [ 'TT', 'I', 'B', 'U', 'S', 'STRIKE', 'BIG', 'SMALL', 'FONT', 'EM', 'STRONG', 'DFN', 'CODE', 'SAMP', 'KBD', 'VAR', 'CITE', 'ABBR', 'ACRONYM', 'SUB', 'SUP', 'Q', 'SPAN', 'BDO', 'OBJECT', 'APPLET', 'IMG', 'BASEFONT', 'BR', 'SCRIPT', 'MAP', 'INPUT', 'SELECT', 'TEXTAREA', 'LABEL', 'BUTTON', 'INS', 'DEL'],
		'inline4'	: [ 'TT', 'I', 'B', 'U', 'S', 'STRIKE', 'BIG', 'SMALL', 'FONT', 'EM', 'STRONG', 'DFN', 'CODE', 'SAMP', 'KBD', 'VAR', 'CITE', 'ABBR', 'ACRONYM', 'SUB', 'SUP', 'Q', 'SPAN', 'BDO', 'A', 'OBJECT', 'APPLET', 'IMG', 'BASEFONT', 'BR', 'SCRIPT', 'MAP', 'INPUT', 'SELECT', 'TEXTAREA', 'BUTTON', 'INS', 'DEL'],
		'block'		: [ 'ADDRESS', 'DIR', 'MENU', 'ISINDEX', 'HR', 'TABLE', 'P', 'H1', 'H2', 'H3', 'H4', 'H5', 'H6', 'PRE', 'UL', 'OL', 'DL', 'DIV', 'CENTER', 'BLOCKQUOTE', 'IFRAME', 'NOSCRIPT', 'NOFRAMES', 'FORM', 'FIELDSET', 'INS', 'DEL' ],
		'block2'	: [ 'ADDRESS', 'DIR', 'MENU', 'ISINDEX', 'HR', 'TABLE', 'P', 'H1', 'H2', 'H3', 'H4', 'H5', 'H6', 'PRE', 'UL', 'OL', 'DL', 'DIV', 'CENTER', 'BLOCKQUOTE', 'IFRAME', 'NOSCRIPT', 'NOFRAMES', 'FIELDSET', 'INS', 'DEL' ],
		'block3'	: [ 'ADDRESS', 'DIR', 'MENU', 'HR', 'TABLE', 'P', 'H1', 'H2', 'H3', 'H4', 'H5', 'H6', 'PRE', 'UL', 'OL', 'DL', 'DIV', 'CENTER', 'BLOCKQUOTE', 'INS', 'DEL' ],
		'block4'	: [ 'H1', 'H2', 'H3', 'H4', 'H5', 'H6', 'PRE', 'UL', 'OL', 'DL', 'DIV', 'CENTER', 'BLOCKQUOTE', 'INS', 'DEL' ]
	}

	var nesting_rules = {
		'ADDRESS'	: [ 'P', nesting_sets['inline']],
		'PRE'		: nesting_sets['inline2'],
		'UL'		: [ 'LI' ],
		'OL'		: [ 'LI' ],
		'LI'		: [ nesting_sets['inline'], 'OL', 'UL' ], // , nesting_sets['block'] ],
		'DIR'		: [ 'LI' ],
		'MENU'		: [ 'LI' ],
		'TABLE'		: [ 'CAPTION', 'COLGROUP', 'COL', 'THEAD', 'TBODY' ],
		'TBODY'		: [ 'TR' ],
		'COLGROUP'	: [ 'COL' ],
		'TR'		: [ 'TH', 'TD' ],
		'TH'		: [ nesting_sets['block'], nesting_sets['inline'] ],
		'TD'		: [ nesting_sets['block'], nesting_sets['inline'] ],
		'P'		: nesting_sets['inline'],
		'H1'		: nesting_sets['inline'],
		'H2'		: nesting_sets['inline'],
		'H3'		: nesting_sets['inline'],
		'H4'		: nesting_sets['inline'],
		'H5'		: nesting_sets['inline'],
		'H6'		: nesting_sets['inline'],
		'DL'		: [ 'DT', 'DD' ],
		'DT'		: nesting_sets['inline'],
		'DD'		: [ nesting_sets['block'], nesting_sets['inline'] ],
		'DIV'		: [ nesting_sets['block'], nesting_sets['inline'] ],
		'CENTER'	: [ nesting_sets['block'], nesting_sets['inline'] ],
		'BLOCKQUOTE'	: [ nesting_sets['block'], nesting_sets['inline'] ],
		'IFRAME'	: [ nesting_sets['block'], nesting_sets['inline'] ],
		'NOSCRIPT'	: [ nesting_sets['block'], nesting_sets['inline'] ],
		'NOFRAMES'	: [ nesting_sets['block'], nesting_sets['inline'] ],
		'FORM'		: [ nesting_sets['block2'], nesting_sets['inline'] ],
		'ISINDEX'	: [],
		'HR'		: [],
		'CAPTION'	: nesting_sets['inline'],
		'COL'		: [],
		'THEAD'		: [ 'TR' ],
		'FIELDSET'	: [ nesting_sets['block'], nesting_sets['inline'], 'LEGEND' ],
		'LEGEND'	: nesting_sets['inline'],
		// inline elements
		'TT'		: nesting_sets['inline'],
		'I'		: nesting_sets['inline'],
		'B'		: nesting_sets['inline'],
		'U'		: nesting_sets['inline'],
		'S'		: nesting_sets['inline'],
		'STRIKE'	: nesting_sets['inline'],
		'BIG'		: nesting_sets['inline'],
		'SMALL'		: nesting_sets['inline'],
		'FONT'		: nesting_sets['inline'],
		'EM'		: nesting_sets['inline'],
		'STRONG'	: nesting_sets['inline'],
		'DFN'		: nesting_sets['inline'],
		'CODE'		: nesting_sets['inline'],
		'SAMP'		: nesting_sets['inline'],
		'KBD'		: nesting_sets['inline'],
		'VAR'		: nesting_sets['inline'],
		'CITE'		: nesting_sets['inline'],
		'ABBR'		: nesting_sets['inline'],
		'ACRONYM'	: nesting_sets['inline'],
		'SUB'		: nesting_sets['inline'],
		'SUP'		: nesting_sets['inline'],
		'Q'		: nesting_sets['inline'],
		'SPAN'		: nesting_sets['inline'],
		'BDO'		: nesting_sets['inline'],
		'A'		: nesting_sets['inline3'],
		'OBJECT'	: [ 'PARAM', nesting_sets['block'], nesting_sets['inline'] ],
		'APPLET'	: [ 'PARAM', nesting_sets['block'], nesting_sets['inline'] ],
		'IMG'		: [],
		'BASEFONT'	: [],
		'BR'		: [],
		'SCRIPT'	: [],
		'MAP'		: [ 'AREA', nesting_sets['block'], nesting_sets['inline'] ],
		'INPUT'		: [],
		'SELECT'	: [ 'OPTGROUP', 'OPTION' ],
		'OPTGROUP'	: [ 'OPTION' ],
		'TEXTAREA'	: [],
		'LABEL'		: nesting_sets['inline4'],
		'BUTTON'	: [ nesting_sets['block3'], nesting_sets['inline3']],
		'DEL'		: [ nesting_sets['block'], nesting_sets['inline'] ],
		'INS'		: [ nesting_sets['block'], nesting_sets['inline'] ]
	}

	var auto_closed = {
		'ISINDEX' 	: true,
		'HR'		: true,
		'COL'		: true,
		'IMG'		: true,
		'BASEFONT'	: true,
		'BR'		: true,
		'INPUT'		: true
	}

	var oblig_child = { /* always add the child tag when setting this tagName */
		'OL'		: 'LI',
		'UL'		: 'LI',
		'ol'		: 'li',
		'ul'		: 'li'
	}

	var oblig_parent = { /* always add this parent when setting this tagName */
		'DT'		: 'DL',
		'DD'		: 'DL',
		'dt'		: 'dl',
		'dd'		: 'dl'
	}

	return {
		allowChild : function(parent, child) {
			if (parent.nodeType) {
				parent = new String(parent.tagName);
			}
			if (child.nodeType) {
				child = new String(child.tagName);
			}
			var list = nesting_rules[parent.toUpperCase()];
			if (list) {
				for (var i=0; i<list.length; i++) {
					if (isArray(list[i])) {
						var found = inArray(list[i], child.toUpperCase());
						if (found) {
							return true;
						}
					} else {
						if (list[i]==child.toUpperCase()) {
							return true;
						}
					}
				}
			}
			return false;
		},
		canHaveChildren : function(name) {
			if (name.tagName) {
				name = name.tagName;
			}
			return nesting_rules[name.toUpperCase()].length>0;
		},
		canHaveContent : function(name) {
			if (name.tagName) {
				name = name.tagName;
			}
			return !auto_closed[name.toUpperCase()];
		},
		isBlock : function(name) {
			if (name.tagName) {
				name = name.tagName;
			}
			return name.match(/^(P|H[1-6]|UL|OL|DIR|MENU|DL|PRE|DIV|CENTER|BLOCKQUOTE|ADDRESS|LI|TD|TH)$/i);
		},
		isEmpty : function(el) {
			// el != null and if nodeType text and contains only whitespace, return true
			var result = false;
			if (el && el.nodeType==TEXT_NODE) {
				var content = new String(el.nodeValue);
				result = content.match(/^[\n\r]*$/m);
			}
			return result;
		},
		isTextNode : function(el) {
			return el && el.nodeType==TEXT_NODE;
		},
		obligatoryChild : function(name) {
			if (name.tagName) {
				name = name.tagName;
			}
			return oblig_child[name.toUpperCase()];
		}
	}
})();