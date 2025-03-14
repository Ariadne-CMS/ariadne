<?php
	$ARCurrent->nolangcheck=true;
	// FIXME: make strict
	if ($this->CheckSilent("read") && $this->CheckConfig()) {
		$arLanguage=$this->getdata("arLanguage","none");
		if (!$arLanguage) {
		 $arLanguage=$ARConfig->nls->default;
		}
		$selectednls=$arLanguage;
		$selectedlanguage=$ARConfig->nls->list[$arLanguage];
		$flagsrc=$AR->dir->images."nls/small/$selectednls.gif";
		$flag="<img src=\"$flagsrc\" alt=\"$selectedlanguage\">";
		$any=$AR->dir->images."nls/small/any.gif";
?>
<script>

	CustomList=new Array();
	CustomData=new Array();
	CustomData['<?php echo $selectednls; ?>']=new Array;
	CustomData['none']=new Array;
	current_node=0;
	Started=false;

	function init() {
		dest=document.wgWizForm.fieldname.options;
		<?php
			$custom=$this->getdata("custom","none");
			if (is_array($custom)) {
				foreach( $custom as $cdnls => $cdvalues ) {
					echo "	CustomData['".AddCSlashes($cdnls, ARESCAPE)."']=new Array();\n";
					foreach( $cdvalues as $cdname => $cdvalue ) {
						// Note: cdvalue doesn't need a further "Ariadne level" backslashing
						// However, EOF chars need to be turned in "\n" inside the string
						//    echo "  CustomData['".AddCSlashes($cdnls, ARESCAPE)."']['".AddCSlashes($cdname, ARESCAPE)."']='".AddCSlashes($cdvalue, ARESCAPE)."';\n";

						if (!is_array($cdvalue) && !is_object($cdvalue)) { // Don't show customdata with objects/arrays, the dialog cannot handle them.
							echo "  CustomData['".AddCSlashes($cdnls, ARESCAPE)."']['".AddCSlashes($cdname, ARESCAPE)."']='".str_replace('</script>', '<\\/script>', str_replace("\r\n", "\\n", AddCSlashes($cdvalue, ARESCAPE)))."';\n";
						}
					}
				}
			} else {
				$custom = array();
			}

			if( $this->arIsNewObject ) {
				$configcache=$ARConfig->cache[$this->parent];
			} else {
				$configcache=$ARConfig->cache[$this->path];
			}

			if (is_array($configcache->custom??null)) {
				foreach($configcache->custom as $name => $customvalue) {
					if (!$customvalue["grant"] || ($this->CheckSilent($customvalue["grant"]))) {
						if (($customvalue["type"]==$this->type) ||
								($customvalue["inherit"] &&
								($this->AR_implements($customvalue["type"]))
								)
							) {
							if ($customvalue["nls"]=="true") {
								$showdata=$custom[$selectednls][$name];
							} else {
								$showdata=$custom['none'][$name];
							}
							echo "		dest[dest.length]=new Option('".AddCSlashes($name, ARESCAPE)."','".AddCSlashes($name, ARESCAPE)."');\n";
							echo "		CustomList[CustomList.length]=new custom_node(\n";
							echo "			'".AddCSlashes($customvalue["type"], ARESCAPE)."',";
							if ($customvalue["inherit"] && ($customvalue["inherit"]!="false")) {
								echo 1;
							} else {
								echo 0;
							}
							echo ",\n";
							echo "			'".AddCSlashes($name, ARESCAPE)."','".AddCSlashes($customvalue["size"], ARESCAPE)."',\n";
							echo "			'".AddCSlashes($customvalue["grant"], ARESCAPE)."',";
							if ($customvalue["nls"] && ($customvalue["nls"]!="false")) {
								echo 1;
							} else {
								echo 0;
							}
							echo ",\n";
							echo "			'".AddCSlashes($customvalue["check"], ARESCAPE)."');\n";
						}
					}
				}
			}
		?>
		if (document.wgWizForm['fieldname'].options.length>0) {
			document.wgWizForm['fieldname'].options[0].selected=true;
			show();
		}
		document.wgWizForm['fieldname'].focus();
	}

	function save() {
		if (message=updateCustom(document.wgWizForm.showcustomdata)) {
			alert(message);
			return false;
		}  else {
			savestring='';
			for (nls in CustomData) {
				arData=CustomData[nls];
				for (arName in arData) {
					var value = encodeURIComponent(arData[arName]);
					// Some characters has to be replaced by its encoding
					// Plus (+) char (which encodeURIComponent() doesn't do)
					value = value.replace(/\+/g, "%2B");
					savestring+='&custom['+encodeURIComponent(nls)+']['+encodeURIComponent(arName)+']='+value;
				}

			}
			document.wgWizForm.custom.value=savestring;
			return true;
		}

	}

	function show() {
		form=document.wgWizForm;
		if (form.fieldname.options.length>0) {
			if (Started && (message=updateCustom(form.showcustomdata))) {
				alert(message);
				form.fieldname.selectedIndex=current_node;
				form.showcustomdata.focus();
			} else {
				id=form.fieldname.selectedIndex;
				current_node=id;
				fielddata=CustomList[id];
				if (fielddata.nls) {
					nls='<?php echo $selectednls; ?>';
					document.flagimage.src='<?php echo $flagsrc; ?>';
				} else {
					nls='none';
					document.flagimage.src='<?php echo $any; ?>';
				}
				if (CustomData[nls] && CustomData[nls][fielddata.name]) {
					newvalue=CustomData[nls][fielddata.name];
				} else {
					newvalue='';
				}
				form.showcustomdata.value=newvalue;
			}
		}
		Started=true;
	}

	function custom_node(type, inherit, name, size, grant, nls, check) {
		this.name=name;
		this.type=type;
		this.inherit=inherit;
		this.size=size;
		this.grant=grant;
		this.nls=nls;
		this.check=check;
	}

	function updateCustom(element, byMouseDown) {
		result=false;
		fields=document.wgWizForm.fieldname;
		id=current_node; //fields.selectedIndex;
		if (fielddata=CustomList[id]) {
			data=new String(element.value);
			returnfocus=false;
			if (data.length>fielddata.size) {
				result=fielddata.name+': <?php echo $ARnls["err:maxsize"]; ?> '+data.length+' ('+fielddata.size+')';
			} else if (fielddata.check) {
				if (!data.match(fielddata.check)) {
					result=fielddata.name+': <?php echo $ARnls["err:invalidcontent"]; ?>';
				} else if (fielddata.nls) {
					CustomData['<?php echo $selectednls; ?>'][fielddata.name]=data;
				} else {
					CustomData['none'][fielddata.name]=data;
				}
			}
		} else {
			alert('no fielddata: '+id);
		}
		return result;
	}

</script>
<style>
.nameselect {
	width: 90%;
}
</style>
<input type="hidden" name="custom">
<table border="0" width="100%">
<tr>
	<td>
		<fieldset>
		<legend><?php echo $ARnls["customdata"]; ?></legend><img src="<?php echo $AR->dir->images; ?>dot.gif" alt="" width="1" height="1"><br>
			<table border="0" width="100%" align="center" cellspacing="4" vspace="10" hspace="10">
			<tr>
				<td valign="top">
					<img name="flagimage" src="<?php echo $any; ?>" alt="" align="right">
					<select name="fieldname" class="nameselect" onChange="show();">
					</select>
				</td>
			</tr><tr>
				<td valign="top">
					<textarea name="showcustomdata" class="inputbox"></textarea>
				</td>
			</tr>
		</table>
		</fieldset>
	</td>
</tr>
</table>
<script>
	document.wgWizForm.wgWizSubmitHandler=save;
	window.onload=init;
</script>
<?php
	}
?>
