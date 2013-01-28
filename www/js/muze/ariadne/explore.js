muze.namespace("muze.ariadne");
muze.namespace("muze.util");

muze.require("muze.ariadne.registry");
muze.require("muze.ariadne.cookie");
muze.require("muze.util.pngfix");
muze.require("muze.util.splitpane");

muze.ariadne.explore = function() {
	var windowprops_common = 'resizable';
	var windowprops_full = 'directories,location,menubar,status,toolbar,resizable,scrollbars';
	var windowsize_small = ",height=230,width=400";
	var windowsize_large = ",height=495,width=550";

	return {
		// Array to store YAHOO.Util.Connect objects.
		loaders : Array(),
		authenticate_panel : null,
		windowprops : {
			'dialog.edit' 			: windowprops_common + windowsize_large,
			'dialog.edit.shortcut'		: windowprops_common + windowsize_large,
			'dialog.rename'			: windowprops_common + windowsize_small,
			'dialog.copy'			: windowprops_common + windowsize_small,
			'dialog.link'			: windowprops_common + windowsize_small,
			'dialog.delete'			: windowprops_common + windowsize_small,
			'dialog.mogrify'		: windowprops_common + windowsize_small,
			'dialog.import'			: windowprops_common + windowsize_large,
			'dialog.export'			: windowprops_common + windowsize_large,


			'dialog.svn.tree.info'		: windowprops_common + windowsize_large,
			'dialog.svn.tree.diff'		: windowprops_common + windowsize_large,
			'dialog.svn.tree.commit'	: windowprops_common + windowsize_large,
			'dialog.svn.tree.revert'	: windowprops_common + windowsize_large,
			'dialog.svn.tree.update'	: windowprops_common + windowsize_large,
			'dialog.svn.tree.unsvn'		: windowprops_common + windowsize_large,
			'dialog.svn.tree.checkout'	: windowprops_common + windowsize_large,
			'dialog.svn.tree.import'	: windowprops_common + windowsize_large,

			'dialog.svn.templates.resolved'	: windowprops_common + windowsize_large,
			'dialog.svn.templates.diff'	: windowprops_common + windowsize_large,
			'dialog.svn.templates.commit'	: windowprops_common + windowsize_large,
			'dialog.svn.templates.revert'	: windowprops_common + windowsize_large,
			'dialog.svn.templates.update'	: windowprops_common + windowsize_large,
			'dialog.svn.templates.delete'	: windowprops_common + windowsize_large,
			'dialog.svn.templates.unsvn'	: windowprops_common + windowsize_large,
			'dialog.svn.templates.checkout'	: windowprops_common + windowsize_large,
			'dialog.svn.templates.import'	: windowprops_common + windowsize_large,

			'dialog.priority'		: windowprops_common + windowsize_small,

			// FIXME: The dialog sizes should be as consistent as possible, not all different sizes.
			'dialog.add' 			: windowprops_common + ',height=600,width=550',
			'dialog.cache'			: windowprops_common + ',height=350,width=500',
			'dialog.templates'		: windowprops_common + ',height=500,width=800',
			'dialog.custom'			: windowprops_common + ',height=300,width=625',
			'dialog.language'		: windowprops_common + ',height=350,width=450',
			'dialog.grants'			: windowprops_common + ',height=570,width=950',
			'dialog.owner'			: windowprops_common + ',height=260,width=400',
			'dialog.grantkey'		: windowprops_common + ',height=330,width=400',
			'dialog.preferences'		: windowprops_common + ',height=400,width=500',
			'dialog.search'			: windowprops_common + ',height=500,width=700',
			'help.about'			: windowprops_common + ',height=375,width=600',

			'help'				: windowprops_full,
			'_new'				: windowprops_full,

			// Deprecated window names.
			'edit_find'				: windowprops_common + ',height=500,width=700',
			'edit_preferences'		: windowprops_common + ',height=400,width=500',
			'edit_object_data'		: windowprops_common + ',height=475,width=550',
			'edit_object_layout'	: windowprops_common + ',height=400,width=700',
			'edit_object_custom'	: windowprops_common + ',height=300,width=625',
			'edit_object_shortcut'	: windowprops_common + ',height=475,width=550',
			'edit_object_grants'	: windowprops_common + ',height=570,width=950',
			'edit_object_types'		: windowprops_common + ',height=300,width=500',
			'edit_object_nls'		: windowprops_common + ',height=350,width=450',
			'edit_priority'			: windowprops_common + ',height=220,width=400',
			'edit_object_grantkey'	: windowprops_common + ',height=330,width=400',
			'edit_object_mogrify'	: windowprops_common + ',height=250,width=400',
			'edit_object_owner'		: windowprops_common + ',height=260,width=400',
			'view_fonts'			: windowprops_common + ',height=300,width=450',
			'help_about'			: windowprops_common + ',height=375,width=600',
			'svn_object_info'		: windowprops_common + ',height=475,width=550',
			'svn_object_diff'		: windowprops_common + ',height=475,width=550',
			'svn_object_commit'		: windowprops_common + ',height=475,width=550',
			'svn_object_revert'		: windowprops_common + ',height=475,width=550',
			'svn_object_update'		: windowprops_common + ',height=475,width=550',
			'svn_object_unsvn'		: windowprops_common + ',height=475,width=550',
			'svn_object_checkout'		: windowprops_common + ',height=475,width=550',
			'svn_object_import'		: windowprops_common + ',height=475,width=550'
		},
		store_root : muze.ariadne.registry.get('store_root'), // FIXME: deze wordt te vroeg gedaan, dus is leeg.
		authenticate_loaders : Array(),
		authenticate : function(callback, message) {
			muze.ariadne.explore.authenticate_loaders.push(callback); // Store the original loaders to fire when authentication is done;
			if (muze.ariadne.explore.authenticate_panel == null) {
				// Check if the panel exists, if not create one. If it does, we already popped up a login screen.
				muze.ariadne.explore.authenticate_panel = new YAHOO.widget.Panel("login_panel", { 
					// not needed, panel sizes to fit the contents. width: "540px", 
					//height: "300px",
					fixedcenter: true,
					close: false,
					draggable: false,
					zindex: 10,
					modal: true,
					visible: false
				});

				var login_path = muze.ariadne.registry.get('path');
				if (!login_path) {
					login_path = '/';
				}

				// FIXME: Find the login form in login_form and insert that into the body;
				var login_url = muze.ariadne.registry.get('store_root') + muze.ariadne.registry.get('path') + 'user.login.form.html';
				if (message) {
					login_url += '?arLoginMessage='+escape(message);
				}
				login_form = muze.load(login_url, true); // Load the url and wait for the result.

				muze.ariadne.explore.authenticate_panel.setBody(login_form);
				muze.ariadne.explore.authenticate_panel.render(document.body);

				var form = muze.ariadne.explore.authenticate_panel.body.getElementsByTagName('FORM')[0];
				form.onsubmit = function() {
					var ARLogin = document.getElementById("ARLogin").value;
					var ARPassword = document.getElementById("ARPassword").value;
					muze.ariadne.explore.authenticate_panel.hide();
					muze.ariadne.explore.authenticate_panel.destroy();
					muze.ariadne.explore.authenticate_panel = null;
					// Fire the original loaders again, and reset the stack afterwards;
					for (i=0; i<muze.ariadne.explore.authenticate_loaders.length; i++) {
						muze.ariadne.explore.authenticate_loaders[i](ARLogin, ARPassword); // Fire the original loader.
					}
					ARLogin = '';
					ARPassword = '';
					muze.ariadne.explore.authenticate_loaders = Array();
					return false;
				}
				muze.ariadne.explore.authenticate_panel.show();
				document.getElementById("ARLogin").focus();
			}
		},
		load : function(url, target, callback, postvars) {
			if (!callback) {
				callback = function(){};
			}
			// Load the contents of given path into the target element
			var load_callback = {
				success : function(result) {
					if (result.responseText !== undefined) {
						// FIXME: do we need to check result.status == 200 as well?
						if (result.getResponseHeader["X-Ariadne-401"]) {
							muze.ariadne.explore.authenticate(
								function(ARLogin, ARPassword) {
									var postvars = "ARLogin=" + ARLogin + "&ARPassword=" + ARPassword;
									muze.ariadne.explore.load(url, target, callback, postvars)
								},
								result.getResponseHeader["X-Ariadne-401"]
							);
						} else {
							target.innerHTML = result.responseText;
							muze.util.pngfix();
							callback();
						}
						delete muze.ariadne.explore.loaders[target.id];
					}
				},

				failure : function(result) {
					if(muze.ariadne.explore.loaders[target.id]) {
						alert(muze.ariadne.nls["notfoundpath"]);
						for (loader_id in muze.ariadne.explore.loaders) {
							YAHOO.util.Connect.abort(muze.ariadne.explore.loaders[loader_id]);
							delete muze.ariadne.explore.loaders[loader_id];
						}
					}
					callback();
				}
			}
			
			// Cancel previous request if there is one.
			if (muze.ariadne.explore.loaders[target.id]) {
				YAHOO.util.Connect.abort(muze.ariadne.explore.loaders[target.id]);
			}
			if (!postvars) {
				muze.ariadne.explore.loaders[target.id] = YAHOO.util.Connect.asyncRequest('GET', url, load_callback); 
			} else {
				 muze.ariadne.explore.loaders[target.id] = YAHOO.util.Connect.asyncRequest('POST', url, load_callback, postvars);
			}
		},
		view : function(target) {
			var node;
			var path;
			if (target.path) {
				node = target;
				path = target.path;
			} else {
				path = target;
			}

			// Contain in dialog root.
			if (!muze.ariadne.explore.viewable(path)) { return }

			if (node) {
				muze.ariadne.explore.tree.view(node);
			} else {
				muze.ariadne.explore.tree.view(path);
			}
			
			muze.ariadne.explore.sidebar.view(path);
			muze.ariadne.explore.viewpane.view(path);
			muze.ariadne.explore.browseheader.view(path);
			muze.ariadne.registry.set('path', path);
		},
		objectadded : function() {
			var path = muze.ariadne.registry.get('path', path);
			muze.ariadne.explore.tree.view(path);
			if (muze.ariadne.explore.sidebar.currentpath) {
				muze.ariadne.explore.sidebar.view(muze.ariadne.explore.sidebar.currentpath);
			} else {
				 muze.ariadne.explore.sidebar.view(path);
			}
			if (muze.ariadne.explore.viewpane.selectedPath) {
				muze.ariadne.explore.viewpane.view(muze.ariadne.explore.viewpane.path);
				muze.ariadne.explore.viewpane.saved_load_handler = muze.ariadne.explore.viewpane.load_handler;
				muze.ariadne.explore.viewpane.load_handler = function() {
					muze.ariadne.explore.viewpane.saved_load_handler();
					muze.ariadne.explore.viewpane.load_handler = muze.ariadne.explore.viewpane.saved_load_handler;
					muze.ariadne.explore.viewpane.reselect();
				};
			} else {
				muze.ariadne.explore.viewpane.view(path);
			}
			if (muze.ariadne.explore.browseheader.currentpath) {
				muze.ariadne.explore.browseheader.view(muze.ariadne.explore.browseheader.currentpath);
			} else {
				muze.ariadne.explore.browseheader.view(path);
			}
		},
		arEdit : function(object, arguments) {
			muze.ariadne.explore.arshow('dialog.edit',this.store_root+object+'dialog.edit.php', arguments);
		},
		arshow : function (windowname, link, arguments) {
			properties=muze.ariadne.explore.windowprops[windowname];
			myNewWindow = 0;
			if( windowname == 'dialog.templates' && muze.ariadne.registry.get('window_new_layout')) {
				myNewWindow = 1;
			}

			if( windowname == 'dialog.grants' && muze.ariadne.registry.get('window_new_grants')) {
				myNewWindow = 1;
			}
			
			windowname = windowname.replace(/\./g, "_");
			if( myNewWindow ) {
				// append a timestamp to allow multiple template windows
				myDate = new Date();
				windowname = myDate.getTime() + windowname;
			}
			// get the SessionID from the top so we can uniquely name windows
			sessionid = muze.ariadne.registry.get("SessionID");

			/* FIXME: doesn't work without frames on mozilla*/ 
			windowsize=muze.ariadne.registry.get(windowname);
			if (windowsize) {
				// alert('windowsize='+windowsize);
				properties=properties+','+windowsize;
			}
			if (!arguments || arguments=='undefined') {
				arguments='';
			}
			arguments=window.location.search+arguments;
			workwindow=window.open(link+arguments, windowname, properties);
			workwindow.focus();
		},
		viewable : function(path) {
			// Contain in dialog root.
			var result = false;
			if (path.indexOf(muze.ariadne.registry.get('root')) == 0) { 
				result = true;
			}
			for (var i in muze.ariadne.explore.tree.baseNodes) {
				if (path.indexOf(muze.ariadne.explore.tree.baseNodes[i].path) == 0) {
					result = true;
				}
			}
			return result;
		}
	}
}();

