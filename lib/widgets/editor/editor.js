var editor={

	urls:{
		save:'user.edit.save.html',
		exit:'user.edit.exit.html',
		new:'user.edit.new.html',
		delete:'user.edit.delete.html'
		hyperlink:'edit.object.html.link.phtml',
		image:'edit.object.html.image.phtml'
		transparantImage:'/ariadne/images/dot.gif';
	},

	alerts:{
		dirtyExit:"You have made changes to this page, if you leave these changes will not be saved.",
		saveChanges:"You have made changes to this page, do you wish to save these?"	
	},

	dialogs:{

		options:{
			hyperlink:"font-family:Verdana; font-size:12; dialogWidth:32em; dialogHeight:13em; status: no; resizable: yes;",
			image:"",
			table:"",
			save:'directories=no,height=100,width=300,location=no,status=yes,toolbar=no,resizable=no',
			delete:'directories=no,height=100,width=300,location=no,status=yes,toolbar=no,resizable=no',
			new:'directories=no,height=400,width=550,location=no,status=yes,toolbar=no,resizable=no'
		},

		hyperlink:function() {
			var arr, args, sel, parent, link_el;

			sel = editor.selection.get();
			rg = sel.createRange();
			if (sel.type=="Control") {
				el=rg.item(0);
				parent=el.parentElement;
			} else {
				parent=rg.parentElement();
			}

			arr=null;
			args=new Array();
			//set a default value for your link button
			args["URL"] = "http:/"+"/";
			args["anchors"] = editor.getAnchors();
			if (parent.tagName=="A") {
				args["URL"] = parent.href;
				for (var i=0; i<parent.attributes.length; i++) {
					var temp=parent.attributes.item(i);
					if (temp.specified) {
						args[temp.nodeName]=temp.nodeValue;
					}
				}
			}
			arr = showModalDialog( editor.urls.hyperlink, args,	editor.dialogs.options.hyperlink);
			if (arr != null) {
				var extra={};
				for (var i in arr) {
					switch(i) {
						case 'URL': 
							var url=arr['URL'];
							break;
						case 'name':
							var name=arr['name'];
							break;
						default:
							extra[i]=arr[i];
							break;
					}
				}
				editor.format.hyperlink(url, name, extra);
			}
		},

		image:function() {
			//FIXME
		},

		table:function() {
			//FIXME
		},

		save:function(newurl) {
			savewindow=window.open(editor.urls.save+'?arReturnPage='+escape(newurl), '_savewindow', editor.dialogs.options.save);
			savewindow.focus();
		},

		new:function() {
		    addwindow=window.open(editor.urls.new, '_addwindow', editor.dialogs.options.new);
			addwindow.focus();
		},

		delete:function() {
		    delwindow=window.open(editor.urls.delete, '_delwindow', editor.dialogs.options.delete);
			delwindow.focus();
		}

	},

	exit:function() {
		var temp=editor.isDirty();
		if (temp && editor.events.onDirtyExit()) {
			editor.save(editor.urls.exit);
		} else {
		    window.location=editor.urls.exit;
		}
	},

// ------------------------------------------------------------------------------------------------------------------------------------

	selection:{

		set:function(dir) {
			var tr=editor.content.contentWindow.document.body.createTextRange();
			tr.collapse(dir);
			tr.select();
			editor.selection.keep.save();
		},

		current:null,

		get:function() {
			var sel=editor.selection.current;
			if (!sel) {
				sel=editor.content.contentWindow.document.selection.createRange();
				sel.type=editor.content.contentWindow.document.selection.type;
			}
			return sel;
		},

		save:function() {
			editor.selection.current=editor.content.contentWindow.document.selection.createRange();
			if (!editor.selection.current || 
					(editor.selection.current.parentElement && editor.selection.current.parentElement() && 
					!(editor.selection.current.parentElement() == editor.content.contentWindow.document.body 
						|| editor.content.contentWindow.document.body.contains(this.selection.parentElement() ) 
					) ) 
				) {
				editor.selection.current=editor.content.contentWindow.document.body.createTextRange();
				editor.selection.current.collapse(false);
				editor.selection.current.type="None";
			} else {
				editor.selection.current.type=editor.content.contentWindow.document.selection.type;
			}

		},

		restore:function() {
			if (editor.selection.current) {
				editor.selection.current.select();
			}
		}

	},

	setFormat:function(command, value) {
		var sel=editor.selection.get();
		var target = (sel.type == "None" ? editor.content.contentWindow.document : sel)
		target.execCommand(command, false, value);
		editor.selection.restore();
		return true;
	},

	getBlock:function(el) {
		var BlockElements="|H1|H2|H3|H4|H5|H6|P|PRE|LI|TD|DIV|BLOCKQUOTE|DT|DD|TABLE|HR|IMG|";
		while ((el!=null) && (BlockElements.indexOf("|"+el.tagName+"|")==-1)) {
			el=el.parentElement;
		}
		return el;
	},

	getAnchors:function() {
		var aATags = editor.content.contentWindow.document.getElementsByTagName('A');
		var result = new Array();
		var i=0;
		var ii=0;
		for (ii=0; ii<aATags.length; ii++) {
			var oATag=aATags[ii];
			if (oATag.name) {
				result[i]='#'+oATag.name;
				i++;
			}
		}
		return result;
	},

	isDirty:function() {
		if (editor.fields.current) {
			editor.fields.current.onblur();
		}
		return editor.changes.registry.length;
	},

	getEditableField:function() {
		var parent=false;
		var sel=editor.selection.get();
		if (sel) {
			if (sel.type=="Control") {
				parent=sel.item(0);
			} else {
				parent=sel.parentElement();
			}
			while (parent && parent.className!='editable' && parent.parentElement) {
				parent=parent.parentElement;
			}
		}
		if (parent && parent.className=='editable') {
			return parent;
		} else {
			return false;
		}
	},

	popDirtyField:function() {
		return editor.changes.registry.pop();
	},

	getRequired:function() {
	    var labels=editor.content.contentWindow.document.getElementsByTagName('LABEL');
		if (labels) {
		    var required=new Array();
			var i=labels.length-1;
			do {
				if (labels[i].className=='required') {
					required[labels[i].htmlFor]=labels[i].innerText;
				}
			} while (i--);
		    return required;
		} else {
			// FIXME: what now?
		}
	},

	toggleborders:function() {
		var mydoc=editor.content.contentWindow.document;
		var foundit=false;
		if (mydoc.styleSheets[0]) {
			var myRules=mydoc.styleSheets[0].rules;
			for (var i=0; i<myRules.length; i++) {
				if (myRules[i].selectorText=='.editable') {
					if (myRules[i].style.borderWidth=="1px") {
						myRules[i].style.borderWidth='0px';
					} else {
						myRules[i].style.borderWidth='1px';
						myRules[i].style.borderColor='black';
						myRules[i].style.borderStyle='dotted';
					}	
					foundit=true;
				}
			}
			if (!foundit) {
				// FIXME: append a style
				// alert('no .editable');
			}
		}
	},


	undo:function() {
		editor.setFormat("Undo");
	},

	redo:function() {
		editor.setFormat("Redo");
	},

	copy:function() {
		editor.setFormat("Copy");
	},

	cut:function() {
		editor.setFormat("Cut");
	},

	paste:function() {
		editor.setFormat("Paste");
	},

	delete:function() {
		editor.setFormat("Delete");
	},

	selectall:function() {
		editor.setFormat("SelectAll");
	},

	unselect:function() {
		editor.setFormat("Unselect");
	},

	format:{

		block:function(blockformat) {
			editor.setFormat("FormatBlock", "<"+blockformat+">");
		},

		bold:function() {
			editor.setFormat("Bold");
		},

		bookmark:function(name) {
			editor.setFormat("CreateBookmark", name);
		},

		button:function() {
			editor.setFormat("InsertButton");
		},

		custom:function(tagstring) {
			/*
				following code is inspired if not copied from the very 
				nicely done FCK editor: http://www.fredck.com/FCKeditor/
			*/	

			var sel = editor.selection.get();
			var rg = sel.createRange() ;

			var temp = tagstring.split('.');
			if (temp[0]) {
				var tag = temp[0];
			} else {
				var tag = "";
			}
			if (temp.length==2) {
				var class = temp[1]; 
			} else {
				var class = "";
			}

			if (sel.type == "Text") {
				var span_el = document.createElement("SPAN") ;
				span_el.innerHTML = rg.htmlText ;
				
				var parent = rg.parentElement() ;
				var firstChild = span_el.firstChild ;
				
				if (sTag=='' && firstChild.nodeType == 1 && firstChild.outerHTML == span_el.innerHTML && 
						(firstChild.tagName == "SPAN" || firstChild.tagName == "FONT"
						|| firstChild.tagName == "P" || firstChild.tagName == "DIV")) {
					if (!command.value)	{ 
						// clear
						if (firstChild.tagName=="SPAN") {
							parent.outerHTML = parent.innerHTML;
						} else {
							parent.className = null;
						}
					} else {
						parent.className = sClass ;
					}
				} else {
					if (!command.value) {
						// clear
						rg.pasteHTML(span_el.innerText);
					} else {
		//				var text = oTextRange.htmlText;
						if (tag=='') {
							tag='span';
						}
						if (class) {
							rg.pasteHTML('<'+tag+' class="' + class + '">' + rg.htmlText + '</'+tag+'>');
						} else {
							oTextRange.pasteHTML('<'+tag+'>' + rg.htmlText + '</'+tag+'>');
						}
					}
				}
			} else if (sel.type == "Control" && rg.length == 1) {
				var el = rg.item(0) ;
				if (tag=='' || el.tagName==tag) {
					el.className = class ;
				}
			}
		},

		fieldset:function() {
			editor.setFormat("InsertFieldset");
		},

		form:function() {
			//FIXME
		},

		hr:function() {
			editor.setFormat("InsertHorizontalRule");
		},

		hyperlink:function(url, name, extra) {
			// register change in the editable field, since the focus was already lost through the dialog
			var editField=edior.getEditableField();
			if (editField) {
				editField.onfocus();
			}

			var newLink="<a";
			if (url) {
				newLink+=" href=\""+url+"\"";
			}
			if (name) {
				newLink+=' name="'+name+'"';
			}
			if (extra) {
				try {
					for (var i in extra) {
						newLink=newLink+" "+i+"=\""+extra[i]+"\"";
					}
				} catch(e) {
				}
			}
			newLink=newLink+">";

			var sel=editor.selection.get();

			var rg = sel.createRange();
			if (sel.type=="Control") {
				var el=rg.item(0);
				var parent=el.parentElement;
			} else {
				var parent=rg.parentElement();
			}
			if (parent.tagName=="A") {
				var link_el=parent;
			}

			if (!link_el && (url || name)) {
				if (sel.type=='Control') {
					el.outerHTML=newLink+el.outerHTML+"</A>";
				} else {
					// first let the msie set the link, since it is better in it.
					editor.setFormat("CreateLink", 'http://www.example.com/');
					// now collapse the range, so even if the range overlaps a link partly, the parent
					// element will become the link. trust me.... 
					rg.collapse();
					// now set the link_el object, so it can be 'fixed' with the extra attributes later
					link_el=rg.parentElement();
				}
			}

			if (link_el && (url || name)) {
				link_el.outerHTML=newLink+link_el.innerHTML+'</a>';
			}

			if (link_el && !url && !name) {
				link_el.outerHTML=oATag.innerHTML;
			}

			// register change in the editable field, since the focus was already lost through the dialog
			if (editField) {
				editField.onblur();
			}

		},

		image:function(src, alt, align, class, extra) {
			// register change in the editable field, since the focus was already lost through the dialog
			var editField=editor.getEditableField();
			if (editField) {
				editField.onfocus();
			}

			temp=new String('http://');
			if (src.substring(0,temp.length)!=temp) {
				src=editor.urls.root+src;
			}

			var el=editor.selection.get();
			if (el.type=="Control") {
				var elIMG=el.createRange().item(0);
			}

			if (elIMG) {
				elIMG.src=src;
				if (align=='none') {
					elIMG.align='';
				} else {
					elIMG.align=align;
				}
				elIMG.alt=alt;
				elIMG['class'] = class;
				if (extra) {
					try {
						for (var i in extra) {
							elIMG[i]=extra[i];
						}
					} catch(e) {
					}
				}
			} else {
				if ((el.type=="None") || (el.type=="Text"))	{
					temp='<IMG SRC="'+src+'"';
					if (align!='') {
						temp+=' ALIGN='+align;
					}
					if (alt!='') {
						//FIXME: replace " with escaped char
						temp+=' ALT="'+alt+'"';
					}
					if (class!='') {
						temp+=' CLASS="'+class+'"';
					}
					if (extra) {
						//FIXME: replace " with escaped char
						try {
							for (var i in extra) {
								temp+=' '+i+'="'+extra[i]+'"';
							}
						} catch(e) {
						}
					}
					temp+='>';
					var rg=el.createRange();
					rg.pasteHTML(temp);
					rg.select();
				}
			}

			// register change in the editable field, since the focus was already lost through the dialog
			if (editField) {
				editField.onblur();
			}
		},

		indent:function() {
			editor.setFormat("Indent");
		},

		input:{

			button:function() {
				editor.setFormat("InsertInputButton");
			},

			checkbox:function() {
				editor.setFormat("InsertInputCheckbox");
			},

			fileupload:function() {
				editor.setFormat("InsertInputFileUpload");
			},

			hidden:function() {	
				editor.setFormat("InsertInputHidden");
			},

			password:function() {
				editor.setFormat("InsertInputPassword");
			},

			radio:function() {
				editor.setFormat("InsertInputRadio");
			},

			reset:function() {
				editor.setFormat("InsertInputReset");
			},

			submit:function() {
				editor.setFormat("InsertInputSubmit");
			},

			text:function() {
				editor.setFormat("InsertInputText");
			},

			select:function() {
				editor.setFormat("InsertSelectDropdown");
			},

			selectmultiple:function() {
				editor.setFormat("InsertSelectListBox");
			},

			textarea:function() {
				editor.setFormat("InsertTextArea");
			}

		},

		italic:function() {
			editor.setFormat("Italic");
		},

		justifycenter:function() {
			editor.setFormat("JustifyCenter");
		},

		justifyfull:function() {
			editor.setFormat("JustifyFull");
		},

		justifyleft:function() {
			editor.setFormat("JustifyLeft");
		},

		justifyright:function() {
			editor.setFormat("JustifyRight");
		},

		link:function(url) {
			editor.setFormat("CreateLink", url);
		},

		outdent:function() {
			editor.setFormat("Outdent");
		},

		orderedlist:function() {
			editor.setFormat("InsertOrderedList");
		},

		strikethrough:function() {
			editor.setFormat("StrikeThrough");
		},

		subscript:function() {
			editor.setFormat("Subscript");
		},

		superscript:function() {
			editor.setFormat("Superscript");
		},

		unbookmark:function() {
			editor.setFormat("UnBookmark");
		},

		underline:function() {
			editor.setFormat("Underline");
		},

		unlink:function() {
			editor.setFormat("Unlink");
		},

		unorderedlist:function() {
			editor.setFormat("InsertUnorderedList");
		},

		remove:function() {
			editor.setFormat("RemoveFormat");
		}
	},


	objects:{
		registry:new Array()
	},

	changes:{
		registry:new Array(),
		register:function(fieldId) {
			if (editor.fields.registry[fieldId]) {
				var objectId=editor.fields.registry[fieldId].id;
				var fieldName=editor.fields.registry[fieldId].name;
				if (!editor.changes.registry[fieldName+objectId]) {
					var index=editor.changes.registry.length;
					editor.changes.registry[index]=editor.fields.registry[fieldId];
					editor.changes.registry[new String(fieldName+objectId)]=index;
				}
			}
		},

		checkStart:function() {
			this.startContent=editor.fields.value.get(this.id);
			editor.fields.current=this;
		},

		checkEnd:function() {
			var newValue = editor.fields.value.get(this.id);
			if (editor.fields.registry[this.id]) {
				if (this.startContent!=newValue) {
					editor.changes.register(this.id);

					var objectId = editor.fields.registry[this.id].id;
					for (var i in editor.objects.registry[objectId][editor.fields.registry[this.id].name]) {
						var fieldId = editor.objects.registry[objectId][editor.fields.registry[this.id].name][i].fieldId;
						editor.fields.registry[fieldId].value = newValue;
						if (fieldId!=this.id) {
							// don't update the content of the current field, since that breaks
							// selections.
							editor.fields.value.set(fieldId, newValue);
						}
					}
					this.startContent=newValue;
				}
			}
		},

		clear:function() {
			editor.changes.registry=new Array();
		}

	},

	fields:{
		value:{
			get:function(data_name) {
				var data="";
				var value='';
				if (data=editor.content.contentWindow.document.getElementById(data_name)) {
					switch (data.type) {
						case 'checkbox' :
							if (data.checked) {
								value=data.value;
							}
							break;
						case 'radio' :
							var radio=editor.content.contentWindow.document.all[data_name];
							if (radio) { 
								for (var i=0; i<radio.length; i++) {
									if (radio[i].checked) {
										value=radio[i].value;
										break;
									}
								}
							}
							break;
						case 'hidden' :
						case 'password' :
						case 'text' :
						case 'textarea' :
							value=data.value;
							break;
						case 'select-one' :
							value=data.options[data.selectedIndex].value;
							break;
						case 'select-multiple' :
							value=new Array();
							for (var i=0; i<data.length; i++) {
								if (data.options[i].selected) {
									value[value.length]=data.options[i].value;
								}
							}
							break;
						default :
							value=data.innerHTML;
							break;
					}
					return value;
				} else {
					return '';
				}
			},

			set:function(data_name, value) {
				var data="";
				if (data=editor.content.contentWindow.document.getElementById(data_name)) {
					switch (data.type) {
						case 'checkbox' :
							if (data.checked) {
								value=data.value;
							}
							break;
						case 'radio' :
							var radio=editor.content.contentWindow.document.all[data_name];
							if (radio) { 
								for (var i=0; i<radio.length; i++) {
									if (radio[i].checked) {
										value=radio[i].value;
										break;
									}
								}
							}
							break;
						case 'hidden' :
						case 'password' :
						case 'text' :
						case 'textarea' :
							data.value = value;
							break;
						case 'select-one' :
							value=data.options[data.selectedIndex].value;
							break;
						case 'select-multiple' :
							value=new Array();
							for (var i=0; i<data.length; i++) {
								if (data.options[i].selected) {
									value[value.length]=data.options[i].value;
								}
							}
							break;
						default :
							data.innerHTML = value;
							break;
					}
				}
			}


		},

		registry:new Array(),
		list:new Array(),
		current:false,
		required:new Array(),

		new:function(id, name, path, ob_id) {
			function dataField(fieldid, name, path, id) {
				this.fieldId=fieldid;
				this.name=name;
				this.path=path; //FIXME: an object may have multiple paths, not all of which the user may have edit grants on
				this.id=id;
			}
			return new dataField(id, name, path, ob_id);
		},

		register:function(fieldId, fieldName, objectPath, objectId) {
			editor.fields.registry[fieldId]=editor.fields.new(fieldId, fieldName, objectPath, objectId);
			if (!editor.objects.registry[objectId]) {
				editor.objects.registry[objectId]=new Array();
			}
			if (!editor.objects.registry[objectId][fieldName]) {
				editor.objects.registry[objectId][fieldName]=new Array();
			}
			editor.objects.registry[objectId][fieldName][editor.objects.registry[objectId][fieldName].length]=editor.fields.registry[fieldId];
			editor.fields.list.push(editor.fields.registry[fieldId]); // add to full list of editable fields
		},

		require:function(fieldName, objectId, title) {
			function requiredField(title, fieldId) {
				this.title=title;
				this.fieldId=fieldId;
			}
			var fieldId=editor.objects.registry[objectId][fieldName][0].fieldId;
			editor.fields.required.push(new requiredField(title, fieldId));
		},

		getRequired:function() {
			return editor.fields.required;
		},

		get:function(fieldId) {
			return editor.fields.registry[fieldId];
		},

		initialize:function() {
			var editable;
			var editWindow=editor.content.contentWindow;
			for (i=0; i<editWindow.document.all.length; i++) {
				if (editWindow.document.all[i].className == "editable") {
					editable=editWindow.document.all[i];
					editable.onfocus=editor.changes.checkStart;
					editable.onblur=editor.changes.checkEnd;
					editable.contentEditable=true;
					editable.style.backgroundImage="url('"+editor.urls.transparantImage+"')";
				}
			}
		}

	},

	events:{
		initialize:function() {
			editor.content=editor.contedocument.getElementById('editor.content');
			if (navigator.appName.indexOf("Microsoft")!=-1) {
				editor.content.contentWindow.document.body.onBlur=editor.selection.save;
				editor.content.contentWindow.document.body.onkeyup=editor.events.onChange;
				editor.content.contentWindow.document.body.onmouseup=editor.events.onChange;
				editor.content.contentWindow.document.body.onkeypress=editor.events.onKeyPress;
				editor.content.contentWindow.document.body.onkeydown=editor.events.onKeyDown;

				if (editor.content.onLoadHandler) {
					editor.content.onLoadHandler();
				}
			} else {
				// non microsoft browser, so try to at least show the content
				editor.content.style.border='0px';
				editor.content.style.backgroundColor='white';
				editor.content.style.height='100%';
				editor.content.style.width='100%';
				editor.content.style.overflow='auto';
			}
			document.getElementById("loadingdiv").style.visibility = "hidden";
			window.onbeforeunload=editor.events.onBeforeUnload;
		},
		onBeforeUnload:function() {
			if (editor.isDirty()) {
				event.returnValue=editor.alerts.dirtyExit;
			}
		},
		onChange:function() {
/*
			for ( var i=0; i<editor.QueryStatus.ToolbarButtons.length; i++) {
				if (buttons_disabled[CommandCrossReference[QueryStatusToolbarButtons[i].command]]) {
					TBSetState(QueryStatusToolbarButtons[i].element, "gray"); 
				} else if(!tbContentElement.contentWindow.document.queryCommandState(QueryStatusToolbarButtons[i].command)) {
					if (!tbContentElement.contentWindow.document.queryCommandSupported(QueryStatusToolbarButtons[i].command) ||
							!tbContentElement.contentWindow.document.queryCommandEnabled(QueryStatusToolbarButtons[i].command)) {
					TBSetState(QueryStatusToolbarButtons[i].element, "gray"); 
					} else {
						TBSetState(QueryStatusToolbarButtons[i].element, "unchecked"); 
					} 
				} else { // DECMDF_LATCHED
					 TBSetState(QueryStatusToolbarButtons[i].element, "checked");
				}
			}
*/
			return true;
		},

		onKeyPress:function() {
			myevent=editor.content.contentWindow.event;
			if (!editor.compose.keypress(myevent)) {
				editor.content.contentWindow.event.cancelBubble=true; 
				editor.content.contentWindow.event.returnValue=false; 
			}
			return true;
		},

		onKeyDown:function() {
			myevent=editor.content.contentWindow.event;
			if (!editor.compose.keydown(myevent)) {
				editor.content.contentWindow.event.cancelBubble=true; 
				editor.content.contentWindow.event.returnValue=false; 
			}
			return true;
		},

		onDirtyExit:function() {
			return confirm(editor.alerts.saveChanges);
		}

	},

	compose:{
		busy:false,
		key:19,
		numeric:false,
		symbolic:false,
		table:{
			'i'		: -1,
			'i!'	: "iexcl",
			'$'		: -1,
			'$c'	: "cent",
			'$p'	: "pound",
			'$#'	: "curren",
			'$y'	: "yen",
			'$e'	: "euro",
			'$f'	: "fnof",

			'|'		: "brvbar",
			'#'		: "sect",
			'"'		: "uml",

			'c'		: -1,
			'c@'	: "copy",

			'o'		: -1,
			'of'	: "ordf",

			'<'		: -1,
			'<<'	: "laquo",

			'!'		: "not",
			'-'		: "shy",

			'r'		: -1,
			'r@'	: "reg",

			'_'		: "macr",

			'd'		: -1,
			'dg'	: "deg",

			'+'		: -1,
			'+-'	: "plusmn",

			'^'		: -1,
			'^1'	: "sup1",
			'^2'	: "sup2",
			'^3'	: "sup3",

			"'"		: "acute",
			'm'		: "micro",

			'p'		: -1,
			'pp'	: "para",

			'.'		: "middot",
			','		: "cedil",

			'o'		: -1,
			'om'	: "ordm",

			'>'		: -1,
			'>>'	: "raquo",

			'1'		: -1,
			'1/'	: -1,
			'1/4'	: "frac14",
			'1/2'	: "frac12",
			'3'		: -1,
			'3/'	: -1,
			'3/4'	: "frac34",

			'i'		: -1,
			'i?'	: "iquest",

			'A'		: -1,
			'A`'	: "Agrave",
			"A'"	: "Aacute",
			'A^'	: "Acirc", 
			'A~'	: "Atilde",
			'A"'	: "Auml",  
			'Ao'	: "Aring", 
			'AE'	: "AElig", 

			'C'		: -1,
			'C,'	: "Ccedil",

			'E'		: -1,
			'E`'	: "Egrave",
			"E'"	: "Eacute",
			'E^'	: "Ecirc", 
			'E"'	: "Euml",  
			'ET'	: -1,   
			'ETH'	: "ETH",   

			'I'		: -1,
			'I`'	: "Igrave",
			"I'"	: "Iacute",
			'I^'	: "Icirc", 
			'I"'	: "Iuml",  

			'N'		: -1,   
			'N~'	: "Ntilde",

			'O'		: -1,   
			'O`'	: "Ograve",
			"O'"	: "Oacute",
			'O^'	: "Ocirc", 
			'O~'	: "Otilde",
			'O"'	: "Ouml",  
			'O/'	: "Oslash",

			'*'		: "times", 

			'U'		: -1,
			'U`'	: "Ugrave",
			"U'"	: "Uacute",
			'U^'	: "Ucirc", 
			'U"'	: "Uuml",  

			'Y'		: -1,
			"Y'"	: "Yacute",

			'T'		: -1,
			'TH'	: "THORN", 

			's'		: -1,
			'sz'	: "szlig", 

			'a'		: -1,
			'a`'	: "agrave",
			"a'"	: "aacute",
			'a^'	: "acirc", 
			'a~'	: "atilde",
			'a"'	: "auml",  
			'ao'	: "aring", 
			'ae'	: "aelig", 

			'c'		: -1,
			'c,'	: "ccedil",

			'e'		: -1,
			'e`'	: "egrave",
			"e'"	: "eacute",
			'e^'	: "ecirc", 
			'e"'	: "euml",  

			'i'		: -1,
			'i`'	: "igrave",
			"i'"	: "iacute",
			'i^'	: "icirc", 
			'i"'	: "iuml",  

			'et'	: -1,
			'eth'	: "eth",   

			'n'		: -1,
			'n~'	: "ntilde",

			'o'		: -1,
			'o`'	: "ograve",
			"o'"	: "oacute",
			'o^'	: "ocirc", 
			'o~'	: "otilde",
			'o"'	: "ouml",  
			'/'		: "divide",
			'o/'	: "oslash",

			'u'		: -1,
			'u`'	: "ugrave",
			"u'"	: "uacute",
			'u^'	: "ucirc", 
			'u"'	: "uuml",  

			'y'		: -1,
			"y'"	: "yacute",

			't'		: -1,
			'th'	: "thorn", 
			'tm'	: "trade",
			'y"'	: "yuml",  

			'l'		: -1,
			'l/'	: "lstrok",
			'L'		: -1,
			'L/'	: "Lstrok",  

			'#'		: -2,

			'&'		: -3
		},


		check:function(e) {
			var keycode = e.keyCode;
			if (keycode==editor.compose.key) {
				if (!editor.compose.busy) {
					editor.compose.busy=true;
					window.status='Composing...';
				}
				return false;
			} else {
				return true;
			}
		},

		keydown:function(e) {
			var keycode = e.keyCode;
			var key=String.fromCharCode(keycode);

			if (keycode==editor.compose.key) {
				if (!editor.compose.busy) {
					editor.compose.busy=true;
					editor.compose.numeric=false;
					window.status='Composing...';
				}
				return false;
			}
			if (editor.compose.busy) {
				if (keycode==27) { // esc
					editor.compose.stop();
					return false;
				} else if (keycode==8) { // backspace
					editor.compose.buffer=editor.compose.buffer.substr(0, editor.compose.buffer.length-1);
					window.status=window.status.substr(0, window.status.length-1);
					return false;
				}
			}
			return true;
		},

		keypress:function(e) {
			var keycode = e.keyCode;
			var key=String.fromCharCode(keycode);

			if (keycode==editor.compose.key) {
				if (!editor.compose.busy) {
					editor.compose.busy=true;
					editor.compose.numeric=false;
					window.status='Composing...';
				}
				return false;
			}
			if (editor.compose.busy) {
				if (keycode==27) { // esc
					editor.compose.stop();
					return false;
				}
				editor.compose.buffer+=key;
				if (value=editor.compose.table[wgComposeBuffer]) {
					if (value==-3) {
						editor.compose.symbolic=true;
						window.status=window.status+'&';
					} else if (value==-2) {
						editor.compose.numeric=true;
						window.status=window.status+'#';
					} else if (value!=-1) {
						editor.compose.buffer='&'+editor.compose.table[editor.compose.buffer]+';';
						editor.compose.show(editor.compose.buffer);
						editor.compose.stop();
					} else {
						window.status="Composing: "+editor.compose.buffer;
					}
				} else if (editor.compose.symbolic) {
					if ((keycode==13) || (key==';')) {
						editor.compose.buffer='&'+editor.compose.buffer.substr(1, editor.compose.buffer.length-2)+';';
						editor.compose.show(editor.compose.buffer);
						editor.compose.stop();
					} else {
						window.status=window.status+key;
					}
				} else if (editor.compose.numeric) {
					if ((keycode>=48) && (keycode<=57)) {
						window.status=window.status+key;
					} else if (keycode==13) {
						editor.compose.buffer='&'+editor.compose.buffer.substr(0, editor.compose.buffer.length-1)+';';
						editor.compose.show(editor.compose.buffer);
						editor.compose.stop();
					} else {
						editor.compose.stop();
					}
				} else {
					editor.compose.stop();
					return false;
				}
				return false;
			} else {
				return true;
			}
		},

		stop:function() {
			editor.compose.busy=false;
			editor.compose.numeric=false;
			editor.compose.buffer='';
			window.status='';
		},


		show:function(buffer) {
			var sel=editor.selection.get();
			sel.pasteHTML(buffer);
		}

	}
}