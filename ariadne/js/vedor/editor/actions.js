muze.namespace("vedor.editor.actions");

vedor.editor.actions.text = function() {
	return {
		italic : function() {
			setFormat("Italic");		
		},
		bold : function() {
			setFormat("Bold");
		},
		underline : function() {
			setFormat("Underline");
		},
		type : function(styleId) {
			// fixme: setFormatStyle ...
		},
		remove : function() {
			setFormat("removeFormat");
		},
		indent : function() {
			setFormat("Indent");
		},
		align : function() {
			return {
				left : function() {
					setFormat("JustifyLeft");
				},
				right : function() {
					setFormat("JustifyRight");
				},
				center : function() {
					setFormat("JustifyCenter");
				},
				justify : function() {
					setFormat("Justify");
				}
			}
		}()
	}
}();

vedor.editor.actions.clipboard = function() {
	return {
		copy : function() {
			setFormat("Copy");
		},
		paste : function() {
			setFormat("Paste");
		},
		cut : function() {
			setFormat("Cut");
		}
		selectAll : function() {
		}
	}
}();

vedor.editor.actions.code = function() {
	return {
		insert : function() {
		},
		edit : function() {
		}
	}
}();

vedor.editor.actions.symbol = function() {
	return {
		insert : function() {
		}
	}
}();

vedor.editor.actions.gadget = function() {
	return {
		insert : function() {
		},
		edit : function() {
		}
	}
}();

