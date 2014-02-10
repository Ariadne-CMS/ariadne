(function() {
	var holder = document.getElementById('archildren'),
		tests = {
			filereader: typeof FileReader != 'undefined',
			dnd: 'draggable' in document.createElement('span'),
			formdata: !!window.FormData,
			progress: "upload" in new XMLHttpRequest
		}, 
		progress = document.getElementById('uploadprogress'),
		fileupload = document.getElementById('upload');

	function filesExist(files) {
		var formData = tests.formdata ? new FormData() : null;
		for (var i = 0; i < files.length; i++) {
			if (tests.formdata) formData.append('filenames[]', files[i].name);
		}
		formData.append("filecount", files.length);
		// now post a new XHR request
		if (tests.formdata) {
			var xhr = new XMLHttpRequest();
			xhr.open('POST', muze.ariadne.registry.get('store_root') + muze.ariadne.registry.get('path') + "mfu.exists.ajax", false); 
			var result = xhr.send(formData);
			if (xhr.responseText) {
				return JSON.parse(xhr.responseText);
			}
		}
		return false;
	}

	function handleFiles(files, overwrite) {
		var formData = tests.formdata ? new FormData() : null;
		YAHOO.util.Dom.addClass(document.getElementById("archildren"), "dropzone-uploading");
		for (var i = 0; i < files.length; i++) {
			if (tests.formdata) formData.append('file[]', files[i]);
		}
		formData.append("filecount", files.length);
		formData.append("overwrite", overwrite);

		// now post a new XHR request
		if (tests.formdata) {
			var xhr = new XMLHttpRequest();
			xhr.open('POST', muze.ariadne.registry.get('store_root') + muze.ariadne.registry.get('path') + "mfu.save.html"); 
			xhr.onload = function() {
				progress.value = progress.innerHTML = 100;
				YAHOO.util.Dom.removeClass(document.getElementById("archildren"), "dropzone-uploading");
				progress.style.display = "none";
				window.setTimeout(function() {
					muze.ariadne.explore.viewpane.view(muze.ariadne.explore.viewpane.path);
				}, 500);
			};

			if (tests.progress) {
				xhr.upload.onprogress = function (event) {
					if (event.lengthComputable) {
						var complete = (event.loaded / event.total * 100 | 0);
						progress.value = progress.innerHTML = complete;
					}
				}
			}

			xhr.send(formData);
		}
	}

	function readfiles(files) {
		var overwrite = false;
		var existingFiles = filesExist(files);

		if (!existingFiles) {
			handleFiles(files, true);
		}
		console.log(existingFiles);

		var message = existingFiles.join(", ");

		// Show confirm dialog;
		var simpleDialog = new YAHOO.widget.SimpleDialog(
			"simpledialog1", 
			{ width: "300px",
				fixedcenter: true,
				visible: false,
				draggable: false,
				close: true,
				text: message,
				icon: YAHOO.widget.SimpleDialog.ICON_HELP,
				constraintoviewport: true,
				buttons: [ 
					{ text:"Overwrite", handler:handleOverwrite, isDefault:true },
					{ text:"Upload as copy",  handler:handleCopy },
					{ text:"Cancel",  handler:handleCancel } 
				]
			}
		);
		simpleDialog.files = files;
		simpleDialog.setHeader("Overwrite existing files?");
	
		// Render the Dialog
		simpleDialog.render("viewpane");
		simpleDialog.show();
	}

	function handleOverwrite() {
		console.log("overwrite clicked");
		handleFiles(this.files, true);
		this.hide();
	}

	function handleCopy() {
		console.log("copy clicked");
		handleFiles(this.files, false);
		this.hide();
	}

	function handleCancel() {
		console.log("cancel clicked");
		this.hide();
	}

	function initdropzone() {
		holder = document.getElementById("archildren");
		fileupload = document.getElementById("upload");
		progress = document.getElementById("uploadprogress");
		if (holder) {
			YAHOO.util.Dom.addClass(holder, 'dropzone');

			if (tests.dnd) { 
				holder.ondragover = function () { YAHOO.util.Dom.addClass(this, 'dropzone-hover'); return false; };
			//	holder.ondragleave = function() { YAHOO.util.Dom.removeClass(this, 'dropzone-hover'); return false; };
				holder.ondragend = function () { YAHOO.util.Dom.removeClass(this, 'dropzone-hover'); return false; };
				holder.ondrop = function (e) {
					this.className = '';
					e.preventDefault();
					readfiles(e.dataTransfer.files);
				}
			}
			if (fileupload) {
				fileupload.style.display = "none";
				fileupload.onchange = function() {
					console.log(this.files);
					readfiles(this.files);
					this.outerHTML = this.outerHTML; // clear uploader;
				}
			}
		}
	}
	muze.event.attach(window, "load", function() {
		muze.event.attach(document.body, "viewpaneLoaded", initdropzone);
		initdropzone();
	});
}());