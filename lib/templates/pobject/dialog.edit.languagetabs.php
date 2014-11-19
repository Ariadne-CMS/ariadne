<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckSilent("read") && $this->CheckConfig()) {

		$arLanguage=$this->getdata('arLanguage');
		if (!$AR->user->data->languagelist) {
			// if no languages are selected in the user preferences, use them all...
			// this makes it backwards compatible
			$languagelist=$AR->nls->list;
		} else {
			$languagelist=$AR->user->data->languagelist;
		}
		$configcache=$ARConfig->cache[$this->path];
		$nlsconfig=$configcache->nls;
		// only set arLanguage if we don't have it or we're not a new object and lack nlsconfig->list[arLanguage]
		// new objects don't have a configcache hence the newobject check
		if (!$arLanguage || (!$this->arIsNewObject && !$nlsconfig->list[$arLanguage] ) ) {
			// make sure the selected language makes sense
			$arLanguage = $this->data->nls->default;
			if( !$arLanguage ) { // new object has no data->nls->default
				$arLanguage = $AR->nls->default;
			}
			$ARCurrent->arLanguage=$arLanguage;
		}
		if (!$languagelist[$arLanguage]) {
			// make sure the currently selected language is also available at all times
			$languagelist[$arLanguage]=$AR->nls->list[$arLanguage];
		}

		$languages=array_intersect(array_keys($languagelist), array_keys(
				array_merge( ( $ARConfig->nls->list ? $ARConfig->nls->list : array() ),
							 ( $this->data->config->nls->list ? $this->data->config->nls->list : array() )
							)
						)
				);
		$count=count($languages);

		$itemlist = array();
		foreach( $languages as $key => $nlskey ) {
			$language=$AR->nls->list[$nlskey];
			if (($this->data->config->nls->list[$nlskey]) || ($this->getdata('name',$nlskey))) {
				$image=$AR->dir->images."nls/small/".$nlskey.".gif";
			} else {
				$image=$AR->dir->images."nls/small/faded/".$nlskey.".gif";
			}
			if ($nlskey==$arLanguage) {
				$class="tab-selected";
				$image=$AR->dir->images."nls/small/".$nlskey.".gif";
			} else {
				$class="tab";
			}

			$item['class'] = $class;
			$item['href']  = "javascript:switchLanguage('" . $nlskey . "');";
			$item['image'] = $image;
			$item['language'] = $language;

			if (($count < 10) && ($count > 4)) {
				$item['body'] = "&nbsp;" . $nlskey;
			} elseif ($count <=4) {
				$item['body'] = "&nbsp;" . $language;
			} else {
				$item['body'] = '';
			}
			$itemlist[] = $item;
		}
?>
	<?php if ($itemlist) { ?>
		<ul id="tabnav">
			<?php foreach ($itemlist as $item) { ?>
				<li class="<?php echo $item['class']; ?>">
					<a href="<?php echo $item['href']; ?>" title="<?php echo $item['language']; ?>">
						<img src="<?php echo $item['image']; ?>" alt="<?php echo $item['language']; ?>">
						<?php echo $item['body']; ?>
					</a>
				</li>
			<?php } ?>
		</ul>
	<?php } ?>
<?php
	}
?>
