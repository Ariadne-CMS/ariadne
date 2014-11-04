<?php

class pinp_multipart {

	function _CutPage($tag, $withtags=true, $withtitles=true, $page=false) {
		return multipart::CutPage($tag, $withtags, $withtitles, $page);
	}

	function _ShowSection($section, $template="", $args="", $recurse=false, $level=0) {
		return multipart::ShowSection($section, $template, $args, $recurse, $level);
	}

	function _ShowSections($sections, $template="", $args="", $recurse=false, $level=0) {
		return multipart::ShowSections($sections, $template, $args, $recurse, $level);
	}

}

class multipart {

	function CutPage($tag='h1', $withtags=true, $withtitles=true, $page=false) {
		$context = pobject::getContext();
		$me = $context["arCurrentObject"];
		$sections=Array();
		if ($withtitles) {
			$regexp='/(<'.$tag.'[^>]*>(.*)<\/'.$tag.'[^>]*>)/Usi';
		} else {
			$regexp='/(<('.$tag.')[^>]*>)/Usi';
		}
		if (!$page) {
			$page=$me->GetPage();
		}
		if ($matches=preg_split($regexp, " ".$page, -1, PREG_SPLIT_DELIM_CAPTURE)) {
			$leader=trim($matches[0]);
			if ($leader) {
				$sections[0]['content']=$leader;
			}
			$start=1;
			$max=count($matches)-1;
			$count=$start;
			for ($i=$start; $i<$max; $i+=3) {
				$split=$matches[$i];
				if ($withtitles) {
					$sections[$count]['title']=$matches[$i+1];
				}
				if ($withtags) {
					$sections[$count]['content']=$split.$matches[$i+2];
				} else {
					$sections[$count]['content']=$matches[$i+2];
				}
				$count++;
			}
		}
		return $sections;
	}

	function ShowSection($section, $template="", $args="", $recurse=false, $level=0) {
		$context = pobject::getContext();
		$me = $context["arCurrentObject"];
		if ($recurse && is_array($section['content'])) {
			multipart::ShowSections($section['content'], $template, $args, $recurse, $level++);
		} else {
			if (!$template) {
				echo "<div class='section'><div class='title'>".$section['title']."</div>";
				echo $section['content']."</div>";
			} else {
				$me->call($template, array_merge($args, Array('level' => $level, 'section_title' => $section['title'], 'section_content' => $section['content'])));
			}
		}
	}

	function ShowSections($sections, $template="", $args="", $recurse=false, $level=0) {
		$context = pobject::getContext();
		$me = $context["arCurrentObject"];
		if (is_array($sections)) {
			$max=count($sections);
			for ($i=0; $i<$max; $i++) {
				multipart::ShowSection($sections[$i], $template, $args, $recurse, $level);
				$me->_resetloopcheck();
			}
		}
	}

}