vedor.editor.actions.hyperlink = function() {
	var	getAnchors = function() {
		var aATags = vdEditPane.contentWindow.document.getElementsByTagName('A');
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
	};

	return {
		link : function() {
		},
		code : function() {
		},
		title : function(title) {
		},
		nofollow: function(nofollow) {
		},
		noindex : function(noindex) {
		},
		insert : function() {
			if (isEditable()) {
				var arr,args,oSel, oParent;
				var oATag=false;

				oSel = vdSelectionState.get();
				var control = vdSelectionState.getControlNode(oSel);
				if (control) {
					oElement=control;
					oParent=oElement.parentNode;
				} else {
					if( oSel.select ) { // IE only
						var htmlText = vdSelection.getHTMLText(oSel);
						if (htmlText.substr(htmlText.length-4, 4)=='<BR>') {
							// BR included in selection as last element, remove it, it has
							// dangerous effects on the hyperlink command in IE
							oSel.moveEnd('character',-1);
							oSel.select();
						}
						if (htmlText.substr(0,4)=='<BR>') {
							// idem when its the first character
							oSel.moveStart('character',1);
							oSel.select();
						}
					}
					oParent = vdSelection.parentNode(oSel);
				}

				arr=null;
				args=new Array();
				//set a default value for your link button
				args["URL"] = "http:/"+"/";
				args["anchors"] = this.getAnchors();
				args['vdCurrentSite'] = vdCurrentSite;
				args['vdCurrentPath'] = sitePath;
				args['vdStartpath'] = vdBrowseRoot;
				args['objectPath'] = objectPath;
				args['objectURL'] = objectURL;
				args['objectURL_nls'] = objectURL_nls;
				if (oParent.tagName=="A") {
					oATag=oParent;
					args["url"] = oParent.href;
					args['name'] = oParent.name;
					for (var i=0; i<oParent.attributes.length; i++) {
						oAttr=oParent.attributes.item(i);
						if (oAttr.specified) {
							args[oAttr.nodeName.replace(':', '')]=oAttr.nodeValue;
						}
					}
				}
				var urlArgs = new String();
				for (var key in args) {
					urlArgs += '&' + key + '=' + escape( args[ key ] );
				}
				
				var url = objectURL + 'dialog.hyperlink.php?root=' + (tbContentEditOptions['browse']['root'] ? tbContentEditOptions['browse']['root'] : sitePath ) + urlArgs;
				muze.dialog.open( url, 'hyperlink', { windowFeatures :  muze.ariadne.explore.windowprops['dialog_hyperlink'] } )
				.on('submit', function( arr ) {
					if (arr) {
						// register change in the editable field, since the focus was already lost through the dialog
						var editField=getEditableField();
						if (editField) {
							checkChangeStartEl(editField);
							registerChange(editField.id);
						}

						var linkclass = '';
						var newLink="<a";

						if (arr['href']) {
							newLink+=" href=\""+arr['href']+"\"";
						}
						if (arr['name']) {
							newLink+=' name="'+arr['name']+'"';
						}
						if (arr['attributes']) {
							for (var i in arr['attributes']) {
								var arAttributeValue=arr['attributes'][i];
								if (arAttributeValue) {
									if (i == "ar:type") {
										linkclass = linkclass + arAttributeValue;
									}
									newLink=newLink+" "+i+"=\""+arAttributeValue+"\"";
								}
							}
						}

						newLink = newLink + 'class="'+ linkclass + '"';
						newLink=newLink+">";
						if (!oATag && (arr['href'] || arr['name'])) {
							if ( control ) {
								if( oElement.outerHTML ) {
									oElement.outerHTML=newLink+oElement.outerHTML+"</A>";
								} else { // firefox and co
									var div = oElement.ownerDocument.createElement('div');
									var clone = oElement.cloneNode(true);
									div.appendChild(clone);
									var inner = div.innerHTML;
									div = oElement.ownerDocument.createElement('div');
									div.innerHTML = newLink+inner+'</a>';
									var frag = oElement.ownerDocument.createDocumentFragment();
									for (var i=0; i < div.childNodes.length; i++) {
										var node = div.childNodes[i].cloneNode(true);
										frag.appendChild(node);
									}
									div = null;
									oElement.parentNode.replaceChild(frag, oElement);
								}
							} else {
								
								// first let the dhtmledit component set the link, since it is better in it.
								// but to find it back, we need a unique identifier
								var linkIdentifier=Math.floor(Math.random()*10000);
								setFormat("CreateLink", '#'+linkIdentifier);
								// now collapse the range, so even if the range overlaps a link partly, the parent
								// element will become the link. trust me....
								oRange = vdSelectionState.get();
								oRange = vdSelection.collapse(oRange, false); // oRange.collapse(false);
								// now set the ATag object, so it can be 'fixed' with the extra attributes later
								oATag= vdSelection.parentNode(oRange); // .parentElement();
								var linkIdStr = '#'+linkIdentifier;
								if (oATag.tagName!='A' || oATag.href.substr(oATag.href.length-linkIdStr.length, linkIdStr.length)!=linkIdStr) {
									// ok, the link doesn't line up with the range, apparantly, so try to find the link
									oATag=null;
									var allATags=vdEditPane.contentWindow.document.getElementsByTagName('A');
									for (var i=0; i<allATags.length; i++) {
										if (allATags[i].href.substr(allATags[i].href.length-linkIdStr.length, linkIdStr.length)==linkIdStr) {
											oATag=allATags[i];
											break;
										}
									}
								}
							}
						}
						if (oATag && (arr['href'] || arr['name'])) {
							if( oATag.outerHTML ) {
								oATag.outerHTML=newLink+oATag.innerHTML+'</a>';
							} else { // firefox and co
								var div = oATag.ownerDocument.createElement('div');
								div.innerHTML = newLink+oATag.innerHTML+'</a>';
								var frag = oATag.ownerDocument.createDocumentFragment();
								for (var i=0; i < div.childNodes.length; i++) {
									var node = div.childNodes[i].cloneNode(true);
									frag.appendChild(node);
								}
								div = null;
								oATag.parentNode.replaceChild(frag, oATag);
							}
						}
						if (oATag && (arr['href'] == '') && (arr['name'] == '')) {	
							if( oATag.outerHTML ) { 
								oATag.outerHTML=oATag.innerHTML;
							} else { // firefox and co
								var div = oATag.ownerDocument.createElement('div');
								div.innerHTML = oATag.innerHTML;
								var frag = oATag.ownerDocument.createDocumentFragment();
								for (var i=0; i < div.childNodes.length; i++) {
									var node = div.childNodes[i].cloneNode(true);
									frag.appendChild(node);
								}
								div = null;
								oATag.parentNode.replaceChild(frag, oATag);
							}
						}
						if (editField) {
							checkChangeEndEl(editField);
						}
						vdStoreUndo();


					}
				})
				.always( function() {
					this.close();
				});

			}
			vdEditPane.focus();
		},
		remove : function() {
		}
	}
}();


