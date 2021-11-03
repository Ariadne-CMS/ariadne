/*----------------------------------------------------------------------------\
|                                Table Sort                                   |
|-----------------------------------------------------------------------------|
|                         Created by Erik Arvidsson                           |
|                  (http://webfx.eae.net/contact.html#erik)                   |
|                      For WebFX (http://webfx.eae.net/)                      |
|-----------------------------------------------------------------------------|
| A DOM 1 based script that allows an ordinary HTML table to be sortable.     |
|-----------------------------------------------------------------------------|
|                  Copyright (c) 1998 - 2002 Erik Arvidsson                   |
|-----------------------------------------------------------------------------|
| This software is provided "as is", without warranty of any kind, express or |
| implied, including  but not limited  to the warranties of  merchantability, |
| fitness for a particular purpose and noninfringement. In no event shall the |
| authors or  copyright  holders be  liable for any claim,  damages or  other |
| liability, whether  in an  action of  contract, tort  or otherwise, arising |
| from,  out of  or in  connection with  the software or  the  use  or  other |
| dealings in the software.                                                   |
| - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - |
| This  software is  available under the  three different licenses  mentioned |
| below.  To use this software you must chose, and qualify, for one of those. |
| - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - |
| The WebFX Non-Commercial License          http://webfx.eae.net/license.html |
| Permits  anyone the right to use the  software in a  non-commercial context |
| free of charge.                                                             |
| - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - |
| The WebFX Commercial license           http://webfx.eae.net/commercial.html |
| Permits the  license holder the right to use  the software in a  commercial |
| context. Such license must be specifically obtained, however it's valid for |
| any number of  implementations of the licensed software.                    |
| - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - |
| GPL - The GNU General Public License    http://www.gnu.org/licenses/gpl.txt |
| Permits anyone the right to use and modify the software without limitations |
| as long as proper  credits are given  and the original  and modified source |
| code are included. Requires  that the final product, software derivate from |
| the original  source or any  software  utilizing a GPL  component, such  as |
| this, is also licensed under the GPL license.                               |
|-----------------------------------------------------------------------------|
| 1998-??-?? | First version                                                  |
|-----------------------------------------------------------------------------|
| Created 1998-??-?? | All changes are in the log above. | Updated 2001-??-?? |
\----------------------------------------------------------------------------*/


var dom = (document.getElementsByTagName) ? true : false;
var ie5 = (document.getElementsByTagName && document.all) ? true : false;
var arrowUp, arrowDown;

if (ie5 || dom)
	initSortTable();

function initSortTable() {
	arrowUp = document.createElement("SPAN");
	var tn = document.createTextNode("5");
	arrowUp.appendChild(tn);
	arrowUp.className = "arrow";

	arrowDown = document.createElement("SPAN");
	var tn = document.createTextNode("6");
	arrowDown.appendChild(tn);
	arrowDown.className = "arrow";
}



function sortTable(tableNode, nCol, bDesc, sType) {
	var tBody = tableNode.tBodies[0];
	var trs = tBody.rows;
	var trl= trs.length;
	var a = new Array();
	
	for (var i = 0; i < trl; i++) {
		a[i] = trs[i];
	}
	
	var start = new Date;
	window.status = "Sorting data...";
	a.sort(compareByColumn(nCol,bDesc,sType));
	window.status = "Sorting data done";
	
	for (var i = 0; i < trl; i++) {
		tBody.appendChild(a[i]);
		window.status = "Updating row " + (i + 1) + " of " + trl +
						" (Time spent: " + (new Date - start) + "ms)";
	}
	
	// check for onsort
	if (typeof tableNode.onsort == "string")
		tableNode.onsort = new Function("", tableNode.onsort);
	if (typeof tableNode.onsort == "function")
		tableNode.onsort();
}

function CaseInsensitiveString(s) {
	return String(s).toUpperCase();
}