muze.ariadne.explore.tree = function() {
	var tree;

	function getNodeHTML(node) {
		// Creates the html for displaying in the node, adding icon, path, flag etc.
		var nodeHTML =  "<a href=\"" +
				"javascript:muze.ariadne.explore.view('" + escape(node.path) + "')" +
				"\">";
		if (node.icon) {
			nodeHTML += "<img class='tree_icon' align=\"left\" src=\"" + node.icon + "\">";
		}
		if( node.overlay_icon) {
			nodeHTML += "<img class='tree_overlay_icon' align=\"left\" src=\"" + node.overlay_icon + "\">";
		}

		if (node.svn_icon) {
			nodeHTML += "<img class='tree_svn_icon' align=\"left\" src=\"" + node.svn_icon + "\">";
		}

		nodeHTML += 	"<span class='tree_nodename'>";
		if (node.pre) {
			nodeHTML += node.pre;
		}
		var myname = node.name;
		if (!myname) {
			myname = '';
		}	
		myname = myname.replace("&", "&amp;");
		myname = myname.replace("<", "&lt;");
		myname = myname.replace(">", "&gt;");
		nodeHTML +=	myname + "</span>" +
				"</a>";	
		return nodeHTML;
	}

	function getNodeData(node) {
		var nodeHTML = getNodeHTML(node);
		var result = {html: nodeHTML, path: node.path};
		return result;
	}

	function loadNodeData(node, fnLoadComplete, postvars) {
		//Get the node's path and urlencode it; this is the path we will search for.
		var nodePath = encodeURI(node.path);
		
		//prepare URL for XHR request:
		var time = new Date();
		var sUrl = muze.ariadne.explore.tree.loaderUrl + nodePath + "system.list.folders.json.php?sanity=true&" + time.getTime();

		//prepare our callback object
		var callback = {
			//if our XHR call is successful, we want to make use
			//of the returned data and create child nodes.
			success: function(oResponse) {
				//YAHOO.log(oResponse.responseText);
				if (oResponse.getResponseHeader["X-Ariadne-401"]) {
					muze.ariadne.explore.authenticate(
						function(ARLogin, ARPassword) {
							//muze.ariadne.explore.tree.view(node.path);
							var postvars = "ARLogin=" + ARLogin + "&ARPassword=" + ARPassword;
							loadNodeData(node, fnLoadComplete, postvars);
						}
					);
				} else {
					var oResults = eval("(" + oResponse.responseText + ")");

					var treeNodes = oResults.objects;

					if(treeNodes && treeNodes.length) {
						//Result is an array if more than one result, string otherwise
						if(YAHOO.lang.isArray(treeNodes)) {
							for (var i=0, j=treeNodes.length; i<j; i++) {
								var nodeData = getNodeData(treeNodes[i]);
								var tempNode = new YAHOO.widget.HTMLNode(nodeData, node, false, 1);
								tempNode.path = treeNodes[i].path;
							}
						}
					}
					
					//When we're done creating child nodes, we execute the node's
					//loadComplete callback method which comes in via the argument
					//in the response object (we could also access it at node.loadComplete,
					//if necessary):
					oResponse.argument.fnLoadComplete();
					muze.util.pngfix();
				}
			},
			
			//if our XHR call is not successful, we want to
			//fire the TreeView callback and let the Tree
			//proceed with its business.
			failure: function(oResponse) {
				YAHOO.log("Failed to process XHR transaction.", "info", "example");
				oResponse.argument.fnLoadComplete();
			},
			
			//our handlers for the XHR response will need the same
			//argument information we got to loadNodeData, so
			//we'll pass those along:
			argument: {
				"node": node,
				"fnLoadComplete": fnLoadComplete
			},
			
			//timeout -- if more than 7 seconds go by, we'll abort
			//the transaction and assume there are no children:
			timeout: 15000
		};
		
		//With our callback object ready, it's now time to 
		//make our XHR call using Connection Manager's
		//asyncRequest method:
		if (!postvars) {
			YAHOO.util.Connect.asyncRequest('GET', sUrl, callback);
		} else {
			YAHOO.util.Connect.asyncRequest('POST', sUrl, callback, postvars);
		}
	}

        function buildTree(nodes) {
		//create a new tree:
		tree = new YAHOO.widget.TreeView("treeDiv");
		//turn dynamic loading on for entire tree:
		tree.setDynamicLoad(loadNodeData);

		//get root node for tree:
		var root = tree.getRoot();
		var firstNode;
		for (i in nodes) {
			var node = nodes[i];
			var nodeData = getNodeData(node);
			var tempNode = new YAHOO.widget.HTMLNode(nodeData, root, false, 1);
			tempNode.path = node.path;
			if (!firstNode) {
				firstNode = tempNode;
			}
		}

		//render tree with these toplevel nodes; all descendants of these nodes
		//will be generated as needed by the dynamic loader.
		tree.draw();

		tree.subscribe('clickEvent', function(target) {muze.ariadne.explore.view(target.node);});
		tree.subscribe('enterKeyPressed', function(node) {muze.ariadne.explore.view(node);});
		firstNode.expand();
	}

	var status = 'visible';
	var lastloaded;

	return {
		treewidth: "220px",
		init : function() {
			var baseNodes = muze.ariadne.explore.tree.baseNodes;
			buildTree(baseNodes);
		},
		setpath : function(target) {
			if (!tree) { return; }
			var node;
			if (target.path) {
				node = target;
				path = target.path;
			} else {
				path = target;
				node = tree.getNodeByProperty("path", path);
			}

			// Contain in dialog root.
			if (!muze.ariadne.explore.viewable(path)) { return }

			tree.unsubscribe('expandComplete');
			var parent = path;
			while (!node && parent) {
				parent = parent.substring(0, parent.length-1);
				parent = parent.substring(0, parent.lastIndexOf('/')+1);
				node = tree.getNodeByProperty("path", parent);
			}

			if (parent != path) {
				tree.subscribe('expandComplete', function(node) {
					tree.unsubscribe('expandComplete');
					if (lastloaded != node.path) { // Prevent looping if the object does not show up in the tree.
						lastloaded = node.path;
						muze.ariadne.explore.tree.setpath(path);
					}
				});
			}
			if(node) {
				tree.removeChildren(node);
				node.expand();
			} else {
				tree.unsubscribe('expandComplete');
			}
		},
		refresh : function(path) {
			if (!tree) { return; }
			var node = tree.getNodeByProperty("path", path);
			if (node && node.parent) {
				if (path != tree.getRoot().children[0].path) {
					tree.removeChildren(node.parent);
				}
				muze.ariadne.explore.tree.setpath(path);
			}
		},
		view : function(path) {
			muze.ariadne.explore.tree.setpath(path);
		},
		toggle : function() {
			if (muze.ariadne.explore.tree.status == 'hidden') {
				muze.ariadne.explore.tree.show();
			} else {
				muze.ariadne.explore.tree.hide();
			}
			return status;
		},
		hide : function() {
			if (document.getElementById("explore_managediv").style.left) {
				muze.ariadne.explore.tree.treewidth = document.getElementById("explore_managediv").style.left;
			}
			var animation = new YAHOO.util.Motion('explore_managediv', { left: {to: 0}}, 0.1);
			animation.animate();

			muze.ariadne.explore.tree.status = "hidden";
		},
		show : function() {
			var animation = new YAHOO.util.Motion('explore_managediv', { left: {to: parseInt(muze.ariadne.explore.tree.treewidth)}}, 0.1);
			animation.animate();
			muze.ariadne.explore.tree.status = "visible";
		},
		getstatus : function() {
			return muze.ariadne.tree.status;
		}
	}
}();