vedor.editor.actions.image = function() {
	var set = function() {
		window.setfocusto=false;
		var el=window.el;
		if (arr && arr['src']) {
			// register change in the editable field, since the focus was already lost through the dialog
			var editField=getEditableField();
			if (editField) {
				registerChange(editField.id);
				checkChangeStartEl(editField);
			}

			src=new String(arr['src']);
			var temp1=new String('https://');
			var temp2=new String('http://');
			var temp3=new String('//');

			if (
				(src.substring(0,temp1.length)!=temp1) && 
				(src.substring(0,temp2.length)!=temp2) &&
				(src.substring(0,temp3.length)!=temp3) 
			) {
				src=rootURL+src;
			}
			if (arr['ar:type'] && arr['ar:type']!='undefined') {
				src+=tbContentEditOptions['image']['styles'][arr['ar:type']]['template'];
			}
			if (window.elIMG) { // insert a new img
				elIMG=window.elIMG;
				elIMG.src=src;
				elIMG.border=arr['border'];
				elIMG.hspace=arr['hspace'];
				elIMG.vspace=arr['vspace'];
				if (arr['align']=='none') {
					elIMG.align='';
				} else {
					elIMG.align=arr['align'];
				}
				elIMG.alt=arr['name'];
				elIMG.setAttribute('ar:type',arr['ar:type']);
				if (arr['path']) {
					elIMG.setAttribute('ar:path',arr['path']);
				} else {
					elIMG.setAttribute('ar:path',arr['ar:path']);
				}
				elIMG.className = arr['class'];
			} else {
				el=window.el;
				temp='<IMG SRC="'+src+'"';
				if (arr['border']!='') {
					temp+=' BORDER='+arr['border'];
				}
				if (arr['hspace']!='') {
					temp+=' HSPACE='+arr['hspace'];
				}
				if (arr['vspace']!='') {
					temp+=' VSPACE='+arr['vspace'];
				}
				if (arr['align']!='') {
					temp+=' ALIGN='+arr['align'];
				}
				if (arr['name']!='') {
					temp+=' ALT="'+arr['name']+'"';
				}
				if (arr['class']!='') {
					temp+=' CLASS="'+arr['class']+'"';
				}
				if (arr['ar:type']!='') {
					temp+=' ar:type="'+arr['ar:type']+'"';
				}
				if (arr['path']!='') {
					temp+=' ar:path="'+arr['path']+'"';
				} else if (arr['ar:path']!='') {
					temp+=' ar:path="'+arr['ar:path']+'"';
				}
				temp+='>';
				var control = vdSelectionState.getControlNode(el);
				if (!control) {
					vdSelection.setHTMLText(el, temp);
					vdSelectionState.restore();
				} else {
					if( control.outerHTML ) {
						control.outerHTML = temp;
					} else {
						div = control.ownerDocument.createElement('div');
						div.innerHTML = temp;
						var frag = control.ownerDocument.createDocumentFragment();
						for (var i=0; i < div.childNodes.length; i++) {
							var node = div.childNodes[i].cloneNode(true);
							frag.appendChild(node);
						}
						div = null;
						control.parentNode.replaceChild(frag, control);
					}
				}
			}
			// register change in the editable field, since the focus was already lost through the dialog
			if (editField) {
				checkChangeEndEl(editField);
			}
			vdStoreUndo();
		}
	};
	var	apply = function() {
		if (currentImage) {
			var type=vdGetProperty('vdImageType');
			currentImage.setAttribute('ar:type',type);
			if (tbContentEditOptions['image']['styles'][type]) {
				var className = currentImage.className;
				var classAlign = currentImage.className.match(/\b(vdLeft|vdCenter|vdRight)\b/);
				currentImage.className=tbContentEditOptions['image']['styles'][type]['class'];
				if (classAlign) {
					currentImage.className += ' '+classAlign;
				}
				var temp=new String(currentImage.src);
				temp=temp.substr(0, temp.lastIndexOf('/')+1)+tbContentEditOptions['image']['styles'][type]['template'];
				currentImage.src=temp;
			}
			var align=vdGetProperty('vdImageAlign');
			if (align=='none') {
				currentImage.removeAttribute('align');
			} else {
				currentImage.setAttribute('align',align);
			}
			var alt=vdGetProperty('vdImageAlt');
			if (alt) {
				currentImage.setAttribute('alt',alt);
			} else {
				currentImage.removeAttribute('alt');
			}
			vdStoreUndo();
		}
	};
	
	return {
		insert : function() {
			var args = new Array();
			var elIMG = false;
			var el = false;
			var rg = false;

			if (isEditable()) {
				window.el=false;
				window.elIMG=false;
				window.rg=false;
				el = vdSelectionState.get();
				window.el=el;
				elIMG = vdSelectionState.getControlNode(el);
				if (elIMG) {
					window.elIMG=elIMG;
					if (elIMG && elIMG.tagName=='IMG') {
						src=new String(elIMG.src);
						if (src.substring(0,rootURL.length)==rootURL) {
							src=src.substring(rootURL.length);
						} else { // htmledit component automatically adds http://
							if (src.substring(0,rootURL.length)==rootURL) {
								src=src.substring(rootURL.length);
							} else {
								var temp=new String('http:///');
								if (src.substring(0,temp.length)==temp) {
									src=src.substring(temp.length-1);
								}
							}
						}
						args['src'] = src;
						args['border'] = elIMG.border;
						args['hspace'] = elIMG.hspace;
						args['vspace'] = elIMG.vspace;
						args['align'] = elIMG.align;
						args['name'] = elIMG.alt;
						args['ar:type'] = elIMG.getAttribute('ar:type');
						args['ar:path'] = elIMG.getAttribute('ar:path');
					} else {
						window.elIMG=false;
						window.rg=el;
						src = objectPath;
						args['src'] = src;
						args['hspace'] = "";
						args['vspace'] = "";
						args['align'] = ""; 
						args['name'] = "";
						args['border'] = "";
						if (tbContentEditOptions['image']['default']) {
							var type = tbContentEditOptions['image']['default'];
							args['ar:type'] = type;
							args['class'] = tbContentEditOptions['image']['styles'][type]['class'];
						}
					}
				} else {
					window.elIMG=false;
					window.rg=el;
					src = objectPath;
					args['src'] = src;
					args['hspace'] = "";
					args['vspace'] = "";
					args['align'] = ""; 
					args['name'] = "";
					args['border'] = "";
					if (tbContentEditOptions['image']['default']) {
						var type = tbContentEditOptions['image']['default'];
						args['ar:type'] = type;
						args['class'] = tbContentEditOptions['image']['styles'][type]['class'];
					}
				}
				args['editOptions']=tbContentEditOptions;
				args['stylesheet']=tbContentEditOptions['css']['stylesheet'];
				// args = new Array();

				var url = objectURL + 'dialog.browse.php<?php echo $getargs; ?>&viewmode=icons&root=' + (tbContentEditOptions['photobook']['location'] ? tbContentEditOptions['photobook']['location'] : sitePath + "images/") + '&extraroots=' + sitePath + '&path=' + (tbContentEditOptions['photobook']['location'] ? tbContentEditOptions['photobook']['location'] : sitePath + "images/") + '&pathmode=siterelative';
				muze.dialog.open( url, 'sitemap', { windowFeatures : muze.ariadne.explore.windowprops['dialog_browse'] } )
				.on('submit', function( arr ) {
					if (arr && arr['path']) {
						var ajax=getAjaxRequest();
						ajax.open("get", objectURL+"vd.hyperlink.makeurl.ajax?linkpath="+escape(arr['path']), false);
						ajax.send(arguments);
						arr['src'] = ajax.responseText;
						this.set(arr);
					}
				})
				.always( function() {
					this.close();
				});
			}
		},
		align : function() {
			var	set = function(align) {
				if (currentImage) {
					currentImage.removeAttribute('align');
					var className = currentImage.className;
					className = className.replace(/\b(vdLeft|vdCenter|vdRight)\b/ig, '');
					if ( align!='none' ) {
						className += ' '+align;
					}
					currentImage.className = className;
					vdStoreUndo();
				}
			}
		
			return {
				left : function() {
					this.set("left");
				},
				right : function() {
					this.set("right");
				}
				center : function() {
					this.set("center");
				}
			}
		}(),
		alt : function(alt) {
		}
		title : function(title) {
		}
		upload : function() {
		}
		browse : function() {
		}
		type : function(styleId) {
		}
		remove : function() {
		}
		code : function() {
		}
		link : function() {
		}
	}
}();

