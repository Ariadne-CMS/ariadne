<?php
	if ($this->CheckLogin("layout") && $this->CheckConfig()) {
?>
<style type="text/css">
	.no_warning {
		display: none;
	}

	.warning {
		border: 1px solid #808080;
		background-color: yellow;
		width: 100%;
		height: 50px;
		padding: 5px;
	}
</style>

<script type="text/javascript">
	customlist=new Array();
	current_node=0;

	function custom_init() {
		dest=document.getElementById("nameselect").options;
		<?php
			$this->call('typetree.ini');
			$typelist = $ARCurrent->arTypeNames;

			if (is_array($this->data->config->customconfig)) {
				foreach( $this->data->config->customconfig as $name => $custom ) {
					if (!$custom['nls']) {
						$custom['nls']=0;
					} else {
						$custom['nls']=1;
					}
					if (!$custom['inherit']) {
						$custom['inherit']=0;
					} else {
						$custom['inherit']=1;
					}
					if (!$custom['property']) {
						$custom['property']=0;
					} else {
						$custom['property']=1;
					}

					echo "		dest[dest.length]=new Option('".AddCSlashes($name, ARESCAPE)."','".AddCSlashes($name, ARESCAPE)."');\n";
					echo "		customlist[customlist.length]=new custom_node(\n";
					echo "			'".AddCSlashes($custom["type"], ARESCAPE)."',".AddCSlashes($custom["inherit"], ARESCAPE).",\n";
					echo "			'".AddCSlashes($name, ARESCAPE)."','".AddCSlashes($custom["size"], ARESCAPE)."',\n";
					echo "			'".AddCSlashes($custom["grant"], ARESCAPE)."',".AddCSlashes($custom["nls"], ARESCAPE).",\n";
					echo "			'".AddCSlashes($custom["check"], ARESCAPE)."',".AddCSlashes($custom["property"], ARESCAPE).",".AddCSlashes((int)$custom["containsHTML"], ARESCAPE).");\n";

					if (!$typelist[$custom["type"]]) {
						// If the type is not in the list, add it.
						$typelist[$custom["type"]] = $custom["type"];
					}
				}
			}
		?>
		if (dest.length==0) {
			add();
		}
		document.getElementById("nameselect").selectedIndex=dest.length-1;
		document.getElementById("nameselect").focus();
		show();
	}

	function remove() {
		dest=document.getElementById("nameselect");
		id=dest.selectedIndex;
		dest.options[id]=null;
		ii=0;
		newlist=new Array();
		for (i=0; i<customlist.length; i++) {
			if (i!=id) {
				newlist[ii]=customlist[i];
				ii++;
			}
		}
		customlist=newlist;
		if (!dest.options[id]) {
			id-=1;
		}
		if (id>=0) {
			dest.options[id].selected=true;
			show();
		} else {
			for (i=0; i<document.getElementById("cdtype").options.length; i++) {
				if (document.getElementById("cdtype").options[i].value=='pobject') {
					document.getElementById("cdtype").options[i].selected=true;
					i=document.getElementById("cdtype").options.length;
				}
			}
			document.getElementById("cdname").value='';
			document.getElementById("cdsize").value='255';
			document.getElementById("cdgrant").value='edit';
			document.getElementById("cdcheck").value='.*';
			document.getElementById("cdinherit").checked=true;
			document.getElementById("cdnls").checked=true;
			document.getElementById("cdproperty").checked=false;
			document.getElementById("cdpcontainsHTML").checked=false;
		}
	}

	function add() {
		dest=document.getElementById("nameselect").options;
		if ((dest.length==0) || (dest[dest.length-1].value!='new')) {
			dest[dest.length]=new Option('<?php echo $ARnls["new"]; ?>...','new');
			customlist[customlist.length]=new custom_node(document.getElementById("cdtype").options[document.getElementById("cdtype").selectedIndex].value, true, '', 255, 'edit', true, '.*');
		}
		document.getElementById("nameselect").selectedIndex=dest.length-1;
		document.getElementById("nameselect").focus();
		show();
	}

	function save() {
		savestring='';
		for (i=0; i<customlist.length; i++) {
			data=customlist[i];
			savestring+='&customfields['+i+'][name]='+escape(data.name);
			savestring+='&customfields['+i+'][type]='+escape(data.type);
			savestring+='&customfields['+i+'][inherit]='+escape(data.inherit);
			savestring+='&customfields['+i+'][size]='+escape(data.size);
			savestring+='&customfields['+i+'][nls]='+escape(data.nls);
			savestring+='&customfields['+i+'][grant]='+escape(data.grant);
			savestring+='&customfields['+i+'][check]='+escape(data.check);
			savestring+='&customfields['+i+'][property]='+escape(data.property);
			savestring+='&customfields['+i+'][containsHTML]='+escape(data.containsHTML);
		}
		document.getElementById("customfields").value=savestring;
		return true;
	}

	function show() {
		if (document.getElementById("nameselect").options.length>0) {
			id=document.getElementById("nameselect").selectedIndex;
			fielddata=customlist[id];
			for (i=0; i<document.getElementById("cdtype").options.length; i++) {
				if (document.getElementById("cdtype").options[i].value==fielddata.type) {
					document.getElementById("cdtype").options[i].selected=true;
					i=document.getElementById("cdtype").options.length;
				}
			}
			document.getElementById("cdname").value=fielddata.name;
			document.getElementById("cdsize").value=fielddata.size;
			document.getElementById("cdgrant").value=fielddata.grant;
			document.getElementById("cdcheck").value=fielddata.check;
			document.getElementById("cdinherit").checked=fielddata.inherit;
			document.getElementById("cdnls").checked=fielddata.nls;
			document.getElementById("cdproperty").checked=fielddata.property;
			document.getElementById("cdcontainsHTML").checked=fielddata.containsHTML;
		}
	}

	function custom_node(type, inherit, name, size, grant, nls, check, property, containsHTML) {
		this.name=name;
		this.type=type;
		this.inherit=inherit;
		this.size=size;
		this.grant=grant;
		this.nls=nls;
		this.check=check;
		this.property=property;
		this.containsHTML=containsHTML;
	}

	function setWarning(message) {
		warningelm = document.getElementById("warning");

		if (message) {
			warningelm.innerHTML = message;
			warningelm.className = 'warning';
		} else {
			warningelm.innerHTML = '';
			warningelm.className = 'no_warning';
		}
	}

	function updateCustom(element) {
		fields=document.getElementById("nameselect");
		id=fields.selectedIndex;
		skip=false;
		switch(element.name) {
			case "cdname" :
				value=new String(element.value);
				while (value.substr(0,1)==' ') {
					value=value.substr(1);
				}
				while (value.substr(value.length-1,1)==' ') {
					value=value.substr(0,value.length-1);
				}
				if (value=='') {
					setWarning('<?php echo $ARnls['err:nocustomdataname']; ?>');
					skip=true;
				}
				element.value=value;
				for (i=0; i<fields.options.length; i++) {
					if ((fields.options[i].value==value) && (i!=id)) {
						setWarning(value+': <?php echo $ARnls['err:customdatanameinuse']; ?>');
						skip=true;
						i=fields.options.length;
					}
				}
				if (!skip) {
					setWarning(false);
					customlist[id].name=value;
					fields.options[id]=new Option(value, value);
					fields.options[id].selected=true;
				} else {
					element.select();
					element.focus();
				}
				break;
			case "cdtype" :
				customlist[id].type=element.options[element.selectedIndex].value;
				break;
			case "cdinherit" :
				if (element.checked) {
					value=1;
				} else {
					value=0;
				}
				customlist[id].inherit=value;
				break;
			case "cdsize" :
				customlist[id].size=element.value;
				break;
			case "cdgrant" :
				customlist[id].grant=element.value;
				break;
			case "cdnls" :
				if (element.checked) {
					value=1;
				} else {
					value=0;
				}
				customlist[id].nls=value;
				break;
			case "cdcontainsHTML" :
				if (element.checked) {
					value=1;
				} else {
					value=0;
				}
				customlist[id].containsHTML=value;
				break;
			case "cdcheck" :
				customlist[id].check=element.value;
				break;
			case "cdproperty" :
				if (element.checked) {
					value=1;
				} else {
					value=0;
				}
				customlist[id].property=value;
				break;
		}
	}

	function attach_events() {
		document.getElementById("wgWizForm").onsubmit = save;
	}

	YAHOO.util.Event.onDOMReady(custom_init);
	YAHOO.util.Event.onDOMReady(attach_events);
</script>
<style type="text/css">
	.nameselect {
		width: 130px;
		margin-right: 10px;
		float: left;
	}
	#tabsdata div.left select, #tabsdata div.right select {
		width: auto;
	}

	#tabsdata input.text {
		display: inline;
	}
	#tabsdata label {
		display: block;
		float: left;
		width: 75px;
		margin-top: 5px;
	}
	#tabsdata div.left {
		width: auto;
	}
	#tabsdata input.button {
		display: inline;
	}
	#tabsdata div.right {
		width: 120px;
		float: right;
	}
	#tabsdata div.right label {
		display: inline;
		width: auto;
		float: none;
	}
	#tabsdata fieldset {
		padding-top: 0px;
		padding-bottom: 0px;
	}
	#tabsdata legend {
		margin-bottom: 5px;
	}