muze.ariadne.explore.toolbar = function() {
	return {
		init : function() {
			var menuBar = new YAHOO.widget.MenuBar("explore_menubar", { autosubmenudisplay: true, hidedelay: 750, showdelay: 0, lazyload: true });

			menuBar.render();
		},
		view : function(path) {
			document.getElementById("searchpath").value = path;
		},
		viewparent : function() {
			if( muze.ariadne.explore.viewpane.selectedPath ) {
				path = muze.ariadne.explore.viewpane.selectedPath;
			} else {
				path = muze.ariadne.registry.get('path');
			}
			path = path.substring(0, path.length - 1); // strip last slash;
			lastslash = path.lastIndexOf('/');
			if (lastslash != -1) {
				path = path.substring(0, lastslash);
			}
			path = path + "/";
			muze.ariadne.explore.view(path);
		},
		searchsubmit : function(path) {
			// Check for trailing slash, add if needed.
			if ((path != '/') && (path.substring(path.length - 1, path.length) != '/')) {
				path = path + "/";
			}
			muze.ariadne.explore.view(path);
		},
		searchwindow : function() {
			muze.ariadne.explore.arshow('dialog.search', muze.ariadne.registry.get('store_root')+muze.ariadne.registry.get('path')+'dialog.search.php');
		},
		load : function(path) {
			var sUrl = muze.ariadne.registry.get('store_root')+path+'explore.toolbar.php';
			var fadeOut = new YAHOO.util.Anim("explore_top", { opacity: {to: 0.3}}, 0.2);
			fadeOut.animate();
			var fadeIn = function() {
				var fadeIn = new YAHOO.util.Anim("explore_top", { opacity: {to: 1}}, 0.1);
				fadeIn.animate();

				// Fix for PNG filters in IE6 that break while using another filter;
				fadeIn.onComplete.subscribe(function() {
					document.getElementById("explore_top").style.filter = '';
				});
			};

			muze.ariadne.explore.load(sUrl, document.getElementById("explore_top"), fadeIn, false);
		}
	}
}();