vedor.editor.actions.table = function() {
	return {
		insert : function() {
		}
		type : function(styleId) {
			if (vdTableDesigner) {
				var type=vdGetProperty('vdTableType');
				vdTableDesigner.table.className = type;
				vdTableRestoreCursor();
				vdStoreUndo();
			}
		},
		width : function(width) {
			if (vdTableDesigner) {
				if (vdPropertyIsEnabled('vdTotalWidth')) {
					var width=vdGetProperty('vdTotalWidth');
					if (width) {
						var widthType=vdGetProperty('vdTotalWidthType');
						vdTableDesigner.table.style.width = width+widthType;
					}
				} else {
					vdTableDesigner.table.style.width = 'auto';
				}
				vdShowHandles();
				vdTableRestoreCursor();
				vdStoreUndo();
			}
		},
		height : function(height) {
			if (vdTableDesigner) {
				if (vdPropertyIsEnabled('vdTotalHeight')) {
					var height=vdGetProperty('vdTotalHeight');
					if (height) {
						var heightType=vdGetProperty('vdTotalHeightType');
						vdTableDesigner.table.style.height = height+heightType;
					}
				} else {
					vdTableDesigner.table.style.height = 'auto';
				}
				vdShowHandles();
				vdTableRestoreCursor();
				vdStoreUndo();
			}
		}
	}
}();

