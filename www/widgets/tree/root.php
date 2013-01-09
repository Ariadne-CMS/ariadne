<?php
  /******************************************************
  arguments: $path, $function="view.html", $args="", 
             $layout -> different frame layouts.
                at least three frames defined: treeview, treeload, view
                onOpen will call link+"tree.load.html?$args" in treeload
                normal click will call link+"$function?$args" in view

  call GetFolder.html onOpen with $args

  

  *******************************************************

  start met $path geopend

  FIXME: node toevoegen moet op basis van link gaan, dus elke node met die
         link moet de nieuwe node als child krijgen.

  ******************************************************/

	/* retrieve HTTP GET variables */
	$path 	= $_GET["path"];
	$loader = $_GET["loader"];	
	$wwwroot = $_GET["wwwroot"];
	$interface = $_GET["interface"];
?>
<html>
<head>
<META content="text/html; charset=UTF-8" http-equiv=Content-Type>
<title>Tree: <?php echo $path; ?></title>
<script language="javascript">
<!--

  Nodes=new Array();
  Links=new Array();
  ShortCuts=new Array();
  id=1;
  root=0;
  target=0;
  caller=0;
  ShowInvis=false;
  NodeOpened=false;

  function AddLinks(parent, icon, name, link, pre, shortcut) {
    if (!shortcut) {
      shortcut=0;
    }
    if (Links[parent]) {
      for (i=0; i<Links[parent].length; i++) {
        if (Links[parent][i].status=="Open" || Links[parent][i].firstChild) {
          Links[parent][i].add(icon, name, link, pre, shortcut);
        }
      }
    }
	if (ShortCuts[parent]) {
      for (i=0; i<ShortCuts[parent].length; i++) {
        if (ShortCuts[parent][i].status=="Open" || ShortCuts[parent][i].firstChild) {
          ShortCuts[parent][i].add(icon, name, link, pre);
        }
      }
    }
        
    Draw();
  }

  function UpdateLinks(icon, name, link, pre, shortcut) {
    if (!shortcut) {
      shortcut=0;
    }
    if (Links[link]) {
      for (i=0; i<Links[link].length; i++) {
        Links[link][i].set(icon, name, link, pre, shortcut);
      }
    }
    Draw();
  }

  function DelLinks(link) {
    if (Links[link]) {
      for (i=0; i<Links[link].length; i++) {
        Links[link][i].del();
      }
      Draw();
    }
  }

  function Node(parent, prev, next, icon, name, link, pre, shortcut) {
    this.id=id++;
    this.parent=parent;
    this.prev=prev;
    this.next=next;
    this.name=name;
    this.fullname=name;
    this.name=this.name.replace(/^((&[^;]*;|.){0,20}).*/g, "$1");
    if (this.name.length<name.length) {
      this.name=this.name+"...";
    }
    this.pre=pre;
    this.link=link;
    this.icon=icon;
    this.status="Closed";
	this.visible=true;
	this.editable=true;
    this.children=new Array;
    this.add=addNode;
    this.set=setNode;
    this.del=delNode;
    this.draw=drawNode;
    this.order=orderNode;
    Nodes[this.id]=this;
	if (shortcut) {
      if (!ShortCuts[shortcut]) {
        ShortCuts[shortcut]=new Array();
      }
      ShortCuts[shortcut][ShortCuts[shortcut].length]=this;
	}
    if (!Links[this.link]) {
      Links[this.link]=new Array();
    }
    Links[this.link][Links[this.link].length]=this;
  }

  function setNode(icon, name, link, pre, shortcut) {
    this.icon=icon;
    this.name=name;
    this.link=link;
    this.pre=pre;
    if (this.shortcut && (this.shortcut!=shortcut)) {
      // first remove old shortcut entries
      if (ShortCuts[this.shortcut]) {
        ii=0;
        newshortcuts=new Array();
        for (i=0; i<ShortCuts[this.shortcut].length; i++) {
          if (ShortCuts[this.shortcut][i].link!=this.link) {
            newshortcuts[ii]=ShortCuts[this.shortcut][i];
            ii++;
          }
        }
        ShortCuts[this.shortcut]=newshortcuts;
      }
    }
    if (shortcut && this.shortcut!=shortcut) {
      // then add new entry
      this.shortcut=shortcut;
      ShortCuts[this.shortcut][ShortCuts[this.shortcut].length]=this;
    }
    this.order();
  }

  function delNode() { 
    if (this.prev) {
      this.prev.next=this.next;
    } else if (this.parent) {
      this.parent.firstChild=this.next;
    }
    if (this.next) {
      this.next.prev=this.prev;
    }
    // free memory someway?
  }

  function addNode(icon, name, link, pre, shortcut) {
	if (!shortcut) {
		shortcut=false;
	}
    if (this.children[link]) { // this node already exists
      if (this.children[link].name!=name) { // name changed, so reorder.
        this.children[link].del(); // first remove
        this.add(icon, name, link, pre, shortcut); // then add again
      } else {
        this.children[link].set(icon, name, link, shortcut);
      }   
    } else if (this.firstChild) {
      node=this.firstChild;
      while ((node.name<name) && node.next) {
        node=node.next;
      }
      if (node.name>=name) { // insert new object before node
        if (node.name==name && node.link==link && node.icon==icon) {
	  // do nothing, node is exactly the same as existing
        } else {
          temp=node.prev
          node.prev=new Node(node.parent, temp, node, icon, name, link, pre, shortcut);
          if (temp) {
            temp.next=node.prev;
          } else {
            node.parent.firstChild=node.prev;
          }
        }
      } else { // new object last in list.
        node.next=new Node(node.parent, node, 0, icon, name, link, pre, shortcut);
      }
    } else { // first object, no need to compare         
      this.firstChild=new Node(this, 0, 0, icon, name, link, pre, shortcut);
    }
  }

  function orderNode() {
    // before reordering, first cut this node from the list
    if (this.prev) {
      this.prev.next=this.next;
    } else {
      this.parent.firstChild=this.next;
    }
    if (this.next) {
      this.next.prev=this.prev;
    }
    if (this.parent && this.parent.firstChild) {
      // now start at the first node and search till a node has a 'bigger' name
      node=this.parent.firstChild;
      while ((node.name<this.name) && node.next) {
        node=node.next;
      }
      if (node.name>=this.name) { // insert new object before node
        // reinsert this before 'node'
        // first fix pointers from 'this'
        // then fix pointers from prev or parent to this
        this.next=node;
        if (node.prev) {
          this.prev=node.prev;
          this.prev.next=this;
        } else {
          this.parent.firstChild=this;
        }
        // finally fix pointers from next to this
        node.prev=this;
      } else { // new object last in list.
        node.next=this;
        this.next=0;
        this.prev=node;
      }
    } else {
      this.parent.firstChild=this;
    }
  }