muze.ariadne.explore.searchbar = function() {
	return {
		init : function() {
			// Use an XHRDataSource
			var nodePath = encodeURI(muze.ariadne.registry.get('path'));

			var oDS = new YAHOO.util.XHRDataSource(muze.ariadne.explore.tree.loaderUrl + nodePath + "system.search.json");
			
			// Set the responseType
			oDS.responseType = YAHOO.util.XHRDataSource.TYPE_TEXT;
			// Define the schema of the delimited results
			// oDS.responseSchema = {
			//	recordDelim: "\n",
			//	fieldDelim: "\t"
			//};

			oDS.responseType = YAHOO.util.XHRDataSource.TYPE_JSON;
			// Define the schema of the delimited results
			oDS.responseSchema = {
				resultsList:"entries",
				fields: ["path","name", "icons", "overlay_icons"]
			};

			// Enable caching
			oDS.maxCacheEntries = 5;
			// Instantiate the AutoComplete
			var oAC = new YAHOO.widget.AutoComplete("searchpath", "resultscontainer", oDS);

			oAC.formatResult = function(oResultItem, sQuery) { 
				// This was defined by the schema array of the data source
				var image = "<img src='" + oResultItem[2].medium + "'>";
				if (oResultItem[3] && oResultItem[3].medium) {
					image += "<img class='icon_overlay' src='" + oResultItem[3].medium + "'>";
				}

				return image + " <span>" + oResultItem[0] + "<br>" + oResultItem[1] + "</span>";
			};

			oAC.maxResultsDisplayed = 20;

			return {
				oDS: oDS,
				oAC: oAC
			};
		}
	}
}();

