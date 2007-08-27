var muze = function() {};
muze.apps = function() {};
muze.apps.ariadne = function() {};
muze.apps.ariadne.explore = function() {};

muze.apps.ariadne.explore.tree = function() {
	var tree;

	function getNodeHTML(node) {
		// Creates the html for displaying in the node, adding icon, path, flag etc.
		var nodeHTML =  "<a href=\"" +
				"javascript:parent.View('" + escape(node.path) + "')" +
				"\">";
		if (node.icon) {
			nodeHTML += "<img class='tree_icon' align=\"left\" src=\"" + node.icon + "\">";
		}

		nodeHTML += 	"<span class='tree_nodename'>";
		if (node.pre) {
			nodeHTML += node.pre;
		}
		nodeHTML +=	node.name + "</span>" +
				"</a>";	
		return nodeHTML;
	}

	function loadNodeData(node, fnLoadComplete)  {
		//Get the node's path and urlencode it; this is the path we will search for.
		var nodePath = encodeURI(node.path);
		
		//prepare URL for XHR request:
		var time = new Date();
		var sUrl = muze.apps.ariadne.explore.tree.loaderUrl + nodePath + "yui_tree.load.ajax?" + time.getTime();

		//prepare our callback object
		var callback = {
			//if our XHR call is successful, we want to make use
			//of the returned data and create child nodes.
			success: function(oResponse) {
				//YAHOO.log(oResponse.responseText);
				var oResults = eval("(" + oResponse.responseText + ")");

				if(oResults.Nodes && oResults.Nodes.length) {
					//Result is an array if more than one result, string otherwise
					if(YAHOO.lang.isArray(oResults.Nodes)) {
						for (var i=0, j=oResults.Nodes.length; i<j; i++) {
							var nodeHTML = getNodeHTML(oResults.Nodes[i]);
							var tempNode = new YAHOO.widget.HTMLNode(nodeHTML, node, false, 1);
							tempNode.path = oResults.Nodes[i].path;
						}
					}
				}
				
				//When we're done creating child nodes, we execute the node's
				//loadComplete callback method which comes in via the argument
				//in the response object (we could also access it at node.loadComplete,
				//if necessary):
				oResponse.argument.fnLoadComplete();
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
			timeout: 7000
		};
		
		//With our callback object ready, it's now time to 
		//make our XHR call using Connection Manager's
		//asyncRequest method:
		YAHOO.util.Connect.asyncRequest('GET', sUrl, callback);
	}

        function buildTree(node) {
		//create a new tree:
		tree = new YAHOO.widget.TreeView("treeDiv");
		//turn dynamic loading on for entire tree:
		tree.setDynamicLoad(loadNodeData);

		//get root node for tree:
		var root = tree.getRoot();

		var nodeHTML = getNodeHTML(node);
		var tempNode = new YAHOO.widget.HTMLNode(nodeHTML, root, false, 1);
		tempNode.path = node.path;

		//render tree with these toplevel nodes; all descendants of these nodes
		//will be generated as needed by the dynamic loader.
		tree.draw();

		tempNode.expand();
	}

	return {
		init: function() {
			var baseNode = Array();
			baseNode.path = this.basePath;
			baseNode.name = this.baseName;
			baseNode.icon = this.baseIcon;
			buildTree(baseNode);
		}
	}
}();

//once the DOM has loaded, we can go ahead and set up our tree:
YAHOO.util.Event.onDOMReady(muze.apps.ariadne.explore.tree.init, muze.apps.ariadne.explore.tree, true);
