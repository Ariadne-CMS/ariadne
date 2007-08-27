This is a replacement for the Ariadne explorer tree (usually located on the
left side in the Ariadne backend). This tree is built using the YUI
javascript library. It has 3 major files:
- yui_tree.php: this is starting point. It is called in the frameset in
explorer.html, which passes some information to it using GET variables. As
far as they are needed, these variables are passed to yui_tree.js.

- yui_tree.js: javascript parts for the tree. Basicly a modified version of
the dynamic tree widget example supplied with the YAHOO libs.

- lib/templates/pobject/yui_tree.load.ajax is the connection to Ariadne. The
script returns the objects below a given path, and returns them in a
javascript structure that can be evalled directly.

Issues:
- refresh and logoff buttons have the next in them - these should be
replaced with seperate images and texts.