muze.ariadne.explore.splitpane = function() {
	return {
		init : function() {
			muze.util.splitpane.getHorizSplitPane("splitpane_slider", "splitpane_thumb", 0, 9999, "explore_tree", "explore_managediv");
		}
	}
}();

muze.ariadne.explore.sidebar = function() {
	return {
		currentpath : null,
		invisibleSections : new Object(),
		exists : function() {
			if (document.getElementById("sidebar")) {
				return true;
			} else {
				return false;
			}
		},
		objectadded : function() {
			var fileListFrame=parent.document.getElementById('archildren');
			if (fileListFrame) {
				fileListFrame.src=fileListFrame.src;
			} else {
				parent.archildren.src=parent.archildren.src;
			}
		},
		arEdit : function(object, arguments) {
			muze.ariadne.explore.arshow('dialog.edit',muze.ariadne.registry.get("store_root")+object+'dialog.edit.php', arguments);
		},
		removeFromCookie : function(section){
			if (muze.ariadne.explore.sidebar.invisibleSections[section]) {
				delete muze.ariadne.explore.sidebar.invisibleSections[section];
				muze.ariadne.explore.sidebar.setInvisiblesCookie();
			}
		},
		addToCookie : function(section) {
			muze.ariadne.explore.sidebar.invisibleSections[section] = 1;
			muze.ariadne.explore.sidebar.setInvisiblesCookie();
		},
		setInvisiblesCookie : function() {
			var value = '';
			for (section in muze.ariadne.explore.sidebar.invisibleSections) {
				value += section + ";";
			}
			muze.ariadne.cookie.set('invisibleSections', value);
		},
		getInvisiblesCookie : function() {
			var value = muze.ariadne.cookie.get('invisibleSections');
			if ( value != 0 ) {
				cookie = unescape(value);
				cookie = cookie.substring(0, cookie.length - 1);
				cookie = cookie.split(';');
				for (j=0; j < cookie.length; j++ ) {
					var section = cookie[j];
					muze.ariadne.explore.sidebar.invisibleSections[section] = 1;
				}
			}
		},
		removefilter: function() {
			if (muze.ariadne.explore.sidebar.exists()) {
				document.getElementById("sidebar").style.filter = '';
			}
		},
		load : function(path) {
			// Contain in dialog root.
			if (!muze.ariadne.explore.viewable(path)) { return }

			muze.ariadne.explore.sidebar.currentpath = path;
			var template = 'explore.sidebar.php';
			
			var sUrl = muze.ariadne.registry.get('store_root')+path+template;

			var fadeOut = new YAHOO.util.Anim("sidebar", { opacity: {to: 0.3}}, 0.2);
			fadeOut.animate();
			var fadeIn = function() {
				var fadeIn = new YAHOO.util.Anim("sidebar", { opacity: {to: 1}}, 0.1);
				fadeIn.animate();

				if (document.getElementById("workspace_body")) {
					document.getElementById("explore_managediv").className = "managediv workspaced";
				} else {
					document.getElementById("explore_managediv").className = "managediv";
				}

				// Fix for PNG filters in IE6 that break while using another filter;
				fadeIn.onComplete.subscribe(function() {
					document.getElementById("sidebar").style.filter = '';
				});
			};

			muze.ariadne.explore.load(sUrl, document.getElementById("sidebar"), fadeIn, false);
		},
		view : function(path) {
			if (muze.ariadne.explore.sidebar.exists()) {
				muze.ariadne.explore.sidebar.load(path);
			}
		}
	}
}();

