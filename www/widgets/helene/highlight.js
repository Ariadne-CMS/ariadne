    /******************************************************************
     highlight.js                                           Muze Helene
     ------------------------------------------------------------------
     Author: Muze (info@muze.nl)
     Date: 28 februari 2004

     Copyright 2002 Muze

     This file is part of Helene.

     Helene is free software; you can redistribute it and/or modify
     it under the terms of the GNU General Public License as published 
     by the Free Software Foundation; either version 2 of the License, 
     or (at your option) any later version.
 
     Helene is distributed in the hope that it will be useful,
     but WITHOUT ANY WARRANTY; without even the implied warranty of
     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
     GNU General Public License for more details.

     You should have received a copy of the GNU General Public License
     along with Helene; if not, write to the Free Software 
     Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  
     02111-1307  USA

    -------------------------------------------------------------------

     This file contains the syntax highlighting parser

    ******************************************************************/
	/* states */
	var YY_STATE_HTML = 0;
	var YY_STATE_PINP = 1;
	var YY_STATE_DQSTRING = 2;
	var YY_STATE_SQSTRING = 3;

	/* tokens */
	var T_VAR = 0;
	var T_IDENT = 1;
	var T_FUNCTION = 2;
	var T_TOKEN = 3;
	var T_UNKNOWN = 4;
	var T_PINP_START = 5;
	var T_PINP_BLOCK = 6
	var T_PINP_END = 7;
	var T_SPACE = 8;
	var T_DQUOTE = 9
	var T_SQUOTE = 10;
	var T_ESCAPE = 11;
	var T_SPECIAL_CHAR = 12;
	var T_OPERATOR = 13;
	var T_SINGLE_COMMENTS = 14;
	var T_BLOCKCOMMENT = 15;
	var T_BLOCKCOMMENT_END = 16;
	var T_PHP_START = 17;
	var T_PHP_END = 18;
	var T_SCRIPT_START = 19;
	var T_SCRIPT_END = 20;

	var hLines = new Array();
	var debug = 0;

	function hLineToken(tokenType, tokenData) {
		this.type = tokenType;
		this.data = tokenData;
		this.reallength = tokenData.length;

		switch (this.data) {
			case '<':	
				this.data='&lt;';
			break;
			case '>':
				this.data='&gt;';
			break;
			case '&':
				this.data='&amp;';
			break;
		}
		switch (this.type) {
			case T_PINP_START:
			case T_PHP_START:
			case T_PINP_END:
			case T_PHP_END:
			case T_SCRIPT_START:
			case T_SCRIPT_END:
				this.data = this.data.replace(/[<]/g, '&lt;');
				this.data = this.data.replace(/[>]/g, '&gt;');
			case T_SPACE:
				this.data = this.data.replace(/[ ]/g, '&nbsp;');
				this.data = this.data.replace(/[\t]/g, '&nbsp;&nbsp;&nbsp;&nbsp;');
			break;
		}
	}

	function getToken(sData) {
		var re, match;

		/* white space */
		re = /^([\t ]+)/;
		match = re.exec(sData);
		if (match) {
			result = new hLineToken(T_SPACE, match[1]);
			return result;
		}

		/* variable or ident */
		re = /^([$]|->)?([a-z0-9][a-z0-9_]*)/i;
		match = re.exec(sData);
		if (match) {
			if (match[1]) {
				result = new hLineToken(T_VAR, match[0]);
			} else {
				result = new hLineToken(T_IDENT, match[2]);
			}
			return result;
		}

		/* single tokens */
		re = /^([(){},"'\\])/;
		match = re.exec(sData);
		if (match) {
			switch (match[1]) {
				case '\\':
					result = new hLineToken(T_ESCAPE, match[1]); 
				break;
				case '"':
					result = new hLineToken(T_DQUOTE, match[1]); 
				break;
				case "'":
					result = new hLineToken(T_SQUOTE, match[1]);
				break;
				default:
					result = new hLineToken(T_SPECIAL_CHAR, match[1]);
				break;
			}
			return result;
		}

		re = /^((\/[*])|([*]\/))/;
		match = re.exec(sData);
		if (match) {
			if (match[2]) {
				result = new hLineToken(T_BLOCKCOMMENT, match[2]);
			} else {
				result = new hLineToken(T_BLOCKCOMMENT_END, match[3]);
			}
			return result;
		}

		/* comments */
		re = /^(\/\/.*)/;
		match = re.exec(sData);
		if (match) {
			result = new hLineToken(T_SINGLE_COMMENTS, match[1]);
			return result;
		}

		/* php end tags */
		re = /^([\?\%]>)/;
		match = re.exec(sData);
		if (match) {
			result = new hLineToken(T_PHP_END, match[0]);
			return result;
		}



		re = /^([\-\+\.\*\/\=\%])/;
		match = re.exec(sData);
		if (match) {
			result = new hLineToken(T_OPERATOR, match[1]);
			return result;
		}


		/* pinp/php tags */
		re = /^((<(\/)?pinp>)|(<[%?]php|<script[^>]+language[^>]*=[^>]*php[^>]*>))/i;
		match = re.exec(sData);
		if (match) {
			if (match[3]) {
				result = new hLineToken(T_PINP_END, match[0]);
			} else
			if (match[2]) {
				result = new hLineToken(T_PINP_START, match[0]);
			} else {
				result = new hLineToken(T_PINP_START, match[0]);
			}
			return result;
		}

		/* javascript */
		re = /^<(\/)?script[^>]*>/;
		match = re.exec(sData);
		if (match) {
			if (match[1]) {
				result = new hLineToken(T_SCRIPT_END, match[0]);
			} else {
				result = new hLineToken(T_SCRIPT_START, match[0]);
			}
			return result;
		}


		return new hLineToken(T_UNKNOWN, sData.charAt(0));
	}

	function hLineParseString(sData) {
		var token;
		this.tokens = new Array();

		while (sData != '') {
			token = getToken(sData);
			this.tokens[this.tokens.length] = token;	
			sData=sData.substring(token.reallength);
		}
	}

	function getElmSpan(token) {
		var result = '';
		switch (token.type) {
			case T_VAR:
				result = 'class="h_var"';
			break;
			case T_PINP_START:
			case T_PINP_END:
			case T_PHP_START:
				result = 'class="h_pinp_block"';
			break;
			case T_SCRIPT_START:
				result = 'class="h_script_block"';
			break;
			case T_IDENT:
				result = 'class="h_ident"';
			break;
			case T_DQUOTE:
				result = 'class="h_doublequote"';
			break;
			case T_SQUOTE:
				result = 'class="h_singlequote"';
			break;
			case T_SPECIAL_CHAR:
				result = 'class="h_special_char"';
			break;
			case T_OPERATOR:
				result = 'class="h_operator"';
			break;
			case T_SINGLE_COMMENTS:
				result = 'class="h_single_comments"';
			break;
			case T_BLOCKCOMMENT:
				result = 'class="h_blockcomment"';
			break;
		}
		return result;
	}

	function hLineDoHighlight(callback) {
		var state = new Array();
		var result = '';
		if (this.lineNo) {
			/* load parent state */
			state = state.concat(hLines[this.lineNo-1].getEndState());
//			alert((this.lineNo-1)+':'+state.length);
		}
		for (var i = 0; i<state.length; i++) {
			if (!state[i].noMultiLine) {
				result += '<span '+getElmSpan(state[i])+'>';
			}
		}
		if (this.tokens) {
			for (var i=0; i<this.tokens.length; i++) {
				var cState = 0;
				var token = this.tokens[i];
				if (state.length) {
					cState = state[state.length-1].type;
				}

				switch (cState) {
					case 0:
						switch (token.type) {
							case T_PHP_START:
							case T_PINP_START:
								if (i == 1 && this.tokens[i-1].type == T_SPACE) {
									result = '<span '+getElmSpan(token)+'>' + result + '<span class="h_pinp">' + token.data + '</span>';
								} else {
									result += '<span '+getElmSpan(token)+'>' + '<span class="h_pinp">' + token.data + '</span>';
								}
								state.push(token);
							break;
							case T_SCRIPT_START:
								if (i == 1 && this.tokens[i-1].type == T_SPACE) {
									result = '<span '+getElmSpan(token)+'>' + result + '<span class="h_script">' + token.data + '</span>';
								} else {
									result += '<span '+getElmSpan(token)+'>' + '<span class="h_script">' + token.data + '</span>';
								}
								state.push(token);
							break;
							default:
								result += token.data;
							break;
						}
					break;
					case T_SCRIPT_START:
						switch (token.type) {
							case T_PHP_START:
							case T_PINP_START:
								if (i == 1 && this.tokens[i-1].type == T_SPACE) {
									result = '<span '+getElmSpan(token)+'>' + result + '<span class="h_script">' + token.data + '</span>';
								} else {
									result += '<span '+getElmSpan(token)+'>' + '<span class="h_script">' + token.data + '</span>';
								}
								state.push(token);
							break;
							case T_SCRIPT_END:
								result += '<span class="h_script">'+token.data+'</span>';
								result += '</span>';
								state.pop();
							break;
							default:
								result += token.data;
							break;
						}
					break;
					case T_PHP_START:
					case T_PINP_START:
						switch (token.type) {
							case T_DQUOTE:
							case T_SQUOTE:
							case T_BLOCKCOMMENT:
								result += '<span '+getElmSpan(token)+'>';
								result += token.data;
								state.push(token);
							break;
							case T_PHP_END:
							case T_PINP_END:
								result += '<span class="h_pinp">'+token.data+'</span>';
								result += '</span>';
								state.pop();
							break;
							case T_VAR:
							case T_IDENT:
							case T_OPERATOR:
							case T_SPECIAL_CHAR:
							case T_SINGLE_COMMENTS:
								result += '<span '+getElmSpan(token)+'>';
								result += token.data;
								result += '</span>';
							break;
							default:
								result += token.data;
							break;
						}
					break;
					case T_BLOCKCOMMENT:
						switch (token.type) {
							case T_BLOCKCOMMENT_END:
								result += token.data+'</span>';
								state.pop();
							break;
							default:
								result += token.data;
							break;
						}
					break;
					case T_DQUOTE:
						switch (token.type) {
							case cState:
								result += token.data+'</span>';
								state.pop();
							break;
							case T_ESCAPE:
								result += token.data;
								token = this.tokens[++i];
								result += token.data;
							break;
							case T_VAR:
								result += '<span '+getElmSpan(token)+'>';
								result += token.data;
								result += '</span>';
							break;
							default:
								result += token.data;
							break;
						}
					break;
					case T_SQUOTE:
						switch (token.type) {
							case cState:
								result += token.data+'</span>';
								state.pop();
							break;
							case T_ESCAPE:
								result += token.data;
								token = this.tokens[++i];
								result += token.data;
							break;
							default:
								result += token.data;
							break;
						}
					break;
					default:
						result += token.data;
					break;
				}
			}
//			alert(this.lineNo+'::'+this.tokens.length+'::'+result);

		}
		var stateChanged = 0;
		if (state.length != this.getEndState().length) {
			stateChanged = 1;
		}

		for (i=state.length-1; i>=0; i--) {
			if (!stateChanged && state[i].type!=this.getEndState()[i].type) {
				stateChanged = 1;
			}
			if (!state[i].noMultiLine) {
				result += '</span '+getElmSpan(state[i])+'>';
			}
		}

		/* report update */
		if (callback) {
//			alert(this.lineNo+"::"+result);
			if (result) {
//				alert(this.lineNo+': 2 eol chars: "'+result.substr(result.length-2)+'"');
			}
			callback(this.lineNo, result);
		}
		this.setEndState(state);
		if (stateChanged && this.lineNo < hLines.length-1) {
			if (debug) alert('updating: '+this.lineNo+1); 
			hLines[this.lineNo+1].doHighlight(callback);
		}
	}

	function hLineSetEndState(newEndState) {
//		alert(this.lineNo+': new endstate: '+newEndState.length);
		var frop=new Array();
		if (newEndState.length) {
//			alert(':'+newEndState.toString()+':');
			this.endState=frop.concat(newEndState); //newEndState; //.toSource();
		} else {
			this.endState=new Array();
		}
/*
		var line = hLines[2];
		if (line) {
			alert(this.lineNo+'->'+line.endState.length);
		}
*/
	}

	function hLineGetEndState() {
		return this.endState;
	}

	function hLineRemove() {
		if (this.lineNo < (hLines.length-1)) {
			var len = hLines.length-1;
			for (var i=this.lineNo; i<len; i++) {
				hLines[i] = hLines[i+1];
				hLines[i].lineNo = i;
			}
		}
		hLines.pop();
	}

	function hLine(lineNo, lineString) {
		this.lineNo = lineNo;
		if (lineNo && (lineNo != hLines.length)) {
			var hLinesLen = hLines.length;
			for (var i=hLinesLen; i>lineNo; i--) {
				hLines[i] = hLines[i-1];
				hLines[i].lineNo = i;
			}
		}
		hLines[lineNo] = this;
		this.tokens = new Array();
		this.setEndState = hLineSetEndState;
		this.setEndState(new Array());
		this.getEndState = hLineGetEndState;
		this.remove = hLineRemove;

		this.parseString = hLineParseString;
		if (lineString) {
			this.parseString(lineString);
		}
		if (debug) alert(this.lineNo);
		this.doHighlight = hLineDoHighlight;
	}

	function highlightUpdateLine(lineNo, lineContent, callback) {
//		alert('update line: '+lineNo+'::'+lineContent);
		hLines[lineNo].parseString(lineContent);
		hLines[lineNo].doHighlight(callback);
	}

	function highlightDeleteLine(lineNo, callback) {
//		alert('remove line: '+lineNo);
		line = hLines[lineNo];
		line.remove();
		if (hLines.length && (lineNo < hLines.length)) {
			hLines[lineNo].doHighlight(callback);
		}
	}

	function highlightReset() {
		hLines = new Array();
		new hLine(0, '');
	}

	function highlightInsertLine(lineNo, lineContent, callback) {
		if (lineNo) {
			lineNo -= 1;
		}
//		alert('insert at: '+lineNo+'::'+lineContent);
		line = new hLine(lineNo, lineContent);
		line.doHighlight(callback);
	}

	function highlightAppendLine(lineNo, lineContent, callback) {
//		alert('append at: '+(lineNo+1)+'::'+lineContent);
		line = new hLine(lineNo+1, new String(lineContent));
		hLines[lineNo].doHighlight(callback);
	}
