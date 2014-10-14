muze.namespace("muze.ariadne.templates", function() {
	return {
		init : function(myColumnDefs) {
			// Called from within the view template!
			var myDataSource = new YAHOO.util.DataSource(YAHOO.util.Dom.get("templatesTable"));
			myDataSource.responseType = YAHOO.util.DataSource.TYPE_HTMLTABLE;   
			myDataSource.responseSchema = {   
				fields: [{key:"svn"},
					{key:"type"},
					{key:"template"},
					{key:"language"},
					{key:"size"},
					{key:"modified"},
					{key:"search"}
					]
			};

			var myDataTable = new YAHOO.widget.DataTable("templatesDiv", myColumnDefs, myDataSource, {});
			myDataTable.subscribe("rowClickEvent", muze.ariadne.templates.onEventSelectRow);  
			myDataTable.subscribe("rowMouseoverEvent", muze.ariadne.templates.onEventHighlightRow);  
			myDataTable.subscribe("rowMouseoutEvent", muze.ariadne.templates.onEventUnhighlightRow);  
		},
		onEventSelectRow : function(args) {
			var data = this.getRecord(args.target);
			var language = data.getData("language");
			var elm = document.createElement("DIV");
			elm.innerHTML = language;
			var target = elm.getElementsByTagName("A")[0].href;
			document.location = target;
		},
		onEventHighlightRow : function(event) {
			YAHOO.util.Dom.addClass(event.target, "highlight");
		},
		onEventUnhighlightRow : function(event) {
			YAHOO.util.Dom.removeClass(event.target, "highlight");
		}
	}
});

muze.namespace( 'muze.ariadne.templates.control', function() {
	return {
		init : function() {
			var menuBar = new YAHOO.widget.MenuBar("basicmenu", { autosubmenudisplay: true, hidedelay: 750, showdelay: 0, lazyload: true });
			menuBar.render();
		},
		newTemplate : function() {
			var wgWizForm = document.getElementById("wgWizForm");
			wgWizForm.action = "dialog.templates.edit.php";
			wgWizForm.submit();
		}
	}
});

YAHOO.widget.DataTable.MSG_EMPTY = ''; // Message to display if no templates are found.
YAHOO.util.Event.onDOMReady(muze.ariadne.templates.control.init);
