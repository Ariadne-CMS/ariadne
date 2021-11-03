/*
		javascript dom library for muze modules

		string getInnerHTML(object node)
			This method returns the inner HTML of a node cross browser.

			examples: 
					<div id="mynode">Hello world!</div>

					--------- javascript code ---------
					var myInnerHTML = muze.dom.getInnerHTML(document.getElementById("mynode"));
					------------ returns: ---------
					Hello world!
					
		string getOuterHTML(object node)
			This method returns the outer HTML of a node cross browser. 

			examples:
					<div id="mynode">Hello world!</div>

					--------- javascript code ---------
					var myOuterHTML = muze.dom.getOuterHTML(document.getElementById("mynode"));
					------------ returns: ---------
					<div id="mynode">Hello world!</div>
					
				
*/

muze.namespace('muze.dom', function() {

	var _leafElems = ["IMG", "HR", "BR", "INPUT"];
	var leafElems = {};
	for (var i=0; i<_leafElems.length; i++)
		leafElems[_leafElems[i]] = true;

	return {
		getInnerHTML: function(node) {
			var str = "";
			for (var i=0; i<node.childNodes.length; i++) {
				str += muze.dom.getOuterHTML(node.childNodes.item(i));
			}
			return str;
		},

		getOuterHTML: function(node) {
			var str = "";
	
			switch (node.nodeType) {
				case 1: // ELEMENT_NODE
					str += "<" + node.nodeName;
					for (var i=0; i<node.attributes.length; i++) {
						if (node.attributes.item(i).nodeValue != null) {
							str += " "
							str += node.attributes.item(i).nodeName;
							str += "=\"";
							str += node.attributes.item(i).nodeValue;
							str += "\"";
						}
					}

					if (node.childNodes.length == 0 && leafElems[node.nodeName])
						str += ">";
					else {
						str += ">";
						str += muze.dom.getInnerHTML(node);
						str += "</" + node.nodeName + ">"
					}
				break;
				
				case 3:	//TEXT_NODE
					str += node.nodeValue;
				break;
			
				case 4: // CDATA_SECTION_NODE
					str += "<![CDATA[" + node.nodeValue + "]]>";
				break;
					
				case 5: // ENTITY_REFERENCE_NODE
					str += "&" + node.nodeName + ";"
				break;

				case 8: // COMMENT_NODE
					str += "<!--" + node.nodeValue + "-->"
				break;
			}

			return str;
		}
	}
});
