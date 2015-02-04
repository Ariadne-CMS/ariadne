<?php
    /******************************************************************
     mod_html.php                                          Muze Ariadne
     ------------------------------------------------------------------
     Author: Muze (info@muze.nl)
     Date: 26 november 2002

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

	   This module includes a number of usefull HTML related tools,
	   e.g. a method to use htmltidy to clean/rewrite HTML, a word clean
	   method, etc.
	   This module calls the html tidy executable with the given
	   options and returns 'clean' html.

    ******************************************************************/


	class html {


		function tidy($html, $config)
		{
			global $AR;
			require_once($AR->dir->install."/lib/modules/mod_tidy.php");

			return tidy::clean($html, $config);
		}


		function clean($html, $rules) {
			global $AR;
			require_once($AR->dir->install."/lib/modules/mod_htmlcleaner.php");

			return htmlcleaner::clean($html, $rules);
		}

		function cleanmsword($html) {
			/*
				rewrite : array with rewrite/remove rules
				preserve : array with exeptions on the rewrite rules
				rewrite : tag : attribute : value match = new value or false (remove)
			*/
			$rules = array();
			$rules['rewrite']['.*']['class']['mso.*']=false;	// class="msoNormal" etc
			$rules['rewrite']['o:.*']=false;					// <o:p style=".."></o>
			$rules['rewrite']['.*']['style']=false;				// style="..."
			$rules['rewrite']['font']=false;					// font tags begone
			$rules['rewrite']['.*']['v:.*']=false;				// v:shape="..."
			return html::clean($html, $rules);
		}

	}