</style>
<!--<form name="customform" action="edit.object.custom.save.phtml" method="POST" onSubmit="save()">-->
<input type="hidden" name="customfields" id="customfields">
<input type="hidden" name="arReturnPage" value="<?php echo $arReturnPage; ?>">
	<select id="nameselect" name="fieldname" size="10" class="nameselect" onChange="show();">
	</select>
	<fieldset>
	<legend><?php echo $ARnls["customdata"]; ?></legend>
		<div class="left">
			<div class="field">
				<label for="cdtype"><?php echo $ARnls["type"]; ?></label>
				<select class="typeselect" id="cdtype" name="cdtype" onChange="updateCustom(this);" ><?php

				asort($typelist);
				foreach ($typelist as $typeValue => $typeName) {
					echo "<option value=\"$typeValue\">$typeName</option>\n";
				}

				?></select>
			</div>
			<div class="field">
				<label for="cdname"><?php echo $ARnls["name"]; ?></label>
				<input class="text" type="text" id="cdname" name="cdname" value="" onBlur="updateCustom(this);" >
			</div>
			<div class="field">
				<label for="cdsize"><?php echo $ARnls["size"]; ?></label>
				<input class="text" type="text" name="cdsize" id="cdsize" value="255" onBlur="updateCustom(this);" >
			</div>
			<div class="field">
				<label for="cdgrant"><?php echo $ARnls["grant"]; ?></label>
				<input class="text" type="text" id="cdgrant" name="cdgrant" value="" onBlur="updateCustom(this);" >
			</div>
			<div class="field">
				<label for="cdcheck"><?php echo $ARnls["check"]; ?></label>
				<input class="text" type="text" id="cdcheck" name="cdcheck" value=".*" onBlur="updateCustom(this);" >
			</div>
		</div>
		<div class="right">
			<div class="field checkbox">
				<input type="checkbox" id="cdinherit" name="cdinherit" value="1" onClick="updateCustom(this);" checked>
				<label for="cdinherit"><?php echo $ARnls["inherit"]; ?></label>
			</div>
			<div class="field checkbox">
				<input type="checkbox" id="cdnls" name="cdnls" value="1" onClick="updateCustom(this);" checked>
				<label for="cdnls">nls</label>
			</div>
			<div class="field checkbox">
				<input type="checkbox" id="cdproperty" name="cdproperty" value="1" onClick="updateCustom(this);">
				<label for="cdproperty">property</label>
			</div>
			<div class="field checkbox">
				<input type="checkbox" id="cdcontainsHTML" name="cdcontainsHTML" value="1" onClick="updateCustom(this);">
				<label for="cdproperty">contains HTML</label>
			</div>
			<div id="warning" class='no_warning'>
			</div>
		</div>
	</fieldset>
	<div class="buttons">
		<div class="left">
			<input class="button wgWizControl" type="button" name="action" value="<?php echo $ARnls["remove"]; ?>" onClick="remove();">
			<input class="button wgWizControl" type="button" name="action" value="<?php echo $ARnls["add"]; ?>" onClick="add();">
		</div>
	</div>
<!--	<input class="button" type="button" name="action" value="<?php echo $ARnls["cancel"]; ?>" onClick="window.close();">
	<input class="button" type="submit" name="action" value="<?php echo $ARnls["ok"]; ?>">
-->
<!--/form-->
<?php
	}
?>
