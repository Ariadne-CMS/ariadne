muze.namespace('vedor.dom.cleaner');

vedor.dom.cleaner = ( function() {

	var self = {

		check:function(str) {
			if (str.indexOf("; mso-")>=0 
				|| str.indexOf("<v:")>=0 
				|| str.indexOf("class=Mso")>=0 
				|| str.match(/<p[^>]*style=/gi) 
				|| str.match(/<\/?(\?XML|ST1|FONT|SHAPE|V:|O:|F:|F|PATH|LOCK|IMAGEDATA|STROKE|FORMULAS)[^>]*>/gi)) 
			{
				return true;
			}
		},

		clean:function(str, mode) {
			switch(mode) {
				case 'none':
					break;
				case 'text':
					str = str.replace(/<\/?[^>]*>/gi, "")
						.replace(/[–]/g,'-') //long –
						.replace(/[‘’]/g, "'") //single smartquotes ‘’ 
						.replace(/[“”]/g, '"') //double smartquotes “”
						.replace(/&nbsp;/g, ' ') // soft spaces
						.replace(/  /g, ' ') // replace multiple spaces
						.replace(/ +\r?\n/g, "\n")
						.replace(/\r?\n(\r?\n)+/g, "<p>")
						.replace(/\n/g, "<br>\n");
					break;
				case 'full':
					str = str.replace(/<\/?(SPAN|DEL|INS|U|DIR)(\s[^>]*)?>/gi, "")
						.replace(/\b(CLASS|STYLE)=\"[^\"]*\"/gi, "")
						.replace(/\b(CLASS|STYLE)=\w+/gi, "");
				case 'word':
				default:
					str = str.replace(/<\/?(\?XML|ST1|FONT|SHAPE|V:|O:|F:|F |PATH|LOCK|IMAGEDATA|STROKE|FORMULAS)[^>]*>/gi, "")
				        .replace(/\bCLASS=\"?MSO\w*\"?/gi, "")
						.replace(/[–]/g,'-') //long –
						.replace(/[‘’]/g, "'") //single smartquotes ‘’ 
						.replace(/[“”]/g, '"') //double smartquotes “”
				        .replace(/align="?justify"?/gi, "") //justify sends some browsers mad
				        .replace(/<(TABLE|TD|TH)(.*)(WIDTH|HEIGHT)[^A-Za-z>]*/gi, "<$1$2") //no fixed size tables
				        .replace(/<([^>]+)>\s*<\/\1>/gi, "") //empty tag
				        .replace(/<p[^>]*>(<br>|&nbsp;)<\/p[^>]*>/gi, "") // remove empty paragraphs
						.replace(/<p[^>]*>(<span[^>]*>)*<span\s+style=["'][^"']*mso-list:[^>]*>.*?<\/span>(.*?)<\/p[^>]*>/gi, "<li>$2</li>") // change list items back to real list items
						.replace(/<li>(\s*<\/span>)+/gi, "<li>"); // eat possible extra closing span tags
			}
		    return str;
		}
	}
	return self;

})();
