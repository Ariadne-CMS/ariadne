<?php
  /******************************************************
  arguments: $path, $function="view.html", $args="", 
             $layout -> different frame layouts.
                at least three frames defined: treeview, treeload, view
                onOpen will call link+"treeload.html?$args" in treeload
                normal click will call link+"$function?$args" in view

  call GetFolder.html onOpen with $args

  

  *******************************************************

  start met $path geopend

  FIXME: node toevoegen moet op basis van link gaan, dus elke node met die
         link moet de nieuwe node als child krijgen.

  ******************************************************/
?>
<html>
<head>
<title>Tree: <?php echo $path; ?></title>
<script>
<!--

  Nodes=new Array();
  Links=new Array();
  id=1;
  root=0;
  target=0;
  caller=0;

  function frop() {
    alert('frop');
  }

  function AddLinks(parent, icon, name, link, pre) {
    if (Links[parent]) {
      for (i=0; i<Links[parent].length; i++) {
        if (Links[parent][i].status=="Open" || Links[parent][i].firstChild) {
          Links[parent][i].add(icon, name, link, pre);
        }
      }
    }
    Draw();
  }

  function UpdateLinks(icon, name, link, pre) {
    if (Links[link]) {
      for (i=0; i<Links[link].length; i++) {
        Links[link][i].set(icon, name, link, pre);
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

  function Node(parent, prev, next, icon, name, link, pre) {
    this.id=id++;
    this.parent=parent;
    this.prev=prev;
    this.next=next;
    this.name=name;
    this.pre=pre;
    this.link=link;
    this.icon=icon;
    this.status="Closed";
    this.children=new Array;
    this.add=addNode;
    this.set=setNode;
    this.del=delNode;
    this.draw=drawNode;
    this.order=orderNode;
    Nodes[this.id]=this;
    if (!Links[this.link]) {
      Links[this.link]=new Array();
    }
    Links[this.link][Links[this.link].length]=this;
  }

  function setNode(icon, name, link, pre) {
    this.icon=icon;
    this.name=name;
    this.link=link;
    this.pre=pre;
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

  function addNode(icon, name, link, pre) {
    if (this.children[link]) { // this node already exists
      if (this.children[link].name!=name) { // name changed, so reorder.
        this.children[link].del(); // first remove
        this.add(icon, name, link, pre); // then add again
      } else {
        this.children[link].set(icon, name, link);
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
          node.prev=new Node(node.parent, temp, node, icon, name, link, pre);
          if (temp) {
            temp.next=node.prev;
          } else {
            node.parent.firstChild=node.prev;
          }
        }
      } else { // new object last in list.
        node.next=new Node(node.parent, node, 0, icon, name, link, pre);
      }
    } else { // first object, no need to compare         
      this.firstChild=new Node(this, 0, 0, icon, name, link, pre);
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

  function drawNode(pre, target) {
    variant='';
    line='<img src="../../images/tree/line.gif" width=18 height=18 border=0 align="left" hspace="0" vspace="0" alt="">';
    blank='<img src="../../images/tree/blank.gif" width=18 height=18 border=0 align="left" hspace="0" vspace="0" alt="">';
    target.write('<nobr>'+pre);
    if (!this.next) {
      if (!this.prev && !this.parent) {
        variant='only';
      } else {
        variant='bottom';
      }
      pre=pre+blank;
    } else {
      if (!this.prev && !this.parent) {
        variant='top';
      }
      pre=pre+line;
    }
    if (this.status=="Closed") {
      plus='<img src="../../images/tree/plus'+variant+'.gif" border="0" width=18 height=18 border=0 align="left" hspace="0" vspace="0" alt="">';
      target.writeln('<A HREF="javascript:toggle('+this.id+');">'+plus+'</A>'
                    +'<img src="../../images/icons/'+this.icon+'.gif" border="0" alt="" valign="middle">'
		    +'<span class="node"><nobr><A HREF="javascript:View('+this.id+');"'
                    +' onMouseOver="window.status=\''+this.link+'\'; return true;" onMouseOut="window.status=\'\'; return true;">'
                    +this.pre+this.name+'</A></nobr></span></nobr><br clear="all">');
    } else {
      minus='<img src="../../images/tree/minus'+variant+'.gif" border="0" width=18 height=18 border=0 align="left" hspace="0" vspace="0" alt="">';
      target.writeln('<A HREF="javascript:toggle('+this.id+');">'+minus+'</A>'
                    +'<img src="../../images/icons/'+this.icon+'.gif" border="0" alt="" valign="middle">'
		    +'<span class="node"><nobr><A HREF="javascript:View('+this.id+');"'
                    +' onMouseOver="window.status=\''+this.link+'\'; return true;" onMouseOut="window.status=\'\'; return true;">'
                    +this.pre+this.name+'</A></nobr></span></nobr><br clear="all">');
      if (this.firstChild) {
        this.currnode=this.firstChild;
        while (this.currnode.next) {
          this.currnode.draw(pre, target);
          this.currnode=this.currnode.next;
        }
        this.currnode.draw(pre, target);
      }
    }     
  }

  function Draw() {
    <?php 
      // netscape takes the path of the calling page as root.
      // thus calculate the absolute path of the tree widget docroot. 
      $rootpath=substr($PHP_SELF,0,-strlen("root.php"));
    ?>
    window.treeview.document.location="<?php echo $rootpath; ?>draw.html";
    window.parent.LoadingDone(); 
  }

  function toggle(id) {
    if (Nodes[id]) {
      node=Nodes[id];
      if (node.status=="Closed") {
        node.status="Open";
        if (!node.firstChild) {
          treeload.document.location='<?php echo $loader; ?>'+node.link+'treeload.phtml?node='+id;
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
    window.parent.Loading(); 
    window.parent.View(Nodes[id].link);
  }

  function Open(id) {
    window.parent.Loading();
    window.parent.Open(Nodes[id].link);
  }

  function init(icon, name, path, pre) {
    Nodes=new Array();
    Links=new Array();
    id=1;

    root=new Node(0, 0, 0, icon, name, path, pre);
  }

// -->
</script>
<?php
  if (!$layout) {
    $layout="./frames.js";
  } else {
    $layout=ereg_replace("[\./\\]","",$layout).".js";
  }
  include($layout);
?>
</html>