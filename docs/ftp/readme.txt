1. Using the Ariadne FTP server.
--------------------------------

This document outlines howto use the Ariadne FTP server. It assumes that you
already have installed the Ariadne FTP server. You can find more information
about installing the Ariadne FTP server in the file "install.txt" in this 
directory.


2. The problem with FTP.
------------------------

FTP is a very simple protocol for file transfers. Unfortunately this doesn't
match very well with the rich content of an Ariadne system. An Ariadne file
contains a lot more information than just the plain file. It has a title, 
perhaps a summary, in different languages, it can have templates assigned to 
it, and it can even contain other files.

3. List modes.
--------------

'List modes' are our solution to this problem. When you first login to the
Ariadne FTP server you will be confronted with a normal listing of the 
current directory. You will also notice that there are 3 shortcuts starting
and ending with an '#' sign:

	#files#
	#templates#
	#objects#

These shortcuts tell the ftp server to switch to that list mode, in effect 
changing which part of each object the server shows. 

#files#

	The '#files#' listmode shows you all of the Ariadne file objects in
	the current directory as normal files while all other Ariadne objects will 
	be shown as directories. 
	When downloading a file the template 'ftp.view.html' will be called on the 
	object and the output sent to the FTP client.

	If you upload a file, while listmode is set to '#files#', the uploaded file 
	will be transformed into Ariadne object. The class of the object depends on
	the mimetype of the file. For each uploaded file the mimetype is checked by
	walking through a list of mimetype -> objecttype mappings. These mappings 
	can be configured in the ariadne.phtml configuration file.

	An example of such a configuration is:

		$ARConfig->cache[".."]->mimetypes['^image/']= "pphoto";
		$ARConfig->cache[".."]->mimetypes['.*']     = "pfile";

	The index of the array 'mimetypes' is a regular expression which is matched
	with the mimetype of the uploaded file. If the regular expression matches 
	the mimetype then the class of the object will be set to that value.

	For example, if an uplaoded file has the mimetype 'image/x-jpeg',
	the '^image/' expression will match it and the new object will be of the 
	class "pphoto".


#templates#

	Within the '#templates#' listmode, all objects are accessable as
	directories. This way you can browse to any object. 

	The templates of the objects will be accessable as files under the
	objects where these templates are defined. These files are shown and stored
	in the following filename format:

		[base].[type].[template name].[language]

		[base] 
		This is either empty or 'local'. 
		The base information tells Ariadne whether the template is local to the	
		current object ([base] = 'local') or the template is a default template
		([base] isn't set).
 
		[type]
		This is the class this template is defined for (e.g., 'pobject', 
		'pphoto', ...)

		[template name]
		This is the name of the template (eg view.html)

		[language]
		This tells Ariadne which language this template uses (en, nl, any, ...)


	Any file uploaded in #templates# mode will be saved as a pinp template in 
	the current object.
	It won't pass through the mimetype check. So, if you upload an image in 
	#templates# mode, Ariadne will not create a pphoto object for you, it will 
	just store this file as a template, which is probably not what you want.

#objects#

	This mode is currently not finished. In the future it will allow you to 
	download the entire object in an as yet unknown format.
	FOr now it can be used to quickly browse through the Ariadne store.


4 Tested FTP Clients
--------------------

These ftp clients were succesfully used with the Ariadne FTP server.

	- WebIFS / WebDrive								http://www.webifs.com

	With this tool you can map ftp sites to disk volumes onder Windows. Works 
	best under Windows 2000 and up.

	Note: after renaming Ariadne templates, without specifying the full
	detailed destination filename, WebIFS thinks the file does not
	exist. To enable the renamed file you have to refresh your folder listing.

	- WS_FTP										http://www.ipswitch.com

	Neat ftp client for Windows.

	- Ultra Edit									http://www.ultraedit.com

	Text and HTML editor for Windows with the ability to read and write
	files on FTP sites.

	- LFTP, NCFTP

	Unix / Linux ftp clients.