muze.ariadne.explore.sidebar.section = function() {
	return {
		isCollapsed : function(section) {
			var sectiondiv = document.getElementById(section + '_body').parentNode;
			if (YAHOO.util.Dom.hasClass(sectiondiv, 'collapsed')) {
				return true;
			} else {
				return false;
			}
		},
		collapse : function(section) {
			var sectiondiv = document.getElementById(section + '_body').parentNode;

			var animation = new YAHOO.util.Motion(section + '_body', { height: {to: 0}}, 0.05);
			animation.onComplete.subscribe(function() {
				YAHOO.util.Dom.removeClass(sectiondiv, 'expanded');
				YAHOO.util.Dom.addClass(sectiondiv, 'collapsed');
			});
			animation.animate();

			muze.ariadne.explore.sidebar.addToCookie(section);
		},
		expand : function(section) {
			var sectiondiv = document.getElementById(section + '_body').parentNode;


			document.getElementById(section + "_body").style.height = "auto";
			var myheight = parseInt(document.getElementById(section + "_body").offsetHeight);
			document.getElementById(section + "_body").style.height = "0px";
			
			YAHOO.util.Dom.removeClass(sectiondiv, 'collapsed');
			YAHOO.util.Dom.addClass(sectiondiv, 'expanded');

			var animation = new YAHOO.util.Motion(section + '_body', { height: {to: myheight}}, 0.05);
			animation.animate();
			muze.ariadne.explore.sidebar.removeFromCookie(section);
		},
		toggle : function(section) {
			if (muze.ariadne.explore.sidebar.section.isCollapsed(section)) {
				muze.ariadne.explore.sidebar.section.expand(section);
			} else {
				muze.ariadne.explore.sidebar.section.collapse(section);
			}
		}
	}
}();

