<?php
    /******************************************************************
     winxp.browse.contextmenu.js                           Muze Ariadne
     ------------------------------------------------------------------
     Author: Muze (info@muze.nl)
     Date: 11 december 2002

     Copyright 2002 Muze

     This file is part of Ariadne.

     Ariadne is free software; you can redistribute it and/or modify
     it under the terms of the GNU General Public License as published 
     by the Free Software Foundation; either version 2 of the License, 
     or (at your option) any later version.
 
     Ariadne is distributed in the hope that it will be useful,
     but WITHOUT ANY WARRANTY; without even the implied warranty of
     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
     GNU General Public License for more details.

     You should have received a copy of the GNU General Public License
     along with Ariadne; if not, write to the Free Software 
     Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  
     02111-1307  USA

    -------------------------------------------------------------------

     Description:

	Javascript functions for the WinXP context menus.

    ******************************************************************/

  	include_once($this->store->code."nls/winxp.".$this->reqnls);
	
	if ($this->CheckLogin("read") && $this->CheckConfig($arCallFunction, $arCallArgs)) {
		if (!$arLanguage) {
			$arLanguage=$nls;
		}
		if ($data->$arLanguage) {
			$nlsdata=$data->$arLanguage;
		}
?>
	function createContextMenus() {
		cm_createMenu("normalContextMenu", 150, 0);
		cm_createItem("normalCMexplore", "<strong><?php echo $ARnls["xp_explore"]; ?></strong>", "normalContextMenu", "javascript:top.View('%objectpath%')", "", false);
		cm_createItem("normalCMviewweb", "<?php echo $ARnls["xp_viewweb"]; ?>", "normalContextMenu", "javascript:arshow('_new', '%fullObjectpath%')", "", false);
  		cm_createSep("normalContextMenu");
		cm_createItem("normalCMcopy", "<?php echo $ARnls["xp_copy"]; ?>", "normalContextMenu", "javascript:arshow('object_fs','%fullObjectpath%object.copy.phtml')", "", false);
		cm_createItem("normalCMlink", "<?php echo $ARnls["xp_link"]; ?>", "normalContextMenu", "javascript:arshow('object_fs','%fullObjectpath%object.link.phtml')", "", false);
		cm_createItem("normalCMrename", "<?php echo $ARnls["xp_rename"]; ?>", "normalContextMenu", "javascript:arshow('object_fs','%fullObjectpath%object.rename.phtml')", "", false);
		cm_createItem("normalCMdelete", "<?php echo $ARnls["xp_delete"]; ?>", "normalContextMenu", "javascript:arshow('object_fs','%fullObjectpath%object.delete.phtml')", "", false);
  		cm_createSep("normalContextMenu");
		cm_createItem("normalCMedit", "<?php echo $ARnls["xp_edit"]; ?>", "normalContextMenu", "javascript:arshow('edit_object_data','%fullObjectpath%edit.object.data.phtml')", "", false);
		cm_createItem("normalCMshortcut", "<?php echo $ARnls["xp_shortcut"]; ?>", "normalContextMenu", "javascript:arshow('edit_object_shortcut','%fullObjectpath%edit.object.shortcut.phtml')", "", false);

		cm_createMenu("bodyContextMenu", 150, 0);
		cm_createItem("bodyCMview", "<?php echo $ARnls["xp_view"]; ?>", "bodyContextMenu", "", "", true);

		cm_createMenu("iconviewContextmenu", 150, 1);
		var vm_temp = top.Get('viewmode');
		var list_checked = icons_checked = details_checked = "";
		if (!vm_temp || vm_temp == 'list')
			list_checked = '<img class="itemimage" src="<?php echo $AR->dir->images; ?>winxp/checked.png" border="0" widh="16" height="16 alt="" border="0" vspace="0" hspace="0" />';
		if (vm_temp == 'icons')
			icons_checked = '<img class="itemimage" src="<?php echo $AR->dir->images; ?>winxp/checked.png" border="0" widh="16" height="16 alt="" border="0" vspace="0" hspace="0" />';
		if (vm_temp == 'details')
			details_checked = '<img class="itemimage" src="<?php echo $AR->dir->images; ?>winxp/checked.png" border="0" widh="16" height="16 alt="" border="0" vspace="0" hspace="0" />';
		
		cm_createItem("iconCMsmall", list_checked + "<?php echo $ARnls["xp_small"]; ?>", "iconviewContextmenu", "javascript:parent.setXpView('list')", "", false);
		cm_createItem("iconCMlarge", icons_checked + "<?php echo $ARnls["xp_large"]; ?>", "iconviewContextmenu", "javascript:parent.setXpView('icons')", "", false);
		cm_createItem("iconCMdetails", details_checked + "<?php echo $ARnls["xp_details"]; ?>", "iconviewContextmenu", "javascript:parent.setXpView('details')", "", false);

  		cm_linkSubMenu("bodyCMview", "iconviewContextmenu");
		
	<?php
	if (!$arReturnTemplate) {
		$arReturnTemplate="object.new.phtml";
	}
	if ($AR->user->data->login=="admin") {
		$result=$this->ls("/system/ariadne/types/","system.get.phtml");
	} else {
		$configcache=$ARConfig->cache[$this->path];
		if ($configcache->typetree) {
			$result=$this->ls($configcache->typetree."/".$this->type."/",
				"system.get.phtml");
		} else {
			$result=$this->ls("/system/ariadne/typetree/normal/".$this->type."/",
				"system.get.phtml");
		}
	}
	if ($result && is_array($result)) {
		?>
  		cm_createSep("bodyContextMenu");
		cm_createItem("bodyCMnew", "<?php echo $ARnls["xp_new"]; ?>", "bodyContextMenu", "", "", true);

		cm_createMenu("newObjectContextmenu", 150, 1);
  		cm_linkSubMenu("bodyCMnew", "newObjectContextmenu");
		
		<?php
		while (list($key, $object)=each($result)) {
			$type=$object->data->value;
			$name=$object->nlsdata->name;
			if ($this->CheckSilent("add",$type))
				echo 'cm_createItem("bodyCMnew", '.
						'\'<img class="itemimage" width="20" height="20" src="'.$AR->dir->images."icons/".$type.'.gif" alt="'.$type.'" border="0" />'.
						$name.'\', "newObjectContextmenu", "javascript:arshow(\'object_new\', \''.$arReturnTemplate.'?arNewType='.RawUrlEncode($type).'&'.ldGetServerVar("QUERY_STRING").'\')", "", false);'."\n";
		}
	}
	echo "}";
}
?>