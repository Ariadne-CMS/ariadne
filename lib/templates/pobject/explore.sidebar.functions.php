<?php
	if( !function_exists("buildhtml") ) {
		function buildhtml($settings) {
			$result = '';

			if(is_array($settings)) {
				foreach ($settings as $item) {
					$result .= '<a class="sidebar_task';
					if ($item['class']) {
						$result .= ' ' . $item['class'];
					}
					$result .= '"';

					if ($item['href']) {
						$result .= ' href="' . $item['href'] . '"';
					}
					if ($item['onclick']) {
						$result .= ' onclick="' . $item['onclick'] . '"';
					}
					if ($item['target']) {
						$result .= ' target="' . $item['target'] . '"';
					} else {
						// FIXME: This should not be used in strict doctype, but we don't want it in our
						// window either.

						// $result .= ' target="_blank"'; 
					}

					$result .= '>';

					if ($item['icon']) {
						$result .= '<img class="task_icon" src="' . $item['icon'] . '" alt="' . $item['iconalt'] . '" title="' . $item['iconalt'] . '">&nbsp;&nbsp;';
					}

					$itemlabel = $item['nlslabel'];
					$maxlabellength = 25;
					if (mb_strlen($itemlabel, "utf-8") > $maxlabellength) {
						$origName = $itemlabel;
						$itemlabel = "<span title=\"$origName\">" . mb_substr($itemlabel, 0, $maxlabellength-3, "utf-8")."..."."</span>";
					}
					$result .= $itemlabel;
					$result .= "</a>";
				}
			}
			return $result;
		}
	}
	
	if( !function_exists("showSection") ) {
		function showSection($section) {
			$invisibleSections= $_COOKIE['invisibleSections'];

			$maxheadlength = 18;
			if ($section['icon']) {
				$maxheadlength = 14;
			}

			$sectionDisplayName = $section['label'];
			$sectionName = $section['id'];
			$icon = $section['icon'];

			if (mb_strlen($sectionDisplayName, "utf-8") > $maxheadlength) {
				$origName = htmlspecialchars($sectionDisplayName);
				$sectionDisplayName = "<span title=\"$origName\">".htmlspecialchars(mb_substr($sectionDisplayName, 0, $maxheadlength-3, "utf-8")."...")."</span>";
			} else {
				$sectionDisplayName = htmlspecialchars($sectionDisplayName);
			}
			$icontag = "";
			if ($icon) {
				$icontag .= '<img src="' . $icon . '" class="icon" alt="' . $section['iconalt'] . '" title="' . $section['iconalt'] . '">';
				if( $section['overlay_icon']  ) {
					$icontag .= '<img src="' . $section['overlay_icon'] . '" class="overlay_icon" alt="' . $section['overlay_iconalt'] . '" title="' . $section['overlay_iconalt'] . '">';
				}
			}

			if (strstr(strtolower($invisibleSections), $sectionName . ";")) {
				$section_class = " collapsed";
			} else {
				$section_class = " expanded";
			}

			$sectionhead_class = "";
			if ($icon) {
				$sectionhead_class .= " iconsection";
			}

			if( $section['inline_icon'] ) {
				$sectionhead_class .= " iconinlinesection";
				$icontag .= '<img src="' . $section['inline_icon'] . '" class="inline_icon" alt="' . $section['inline_iconalt'] . '" title="' . $section['inline_iconalt'] . '">';
			}

			$togglehref = "javascript:muze.ariadne.explore.sidebar.section.toggle('" . $sectionName . "');";

			$result = '';

			$result .= '<div class="section' . $section_class . '">';
			$result .= $icontag;

			$result .= '<div class="sectionhead yuimenubar' . $sectionhead_class . '">';
			$result .= '<a href="' . $togglehref . '">' . $sectionDisplayName . '</a>';
			$result .= '<a class="toggle" href="' . $togglehref . '">&nbsp;</a>';
			$result .= '</div>';

			$result .= '<div class="sectionbody" id="' . $sectionName . '_body">';

			$result .= '<div class="section_content">';

			if ($section['details']) {
				$result .= '<div class="details">';
				$result .= $section['details'];
				$result .= '</div>';
			}

			$result .= buildhtml($section['tasks']);
			$result .= '</div>';

			$result .= '</div>';
			$result .= '</div>';
			return $result;
		}
	}
?>