vedor.editor.actions.tableRow = function() {
	return {
		insertAbove : function() {
			if (vdTableDesigner) {
				vdTableDesigner.addRow('before');
				vdShowHandles();
				vdTableRestoreCursor();
				vdStoreUndo();
			}
		},
		insertBelow : function() {
			if (vdTableDesigner) {
				vdTableDesigner.addRow('after');
				vdShowHandles();
				vdTableRestoreCursor();
				vdStoreUndo();
			}
		},
		remove : function() {
			if (vdTableDesigner) {
				vdTableDesigner.deleteRow();
				vdShowHandles();
				vdTableRestoreCursor();
				vdStoreUndo();
			}
		}
	}
}();
vedor.editor.actions.tableColumn = function() {
	return {
		insertLeft : function() {
			if (vdTableDesigner) {
				vdTableDesigner.addColumn('before');
				vdShowHandles();
				vdTableRestoreCursor();
				vdStoreUndo();
			}
		},
		insertRight : function() {
			if (vdTableDesigner) {
				vdTableDesigner.addColumn('after');
				vdShowHandles();
				vdTableRestoreCursor();
				vdStoreUndo();
			}
		},
		remove : function() {
		}
	}
}();
vedor.editor.actions.tableCell = function() {
	return {
		mergeUp : function() {
			if (vdTableDesigner && vdTableDesigner.checkMergeUp()) {
				vdTableDesigner.mergeVertical('up');
				vdShowHandles();
				vdTableRestoreCursor();
				vdStoreUndo();
			}
		},
		mergeDown : function() {
			if (vdTableDesigner && vdTableDesigner.checkMergeDown()) {
				vdTableDesigner.mergeVertical('down');
				vdShowHandles();
				vdTableRestoreCursor();
				vdStoreUndo();
			}
		},
		mergeLeft : function() {
			if (vdTableDesigner && vdTableDesigner.checkMergeLeft()) {
				vdTableDesigner.mergeHorizontal('left');
				vdShowHandles();
				vdTableRestoreCursor();
				vdStoreUndo();
			}
		},
		mergeRight : function() {
			if (vdTableDesigner && vdTableDesigner.checkMergeRight()) {
				vdTableDesigner.mergeHorizontal('right');
				vdShowHandles();
				vdTableRestoreCursor();
				vdStoreUndo();
			}
		},
		splitHorizontal : function() {
			if (vdTableDesigner) {
				vdTableDesigner.splitHorizontal();
				vdShowHandles();
				vdTableRestoreCursor();
				vdStoreUndo();
			}
		},
		splitVertical : function() {
			if (vdTableDesigner) {
				vdTableDesigner.splitVertical();
				vdShowHandles();
				vdTableRestoreCursor();
				vdStoreUndo();
			}
		},
		height : function(height) {
			if (vdTableDesigner) {
				var rowEl=vdTableDesigner.getRow(vdTableDesigner.currentY);
				if (height) {
					rowEl.style.height = height;
				} else if (vdPropertyIsEnabled('vdCelHeight')) {
					var height=vdGetProperty('vdCelHeight');
					var heightType=vdGetProperty('vdCelHeightType');
					if (!height) {
						height=0;
					}
					rowEl.style.height = height+heightType;
				} else {
					rowEl.style.height = 'auto';
				}
				vdShowHandles();
				if (!height) {
					vdStoreUndo();
				}
			}
		},
		width : function(width) {
			if (vdTableDesigner) {
				var colEl=vdTableDesigner.getCol(vdTableDesigner.currentX);
				if (colEl) {
					if (width) {
						colEl.style.width = width;
					} else if (vdPropertyIsEnabled('vdCelWidth')) {
						var width=vdGetProperty('vdCelWidth');
						var widthType=vdGetProperty('vdCelWidthType');
						if (!width) {
							width=0;
						}
						colEl.style.width = width+widthType;
					} else {
						colEl.style.width = 'auto';
					}
				}
				vdShowHandles();
				if (!width) {
					vdStoreUndo();
				}
			}
		},
		type : function(styleId) {
			if (vdTableDesigner) {
				var type=vdGetProperty('vdCelType');
				vdTableDesigner.currentCell.className = type; // FIXME: replace old type? this removes odd even etc?
				// vdTableDesigner.currentCell.setAttribute('className', type);
				vdStoreUndo();
			}
		},
		align : function() {
			// FIXME: uitwerken hoe de align set dan precies moet gaan werken.
			var	set = function() {
				var alignSet=document.getElementById('vdCelAlignSet');
				if (alignSet.checked) {
					vdCelHorizontalAlign(ha);
					vdCelVerticalAlign(va);
				} else {
					vdCelHorizontalAlign();
					vdCelVerticalAlign();
				}
				vdUpdatePropertyCelAlign(va, ha);
			}
			
			return {
				left : function() {
				},
				right : function() {
				}
				center : function() {
				},
				top : function() {
				},
				bottom : function() {
				},
				middle : function() {
				}
			}
		}(),
	};
}();

vedor.editor.actions.menu = function() {
	return {
		sort : function() {
		},
		toggle : function() {
		}
	}
}();

vedor.editor.actions.slideshow = function() {
	return {
		type : function(styleId) {
		},
		showMenu : function(show) {
		},
		upload : function() {
		},
		browse : function() {
		},
		remove : function() {
		},
		delay : function(delay) {
		},
		speed : function() { 
		},
		slides : function() {
		},
		sort : function() {
		}
	}
}();

vedor.editor.actions.gallery = function() {
	return {
		upload : function() {
		},
		browse : function() {
		},
		remove : function() {
		},
		width : function(width) {
		},
		rowMinHeight : function(height) {
		},
		rowMaxHeight : function(height) {
		},
		sort : function() {
		}
}();

vedor.editor.actions.newspaper = function() {
	return {
		showDateTime : function(show) {
		},
		showImage : function(show) {
		},
		add : function() {
		},
		browse : function() {
		},		
		remove : function () {
		},
		showArticles(number) {
		}
	}
}();