function parseDate(s) {
	return Date.parse(s.replace(/\-/g, '/'));
}

/* alternative to number function
 * This one is slower but can handle non numerical characters in
 * the string allow strings like the follow (as well as a lot more)
 * to be used:
 *    "1,000,000"
 *    "1 000 000"
 *    "100cm"
 */

function toNumber(s) {
    return Number(s.replace(/[^0-9\.]/g, ""));
}

function compareByColumn(nCol, bDescending, sType) {
	var c = nCol;
	var d = bDescending;
	
	var fTypeCast = String;
	
	if (sType == "Number")
		fTypeCast = Number;
	else if (sType == "Date")
		fTypeCast = parseDate;
	else if (sType == "CaseInsensitiveString")
		fTypeCast = CaseInsensitiveString;

	return function (n1, n2) {
		if (fTypeCast(getInnerText(n1.cells[c])) < fTypeCast(getInnerText(n2.cells[c])))
			return d ? -1 : +1;
		if (fTypeCast(getInnerText(n1.cells[c])) > fTypeCast(getInnerText(n2.cells[c])))
			return d ? +1 : -1;
		return 0;
	};
}

function sortColumnWithHold(e) {
	// find table element
	var el = ie5 ? e.srcElement : e.target;
	var table = getParent(el, "TABLE");
	
	// backup old cursor and onclick
	var oldCursor = table.style.cursor;
	var oldClick = table.onclick;
	
	// change cursor and onclick	
	table.style.cursor = "wait";
	table.onclick = null;
	
	// the event object is destroyed after this thread but we only need
	// the srcElement and/or the target
	var fakeEvent = {srcElement : e.srcElement, target : e.target};
	
	// call sortColumn in a new thread to allow the ui thread to be updated
	// with the cursor/onclick
	window.setTimeout(function () {
		sortColumn(fakeEvent);
		// once done resore cursor and onclick
		table.style.cursor = oldCursor;
		table.onclick = oldClick;
	}, 100);
}

function sortColumn(e) {
	var tmp = e.target ? e.target : e.srcElement;
	var tHeadParent = getParent(tmp, "THEAD");
	var el = getParent(tmp, "TD");

	if (tHeadParent == null)
		return;
		
	if (el != null) {
		var p = el.parentNode;
		var i;

		// typecast to Boolean
		el._descending = !Boolean(el._descending);

		if (tHeadParent.arrow != null) {
			if (tHeadParent.arrow.parentNode != el) {
				tHeadParent.arrow.parentNode._descending = null;	//reset sort order		
			}
			tHeadParent.arrow.parentNode.removeChild(tHeadParent.arrow);
		}

		if (el._descending)
			tHeadParent.arrow = arrowUp.cloneNode(true);
		else
			tHeadParent.arrow = arrowDown.cloneNode(true);

		el.appendChild(tHeadParent.arrow);

			

		// get the index of the td
		var cells = p.cells;
		var l = cells.length;
		for (i = 0; i < l; i++) {
			if (cells[i] == el) break;
		}

		var table = getParent(el, "TABLE");
		// can't fail
		
		sortTable(table,i,el._descending, el.getAttribute("type"));
	}
}


function getInnerText(el) {
	if (ie5) return el.innerText;	//Not needed but it is faster
	
	var str = "";
	
	var cs = el.childNodes;
	var l = cs.length;
	for (var i = 0; i < l; i++) {
		switch (cs[i].nodeType) {
			case 1: //ELEMENT_NODE
				str += getInnerText(cs[i]);
				break;
			case 3:	//TEXT_NODE
				str += cs[i].nodeValue;
				break;
		}
		
	}
	
	return str;
}

function getParent(el, pTagName) {
	if (el == null) return null;
	else if (el.nodeType == 1 && el.tagName.toLowerCase() == pTagName.toLowerCase())	// Gecko bug, supposed to be uppercase
		return el;
	else
		return getParent(el.parentNode, pTagName);
}