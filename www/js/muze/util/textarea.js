
    /*
        muze.util.textarea.Position
            offset  character offset from the start of the textarea, in IE each newline is counted as two characters (\r\n)
            char    character offset from the start of the line the cursor is on, tabs are counted as one character
            col column position of the cursor, tabs are calculated to their tab stops
            row row position of the cursor
    */


muze.namespace("muze.util.textarea", function() {
	var tabSize=8;
	
	function setCursorPosition(input, position, end) {
		var fullPosition = getPosition(input, position);
		if (end) {
			var endPosition = getPosition(input, end);
		} else {
			var endPosition = fullPosition;
		}
		if (input.setSelectionRange) {
			input.setSelectionRange(fullPosition.offset,endPosition.offset);
		} else if (document.selection) {
			// for each newline in the text upto the cursor position, the offset is off by one, since IE counts \r\n as one position with moveStart
			var offset = fullPosition.offset - (fullPosition.row-1);
			var cursor = document.selection.createRange();
			cursor.moveToElementText(input);
			cursor.collapse();
			cursor.moveStart('character',offset);
			if (endPosition.largerThan(input, fullPosition)) {
				var endOffset = endPosition.offset - (endPosition.row-1) - offset;
				cursor.moveEnd('character', endOffset);
			}
			cursor.select();
		}
		return offset;
	}

	function normalizeNL(value) {
		if (document.all) {
			return String(value).replace('\r\n','\n');
		} else {
			return String(value);
		}
	}
	
	function getPosition(input, position) {
		var result	= new muze.util.textarea.Position(position.row, position.col, position.char, position.offset);
		var text	= String(input.value);
		// check what we know
		if (!position.offset && position.offset!==0) { // 0 is a valid offset
			if (position.row) { // rows count from 1, so 0 is not a valid row
				var lines	= text.split('\n');
				var pre 	= lines.slice(0,position.row-1).join('\n');
				if (position.row>1) { // add one newline for the last line, since join skips the last line, but only if there is at least one line in pre 
					pre+='\n';
				}
				if (position.row>lines.length) {
					result.row=lines.length;
				}
				if (position.char || position.char===0) { // 0 is a valid char position, check it explicitly or it fails
					pre 		+= lines[result.row-1].substr(0, position.char)
					result.col 	= charPosToColumn(position.char, lines[result.row-1]);
				} else if (position.col) { // columns count from 1 on, no need to check for 0
					result.char 	= columnToCharPos(position.col, lines[result.row-1]);
					pre 		+= lines[result.row-1].substr(0, result.char)
				} else {
					throw('Need more position elements either (row,col), (row,char) or (offset)');
				}
				result.offset = pre.length;
			} else {
				throw('Need more position elements either (row,col), (row,char) or (offset)');
			}
		} else {
			// offset is known, calc everything else
			var pre = text.slice(0,position.offset);
			var lines = pre.split('\n');
			result.row = lines.length;
			result.char = lines[lines.length-1].length+1;
			result.col = charPosToColumn(result.char, lines[lines.length-1]);
		}			
		return result;
	}
	
	function getCursorPosition(textarea, end) {
		// FIXME: where is the cursor in a selection, head or arse end?
		// the only way is to keep track of previous values of the cursor position, the one that stays the same is not the cursor
		var result = false;
		if (textarea.setSelectionRange) { 
			if (end) {
				result = getPosition(textarea, new muze.util.textarea.Position(0,0,0,textarea.selectionEnd));
			} else {
				result = getPosition(textarea, new muze.util.textarea.Position(0,0,0,textarea.selectionStart));
			}
		} else if (document.selection) { //IE
			var selection_range = document.selection.createRange().duplicate();

			if (selection_range.parentElement() == textarea) {    // Check that the selection is actually in our textarea
				// Create three ranges, one containing all the text before the selection,
				// one containing all the text in the selection (this already exists), and one containing all
				// the text after the selection.
				var before_range = document.body.createTextRange();
				before_range.moveToElementText(textarea);                    // Selects all the text
				before_range.setEndPoint("EndToStart", selection_range);     // Moves the end where we need it

				var after_range = document.body.createTextRange();
				after_range.moveToElementText(textarea);                     // Selects all the text
				after_range.setEndPoint("StartToEnd", selection_range);      // Moves the start where we need it

				var before_finished = false, selection_finished = false, after_finished = false;
				var before_text, untrimmed_before_text, selection_text, untrimmed_selection_text, after_text, untrimmed_after_text;

				// Load the text values we need to compare
				before_text = untrimmed_before_text = before_range.text;
				selection_text = untrimmed_selection_text = selection_range.text;
				after_text = untrimmed_after_text = after_range.text;

				// Check each range for trimmed newlines by shrinking the range by 1 character and seeing
				// if the text property has changed.  If it has not changed then we know that IE has trimmed
				// a \r\n from the end.
				do {
				  if (!before_finished) {
				      if (before_range.compareEndPoints("StartToEnd", before_range) == 0) {
				          before_finished = true;
				      } else {
				          before_range.moveEnd("character", -1)
				          if (before_range.text == before_text) {
				              untrimmed_before_text += "\r\n";
				          } else {
				              before_finished = true;
				          }
				      }
				  }
				  if (!selection_finished) {
				      if (selection_range.compareEndPoints("StartToEnd", selection_range) == 0) {
				          selection_finished = true;
				      } else {
				          selection_range.moveEnd("character", -1)
				          if (selection_range.text == selection_text) {
				              untrimmed_selection_text += "\r\n";
				          } else {
				              selection_finished = true;
				          }
				      }
				  }
				  if (!after_finished) {
				      if (after_range.compareEndPoints("StartToEnd", after_range) == 0) {
				          after_finished = true;
				      } else {
				          after_range.moveEnd("character", -1)
				          if (after_range.text == after_text) {
				              untrimmed_after_text += "\r\n";
				          } else {
				              after_finished = true;
				          }
				      }
				  }

				} while ((!before_finished || !selection_finished || !after_finished));

				// Untrimmed success test to make sure our results match what is actually in the textarea
				// This can be removed once you're confident it's working correctly
				var untrimmed_text = untrimmed_before_text + untrimmed_selection_text + untrimmed_after_text;
				var untrimmed_successful = false;
				if (textarea.value == untrimmed_text) {
				  untrimmed_successful = true;
				}
				// ** END Untrimmed success test

				var startPoint = untrimmed_before_text.length;
				var endPoint = untrimmed_before_text.length + untrimmed_selection_text.length;
				if (end) {
					offset = endPoint;
				} else {
					offset = startPoint;
				}
				result = getPosition(textarea, new muze.util.textarea.Position(0,0,0,offset));
			}
		}
		return result;
	}
	
	function charPosToColumn(charPos, line) {
		var colCounter	= 1;
		var charCounter	= 0;
		var tempLine	= line;
		var nextChar;
		var realTabSize;
		while (charCounter < (charPos-1)) {
			// walk through the string a character at a time
			// FIXME: this can be sped up
			nextChar = tempLine.charAt(charCounter);
			charCounter++;
			if (nextChar == '\t') {
				realTabSize = tabSize-((colCounter-1)%tabSize);
				colCounter += realTabSize;
			} else if ((nextChar == '\r') || (nextChar == '\n')) {
				// don't count as a column, it's the end of the line, so get out
				break;
			} else {
				colCounter++;
			}
			if (!nextChar) {
				break;
			}
		}
		return colCounter;		
	}
	
	function columnToCharPos(column, line) {
		var colCounter	= 0;
		var charCounter	= 0;
		var tempLine	= new String(line);
		var nextChar;
		var realTabSize;
		do {
			nextChar = tempLine.charAt(charCounter);
			// walk through the string a character at a time
			// FIXME: this can be sped up
			charCounter++;
			if (nextChar == '\t') {
				realTabSize = tabSize-(colCounter%tabSize);
				colCounter += realTabSize;
			} else if ((nextChar == '\r') || (nextChar == '\n')) {
				// don't count as column, it's the end of the line, so everybody get out now
				break;
			} else {
				colCounter++;
			}
		} while (nextChar && colCounter<column);
		return charCounter-1;
	}

	function getSelection(input) {
		var start = getCursorPosition(input);
		var end = getCursorPosition(input, true);
		var value = String(input.value).substr(start.offset, end.offset);
		return {
			'start': start,
			'end': end,
			'value': value
		}
	}
	
	function insertContent(input, value, start, end) {
		var currentSelection = getSelection(input);
		if (!start) {
			start = currentSelection.start;
			if (!end && end!=0) {
				end = currentSelection.end;
			}
		} else {
			start = getPosition(input, start);
			if (!end) {
				end = start;
			}
			end = getPosition(input, end);
			if (end.smallerThan(input, start)) {
				end = start;
			}
		}
		if (input.setSelectionRange) { 
			input.value = String(input.value).substr(0, start.offset)+value+String(input.value).substr(end.offset);
		} else if (document.selection) { //IE
			setCursorPosition(input, start, end);
			var r = document.selection.createRange();
			r.text = value;
		}
		if (currentSelection.start.equalTo(input, start)) { // cursor was at the content start, so move the cursor to after the last char of the inserted value and collapse it
			start = new muze.util.textarea.Position(0,0,0,start.offset+value.length);
			setCursorPosition(input, start);
		} else if (currentSelection.start.largerThan(input, end)) { // move the selection to the new position
			start = new muze.util.textarea.Position(0,0,0,currentSelection.start.offset+value.length);
			end = new muze.util.textarea.Position(0,0,0,currentSelection.end.offset+value.length);
			setCursorPosition(input, start, end);
		} else {
			setCursorPosition(input, currentSelection.start, currentSelection.end);
		}
	}
	
	return {
		getPosition:function(input, position) {
			return getPosition(input, position);
		},
		getCursorPosition:function(input, end) {
			return getCursorPosition(input,end);
		},
		setCursorPosition:function(input, start, end) {
			return setCursorPosition(input, start, end);
		},
		getSelection:function(input) {
			return getSelection(input);
		},
		insertContent:function(input, value, start, end) {
			return insertContent(input, value, start, end);
		},
		Position:function(row, col, char, offset) {
			this.row=row; 
			this.col=col; 
			this.char=char; 
			this.offset=offset;
			this.compare	= function(input, pos) {
				if (!this.offset) {
					var me = getPosition(input, this);
					this.offset = me.offset;
				}
				if (!pos.offset) {
					pos = getPosition(input, pos);
				}
				if (this.offset < pos.offset) {
					return -1;
				} else if (this.offset > pos.offset) {
					return 1;
				} else {
					return 0;
				}
			}
			this.largerThan	= function(input, pos) {
				return this.compare(input, pos)==1;
			}
			this.smallerThan = function(input, pos) {
				return this.compare(input, pos)==-1;
			}
			this.equalTo = function(input, pos) {
				return this.compare(input, pos)==0;
			}
		}
	}
});