function drawNode(pre, level) {
	// display node with its style	
	// + - both displayed, one in hidden div, other visible
	// onClick -> switch div and display/hide children
    result='';
	addpre='';
	imgplus='plus';
	imgminus='minus';
	imgjoin='join';
	img='';
	currimg='';
	plusminus='';
	if (this.visible || ShowInvis) {
		style='';
		post='';
		if (this.editable) {
			style+='<span class="editable">';
		} else {
			style+='<span class="fixed">';
		}
		post+='</span>';
		next=this.next;
		while (next && !next.visible && !ShowInvis) {
			next=next.next;
		} 
		prev=this.prev;
		while (prev && !prev.visible && !ShowInvis) {
			prev=prev.prev;
		}
		if (next) {
			addpre='<img src="<?php echo $wwwroot; ?>images/tree/line.gif" alt="" width=20" height="20" align="left" valign="middle">'
			if (!prev && pre=='') {
				img+='top';
			}
		} else if (this.parent) {
			addpre='<img src="<?php echo $wwwroot; ?>images/tree/blank.gif" alt="" width=20" height="20" align="left" valign="middle">'
			if (!prev && pre=='') {
				img+='only';
			} else {
				img+='bottom';
			}
		}
		if (!this.visible) {
			style+='<span class="invisible">';
			post+='</span>';
		}
		if (this.parent) {
			if (this.status=="Open") {
				currimg='minus';
				plusminus='<a href="javascript:parent.toggle(\''+this.id+'\');"><img src="<?php echo $wwwroot; ?>images/tree/minus'+img+'.gif" alt="" width=20" height="20" border="0" align="left" valign="middle"></a>';
			} else {
				currimg='plus';
				plusminus='<a href="javascript:parent.toggle(\''+this.id+'\');"><img src="<?php echo $wwwroot; ?>images/tree/plus'+img+'.gif" alt="" width=20" height="20" border="0" align="left" valign="middle"></a>';
			}
		}
		if (this.icon) {
//			icon='<img class="icon" src="<?php echo $wwwroot; ?>images/icons/'+this.icon+'.gif" alt="" width="20" height="20" border="0" align="left">';
			icon='<img class="icon" src="'+this.icon+'" alt="" width="20" height="20" border="0" align="left">';
		} else {
			icon='';
		}
		width=(level*20)+125;
		// Mozilla ignores <nobr> tags with image placement, so calculate a minimum width here
		result="\n"+'<div id="'+this.id+'" class="node"><div class="row" style="width: '+width+'px;"><nobr>'+
			pre+plusminus+'<a href="javascript:parent.View(\''+this.id+'\');" title="'+this.fullname+'" '+
			'onMouseOver="window.status=\''+this.link+'\'; return true;" onMouseOut="window.status=\'\'; return true;">'+
            icon+'<span class="item">'+style+this.pre+this.name+post+'</span></a></nobr></div>';
		if (this.firstChild && this.status=="Open" ) {
			result=result+'<div name="sub" class="submenu" id="'+this.id+'_submenu">';
			result=result+this.firstChild.draw(pre+addpre, level+1);
			result=result+'</div>';
		}
	} 
	result=result+'</div>';
	if (this.next) {
		result=result+this.next.draw(pre, level);
 	}
    return result;
}