muze.ariadne.explore.viewpane = function() {
	return {
		saved_load_handler : null,
		selectedItem : null,
		selectedPath : null,
		path : null,
		typefilter : null,
		exists : function() {
			if (document.getElementById("viewpane")) {
				return true;
			} else {
				return false;
			}
		},
		removefilter: function() {
			if (muze.ariadne.explore.viewpane.exists()) {
				document.getElementById("viewpane").style.filter = '';
			}
		},
		filter : function(type) {
			muze.ariadne.explore.viewpane.typefilter = type;
			muze.ariadne.explore.viewpane.view(muze.ariadne.explore.viewpane.path);
		},
		setitempath : function(item) {
			if (!item.path) {
				if (item.tagName == "TR") {
					var record = muze.ariadne.explore.viewpane.dataTable.getRecord(item);
					item.path = record.getData("path");
				} else {
					var href = item.getElementsByTagName("A")[0].href;
					href = decodeURI(href);

					var store_root = muze.ariadne.registry.get('store_root');

					// Find the location of the store root, and take everything behind it.
					store_root_pos = href.indexOf(store_root);

					// If not found, remove the language
					if (store_root_pos < 0) {
						store_root = store_root.substring(0, store_root.lastIndexOf("/")); 
						store_root_pos = href.indexOf(store_root);
					}

					// If still not found, remove the session
					if (store_root_pos < 0) {
						store_root = store_root.substring(-1);
						store_root = store_root.substring(0, store_root.lastIndexOf("/"));

						store_root_pos = href.indexOf(store_root);
					}

					path = href.substring(store_root_pos + store_root.length, href.length);
					// Remove "explore.html from the end, and all other trailing stuff.
					explore_pos = path.indexOf('explore.html'); // FIXME: configbaar maken.
					item.path = path.substring(0, explore_pos);
				}
			}
		},
		reselect : function() {
			var viewmode=muze.ariadne.registry.get('viewmode');
			if (!viewmode) {
				viewmode = 'list';
			}

			if (muze.ariadne.explore.viewpane.selectedItem && muze.ariadne.explore.viewpane.selectedItem.path) {
				var path = muze.ariadne.explore.viewpane.selectedItem.path;
				if (viewmode != 'details') {
					var items = document.getElementById("viewpane").getElementsByTagName("LI");
					for(i=0; i<items.length; i++) {
						muze.ariadne.explore.viewpane.setitempath(items[i]);
						if (items[i].path == path) {
							YAHOO.util.Dom.addClass(items[i], 'selected');
							muze.ariadne.explore.viewpane.selectedItem = items[i];
							break;
						}
					}
				} else {
					if (muze.ariadne.explore.viewpane.dataTable) {
						var records = muze.ariadne.explore.viewpane.dataTable.getRecordSet().getRecords();
						for (i=0; i<records.length; i++) {
							if(records[i].getData("path") == path) {
								records[i].path = records[i].getData("path");
								muze.ariadne.explore.viewpane.selectedItem = records[i];
								muze.ariadne.explore.viewpane.dataTable.selectRow(records[i]);
								break;
							}
						}
					}
				}
			}
		},
		selectItem : function(item) {
			YAHOO.util.Dom.addClass(item, 'selected');
			if (item != muze.ariadne.explore.viewpane.selectedItem){
				muze.ariadne.explore.viewpane.selectedItem = item;
				muze.ariadne.explore.viewpane.onSelectItem(item);
			}
		},
		unselectItem : function() {
			if (muze.ariadne.explore.viewpane.selectedItem) {
				YAHOO.util.Dom.removeClass(muze.ariadne.explore.viewpane.selectedItem, 'selected');
				muze.ariadne.explore.viewpane.selectedItem = null;
			}
			if (muze.ariadne.explore.viewpane.dataTable) {
				muze.ariadne.explore.viewpane.dataTable.unselectAllRows();
			}

			var items = YAHOO.util.Dom.getElementsByClassName("selected", "li", "viewpane");
			for (var i=0; i<items.length; i++) {
				YAHOO.util.Dom.removeClass(items[i], "selected");
			}
		},
		rowClick : function(args) {
			var event = args.event;
			YAHOO.util.Event.stopEvent(event);

			this.unselectAllRows();

			var data = this.getRecord(args.target);

			var path = data.getData("path");
			args.target.path = path;

			//var filename = data.getData("filename");
			//args.target.path = muze.ariadne.explore.viewpane.path + filename + '/';

			this.selectRow(args.target);
			muze.ariadne.explore.viewpane.selectedItem = args.target;
			muze.ariadne.explore.viewpane.onSelectItem(args.target);
			// FIXME: with the regular onClick not in place, we need a way to unselect a row.
		},
		rowDoubleClick : function(args) {
			var event = args.event;
			YAHOO.util.Event.stopEvent(event);

			var path = this.getRecord(args.target).getData('path');

			//var path = muze.ariadne.explore.viewpane.path + this.getRecord(args.target).getData('filename') + '/';
			muze.ariadne.explore.view(path);
		},
		onEventHighlightRow : function(event) {
			YAHOO.util.Dom.addClass(event.target, "highlight");
		},
		onEventUnhighlightRow : function(event) {
			YAHOO.util.Dom.removeClass(event.target, "highlight");
		},
		onClick : function(event) {
			YAHOO.util.Event.preventDefault(event);
			muze.ariadne.explore.viewpane.unselectItem();
			var target = YAHOO.util.Event.getTarget(event);
			while(target.id != "viewpane") {
				if (YAHOO.util.Dom.hasClass(target, 'explore_item')) {
					if (YAHOO.util.Dom.hasClass(target, 'selected')) {
						YAHOO.util.Dom.removeClass(target, 'selected');
					} else {
						muze.ariadne.explore.viewpane.selectItem(target);
					}
					return;
				}
				if (target.parentNode) {
					target = target.parentNode;
				} else {
					break;
				}
			}
			var item = new Object();
			item.path = muze.ariadne.explore.viewpane.path;
			muze.ariadne.explore.viewpane.onSelectItem(item);
		},
		setviewmode : function(viewmode) {
			muze.ariadne.registry.set('viewmode', viewmode);
			muze.ariadne.cookie.set('viewmode', viewmode);
			var path = muze.ariadne.registry.get('path');
			muze.ariadne.explore.viewpane.view(path);
			muze.ariadne.explore.browseheader.view(path);
			muze.ariadne.explore.sidebar.view(path);
		},
		update : function(qs) {
			var browse_template = muze.ariadne.registry.get('browse_template');
			var viewmode=muze.ariadne.registry.get('viewmode');
			if (!viewmode) {
				viewmode='list';
			}
			var url = browse_template+viewmode+'.php?'+qs+'&'+document.location.search;
			muze.ariadne.explore.viewpane.browseto(url);
		},
		browseto : function(url) {
			var archildren = document.getElementById("archildren");

			var fadeOut = new YAHOO.util.Anim("archildren", { opacity: {to: 0.3}}, 0.2);
			fadeOut.animate();
			var fadeIn = function() {
				var fadeIn = new YAHOO.util.Anim("archildren", { opacity: {to: 1}}, 0.1);

				// Fix for PNG filters in IE6 that break while using another filter;
				fadeIn.onComplete.subscribe(function() {
					muze.ariadne.explore.viewpane.removefilter();
				});
				fadeIn.animate();
				YAHOO.util.Event.removeListener('archildren', 'click', muze.ariadne.explore.viewpane.onClick);
				YAHOO.util.Event.addListener('archildren', 'click', muze.ariadne.explore.viewpane.onClick);
				YAHOO.util.Event.removeListener('archildren', 'selected', muze.ariadne.explore.viewpane.onSelected);
				YAHOO.util.Event.addListener('archildren', 'selected', muze.ariadne.explore.viewpane.onSelected);
				YAHOO.util.Event.removeListener('archildren', 'clearselection', muze.ariadne.explore.viewpane.unselectItem);
				YAHOO.util.Event.addListener('archildren', 'clearselection', muze.ariadne.explore.viewpane.unselectItem);

				muze.ariadne.explore.viewpane.load_handler();
			};
			muze.ariadne.explore.load(url, archildren, fadeIn);
		},
		onSelected : function(event) {
			// FIXME: Add correct handling for row selection for details view.

			var items = YAHOO.util.Dom.getElementsByClassName("selectable-selected", "*", "archildren");
			if (items.length == 0) {
				muze.ariadne.explore.viewpane.unselectItem();
			} else if (items.length == 1) {
				if (items[0].tagName == "TR") {
					muze.ariadne.explore.viewpane.dataTable.selectRow(items[0]);
				} else {
					muze.ariadne.explore.viewpane.selectItem(items[0]);
				}
			}

			for (var i=0; i< items.length; i++) {
				if (items[i].tagName == "TR") {
					muze.ariadne.explore.viewpane.dataTable.selectRow(items[i]);
				} else {
					YAHOO.util.Dom.addClass(items[i],"selected");
				}
			}

			var unselectitems = YAHOO.util.Dom.getElementsByClassName("yui-dt-selected", "*", "archildren");
			for (var j=0; j<unselectitems.length; j++) {
				if (YAHOO.util.Dom.hasClass(unselectitems[j], "selectable-selected")) {
				} else {
					console.log("unselecting " + unselectitems[j]);
					muze.ariadne.explore.viewpane.dataTable.unselectRow(unselectitems[j]);
				}
			}


			var unselectitems = YAHOO.util.Dom.getElementsByClassName("selected", "*", "archildren");
			console.log(unselectitems.length);
			console.log(unselectitems);
			for (var j=0; j<unselectitems.length; j++) {
				if (YAHOO.util.Dom.hasClass(unselectitems[j], "selectable-selected")) {
				} else {
					YAHOO.util.Dom.removeClass(unselectitems[j], "selected");
				}
			}

			if (items.length != 1) {
				// Select the parent object.
				var item = new Object();
				item.path = muze.ariadne.explore.viewpane.path;
				muze.ariadne.explore.viewpane.onSelectItem(item);
			} else {
				muze.ariadne.explore.viewpane.onSelectItem(items[0]);
			}
		},
		onSelectItem : function(item) {
			muze.ariadne.explore.viewpane.setitempath(item);
			muze.ariadne.explore.sidebar.view(item.path);
			muze.ariadne.explore.browseheader.view(item.path);
			muze.ariadne.explore.viewpane.selectedPath = item.path;
			document.getElementById("searchpath").value = item.path;
		},
		view : function(path, page) {
			// Contain in dialog root.
			if (!muze.ariadne.explore.viewable(path)) { return }

			if (!muze.ariadne.explore.viewpane.exists()) { return }
			if (!page) {
				page = 1;
			}
			var browse_template = muze.ariadne.registry.get('browse_template');
			var viewmode = muze.ariadne.cookie.get('viewmode');
			if( viewmode == 0 ) {
				viewmode = muze.ariadne.registry.get('viewmode');
			} else {
				muze.ariadne.registry.set('viewmode', viewmode);
			}
			var store_root = muze.ariadne.registry.get('store_root');

			var url = store_root + path + browse_template + viewmode + '.php?';
			if (muze.ariadne.explore.viewpane.typefilter) {
				url = url + 'type=' + muze.ariadne.explore.viewpane.typefilter;
			}
			if (page) {
				url = url + 'page=' + page;
			}

			muze.ariadne.explore.viewpane.browseto(url);
			document.getElementById("searchpath").value = path;
			muze.ariadne.explore.viewpane.path = path;
			muze.ariadne.explore.viewpane.selectedPath = path;
		}
	}
}();

