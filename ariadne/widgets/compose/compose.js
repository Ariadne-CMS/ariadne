	var wgComposeTable=new Array();
	var wgComposing=false;
	var wgComposeKey=19;
	var wgComposeBuffer='';
	var wgComposeNumeric=false;
	var wgComposeSymbolic=false;

	wgComposeTable['i']=-1;
	wgComposeTable['i!']="iexcl";

	wgComposeTable['$']=-1;
	wgComposeTable['$c']="cent";
	wgComposeTable['$p']="pound";
	wgComposeTable['$#']="curren";
	wgComposeTable['$y']="yen";
	wgComposeTable['$e']="euro";
	wgComposeTable['$f']="fnof";

	wgComposeTable['|']="brvbar";
	wgComposeTable['#']="sect";
	wgComposeTable['"']="uml";

	wgComposeTable['c']=-1;
	wgComposeTable['c@']="copy";

	wgComposeTable['o']=-1;
	wgComposeTable['of']="ordf";

	wgComposeTable['<']=-1;
	wgComposeTable['<<']="laquo";

	wgComposeTable['!']="not";
	wgComposeTable['-']="shy";

	wgComposeTable['r']=-1;
	wgComposeTable['r@']="reg";

	wgComposeTable['_']="macr";

	wgComposeTable['d']=-1;
	wgComposeTable['dg']="deg";

	wgComposeTable['+']=-1;
	wgComposeTable['+-']="plusmn";

	wgComposeTable['^']=-1;
	wgComposeTable['^1']="sup1";
	wgComposeTable['^2']="sup2";
	wgComposeTable['^3']="sup3";

	wgComposeTable["'"]="acute";
	wgComposeTable['m']="micro";

	wgComposeTable['p']=-1;
	wgComposeTable['pp']="para";

	wgComposeTable['.']="middot";
	wgComposeTable[',']="cedil";

	wgComposeTable['o']=-1;
	wgComposeTable['om']="ordm";

	wgComposeTable['>']=-1;
	wgComposeTable['>>']="raquo";

	wgComposeTable['1']=-1;
	wgComposeTable['1/']=-1;
	wgComposeTable['1/4']="frac14";
	wgComposeTable['1/2']="frac12";
	wgComposeTable['3']=-1;
	wgComposeTable['3/']=-1;
	wgComposeTable['3/4']="frac34";

	wgComposeTable['i']=-1;
	wgComposeTable['i?']="iquest";

	wgComposeTable['A']=-1;
	wgComposeTable['A`']="Agrave";
	wgComposeTable["A'"]="Aacute";
	wgComposeTable['A^']="Acirc"; 
	wgComposeTable['A~']="Atilde";
	wgComposeTable['A"']="Auml";  
	wgComposeTable['Ao']="Aring"; 
	wgComposeTable['AE']="AElig"; 

	wgComposeTable['C']=-1;
	wgComposeTable['C,']="Ccedil";

	wgComposeTable['E']=-1;
	wgComposeTable['E`']="Egrave";
	wgComposeTable["E'"]="Eacute";
	wgComposeTable['E^']="Ecirc"; 
	wgComposeTable['E"']="Euml";  
	wgComposeTable['ET']=-1;   
	wgComposeTable['ETH']="ETH";   

	wgComposeTable['I']=-1;
	wgComposeTable['I`']="Igrave";
	wgComposeTable["I'"]="Iacute";
	wgComposeTable['I^']="Icirc"; 
	wgComposeTable['I"']="Iuml";  

	wgComposeTable['N']=-1;   
	wgComposeTable['N~']="Ntilde";

	wgComposeTable['O']=-1;   
	wgComposeTable['O`']="Ograve";
	wgComposeTable["O'"]="Oacute";
	wgComposeTable['O^']="Ocirc"; 
	wgComposeTable['O~']="Otilde";
	wgComposeTable['O"']="Ouml";  
	wgComposeTable['O/']="Oslash";

	wgComposeTable['*']="times"; 

	wgComposeTable['U']=-1;
	wgComposeTable['U`']="Ugrave";
	wgComposeTable["U'"]="Uacute";
	wgComposeTable['U^']="Ucirc"; 
	wgComposeTable['U"']="Uuml";  

	wgComposeTable['Y']=-1;
	wgComposeTable["Y'"]="Yacute";

	wgComposeTable['T']=-1;
	wgComposeTable['TH']="THORN"; 

	wgComposeTable['s']=-1;
	wgComposeTable['sz']="szlig"; 

	wgComposeTable['a']=-1;
	wgComposeTable['a`']="agrave";
	wgComposeTable["a'"]="aacute";
	wgComposeTable['a^']="acirc"; 
	wgComposeTable['a~']="atilde";
	wgComposeTable['a"']="auml";  
	wgComposeTable['ao']="aring"; 
	wgComposeTable['ae']="aelig"; 

	wgComposeTable['c']=-1;
	wgComposeTable['c,']="ccedil";

	wgComposeTable['e']=-1;
	wgComposeTable['e`']="egrave";
	wgComposeTable["e'"]="eacute";
	wgComposeTable['e^']="ecirc"; 
	wgComposeTable['e"']="euml";  

	wgComposeTable['i']=-1;
	wgComposeTable['i`']="igrave";
	wgComposeTable["i'"]="iacute";
	wgComposeTable['i^']="icirc"; 
	wgComposeTable['i"']="iuml";  

	wgComposeTable['et']=-1;
	wgComposeTable['eth']="eth";   

	wgComposeTable['n']=-1;
	wgComposeTable['n~']="ntilde";

	wgComposeTable['o']=-1;
	wgComposeTable['o`']="ograve";
	wgComposeTable["o'"]="oacute";
	wgComposeTable['o^']="ocirc"; 
	wgComposeTable['o~']="otilde";
	wgComposeTable['o"']="ouml";  
	wgComposeTable['/']="divide";
	wgComposeTable['o/']="oslash";

	wgComposeTable['u']=-1;
	wgComposeTable['u`']="ugrave";
	wgComposeTable["u'"]="uacute";
	wgComposeTable['u^']="ucirc"; 
	wgComposeTable['u"']="uuml";  

	wgComposeTable['y']=-1;
	wgComposeTable["y'"]="yacute";

	wgComposeTable['t']=-1;
	wgComposeTable['th']="thorn"; 
	wgComposeTable['tm']="trade";
	wgComposeTable['y"']="yuml";  

	wgComposeTable['l']=-1;
	wgComposeTable['l/']="lstrok";
	wgComposeTable['L']=-1;
	wgComposeTable['L/']="Lstrok";  

	wgComposeTable['#']=-2;

	wgComposeTable['&']=-3;

	function wgCompose_check(e) {
		var keycode = e.keyCode;
		if (keycode==wgComposeKey) {
			if (!wgComposing) {
				wgComposing=true;
				window.status='Composing...';
			}
			return false;
		} else {
			return true;
		}
	}

	function wgCompose_keydown(e) {
		var keycode = e.keyCode;
		var key=String.fromCharCode(keycode);

		if (keycode==wgComposeKey) {
			if (!wgComposing) {
				wgComposing=true;
				wgComposeNumeric=false;
				window.status='Composing...';
			}
			return false;
		}
		if (wgComposing) {
			if (keycode==27) { // esc
				wgCompose_stop();
				return false;
			} else if (keycode==8) { // backspace
				wgComposeBuffer=wgComposeBuffer.substr(0, wgComposeBuffer.length-1);
				window.status=window.status.substr(0, window.status.length-1);
				return false;
			}
		}
		return true;
	}

	function wgCompose_keypress (e)	{
		var keycode = e.keyCode;
		var key=String.fromCharCode(keycode);

		if (keycode==wgComposeKey) {
			if (!wgComposing) {
				wgComposing=true;
				wgComposeNumeric=false;
				window.status='Composing...';
			}
			return false;
		}
		if (wgComposing) {
			if (keycode==27) { // esc
				wgCompose_stop();
				return false;
			}
			wgComposeBuffer+=key;
			if (value=wgComposeTable[wgComposeBuffer]) {
				if (value==-3) {
					wgComposeSymbolic=true;
					window.status=window.status+'&';
				} else if (value==-2) {
					wgComposeNumeric=true;
					window.status=window.status+'#';
				} else if (value!=-1) {
					wgComposeBuffer='&'+wgComposeTable[wgComposeBuffer]+';';
					wgCompose_show(wgComposeBuffer);
					wgCompose_stop();
				} else {
					window.status="Composing: "+wgComposeBuffer;
				}
			} else if (wgComposeSymbolic) {
				if ((keycode==13) || (key==';')) {
					wgComposeBuffer='&'+wgComposeBuffer.substr(1, wgComposeBuffer.length-2)+';';
					wgCompose_show(wgComposeBuffer);
					wgCompose_stop();
				} else {
					window.status=window.status+key;
				}
			} else if (wgComposeNumeric) {
				if ((keycode>=48) && (keycode<=57)) {
					window.status=window.status+key;
				} else if (keycode==13) {
					wgComposeBuffer='&'+wgComposeBuffer.substr(0, wgComposeBuffer.length-1)+';';
					wgCompose_show(wgComposeBuffer);
					wgCompose_stop();
				} else {
					wgCompose_stop();
				}
			} else {
				wgCompose_stop();
				return false;
			}
			return false;
		} else {
			return true;
		}
	}

	function wgCompose_stop() {
		wgComposing=false;
		wgComposeNumeric=false;
		wgComposeBuffer='';
		window.status='';
	}

/*
	function wgCompose_show(buffer) {
		if (document.all) {
			showit=document.all['showcompose'];
			showit.innerHTML=buffer;
		} else if (showit=document.getElementByid('showcompose')) {
			showit.innerHTML=buffer;
		} else {
			alert(buffer);
		}
	}
*/