function Draw() {
	var target=window.treeview;
	if (target.document.body && ( target.document.body.scrollTop || target.document.body.scrollLeft ) ) {
		y=target.document.body.scrollTop;
		x=target.document.body.scrollLeft;
	} else {
		x=target.pageXOffset;
		y=target.pageYOffset;
	}
	// Draw the entire tree
	target.document.open();

    MenuDraw="<html>\n<head>\n<link REL=STYLESHEET type='text/css' HREF='<?php echo $wwwroot; ?>styles/<?php echo $interface; ?>tree.css'>\n";
    MenuDraw=MenuDraw+"</head>\n<body scroll='auto'><div id='nodes'>\n";
	MenuDraw=MenuDraw+root.draw('',1);
	MenuDraw=MenuDraw+"</div></body>\n</html>";
	target.document.writeln(MenuDraw);
	target.document.close();
	if (x || y) {
		// alert('x: '+x+'; y: '+y);
		if (x && NodeOpened) {
			x+=25;
		} else {
			x-=25;
		}
		if (y && NodeOpened) {
			y+=20;
		} else {
			y-=20;
		}
		NodeOpened=false;
		setTimeout("ScrollTree("+x+","+y+")", 50);
//		target.scrollTo(x,y);
	}
	parent.LoadingDone();
}

function ScrollTree(x, y) {
	target=window.treeview;
	if (target.document && target.document.readystate) {
		if (target.document.readystate!='loaded') {
			setTimeout("ScrollTree", 10, target, x, y);
		}
	}
	target.scrollTo(x,y);
}

  function toggle(id) {
    if (Nodes[id]) {
      node=Nodes[id];
      if (node.status=="Closed") {
        node.status="Open";
		NodeOpened=true;
        if (!node.firstChild) {
          treeload.document.location='<?php echo $loader; ?>'+node.link+'tree.load.phtml?node='+id;
        } else {
          Draw();
        }
      } else {
        node.status="Closed";
        Draw();
      }
    } else {
      count=Nodes.length;
      msg='';
      for (i=0; i<count; i++) {
        msg+='id: '+i+' value: '+Nodes[i]+'\n';
      }
      Draw();
    }
  }

  function View(id) {
	// this call will break-up konqueror
	//    window.parent.Loading(); 
    window.parent.View(Nodes[id].link);
  }

  function Open(id) {
    window.parent.Loading();
    window.parent.Open(Nodes[id].link);
  }

  function init(icon, name, path, pre, shortcut) {
    Nodes=new Array();
    Links=new Array();
    ShortCuts=new Array();
    id=1;

    if (!shortcut) {
      shortcut=0;
    }
    root=new Node(0, 0, 0, icon, name, path, pre, shortcut);
  }

// -->
</script>
<?php
  if (!isset($layout) || (!$layout)) {
    $layout="./frames.js";
  } else {
    $layout=preg_replace("|[\./\\\]|","",$layout).".js";
  }
  include($layout);
?>
</html>