muze.ariadne.explore.browseheader = function() {
	return {
		currentpath : null,
		exists : function() {
			if (document.getElementById("browseheader")) {
				return true;
			} else {
				return false;
			}
		},
		removefilter: function() {
			if (muze.ariadne.explore.viewpane.exists()) {
				document.getElementById("browseheader").style.filter = '';
			}
		},
		load : function(path) {
			muze.ariadne.explore.browseheader.currentpath = path;
			var sUrl = muze.ariadne.registry.get('store_root')+path+'explore.browse.header.php';

			var fadeOut = new YAHOO.util.Anim("browseheader", { opacity: {to: 0.3}}, 0.2);
			fadeOut.animate();
			var fadeIn = function() {
				var fadeIn = new YAHOO.util.Anim("browseheader", { opacity: {to: 1}}, 0.1);
				fadeIn.animate();

				// Fix for PNG filters in IE6 that break while using another filter;
				fadeIn.onComplete.subscribe(function() {
					document.getElementById("browseheader").style.filter = '';
				});
			};

			muze.ariadne.explore.load(sUrl, document.getElementById("browseheader"), fadeIn, false);
		},
		view : function(path) {
			if (muze.ariadne.explore.browseheader.exists()) {
				muze.ariadne.explore.browseheader.load(path);
			}
		}

	}
}();
