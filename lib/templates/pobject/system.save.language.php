<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("layout") && $this->CheckConfig()) {
		// first make sure that the object is clean (data can only be set via
		// the defined interface: $arCallArgs)
		$this->data=current($this->get(".","system.get.data.phtml"));

		unset($this->data->config->nlsconfig);
		$inherit=$this->getdata("inherit","none");
		if (!$inherit) {
			$default=$this->getdata("default","none");
			$available=$this->getdata("available","none");
			if (!$default) {
				$default=$AR->nls->default;
			}
			if (!$available) {
				$available[]=$default;
			}
			if ($this->data->nls && !$this->data->nls->list[$default]) {
				$this->error=sprintf($ARnls["err:nodatafordefaultlanguage"],$ARConfig->nls->list[$default]);
			} else if (!in_array($default, $available)) {
				$this->error = $ARnls['err:defaultlanguagenotavailable'];
			} else {
				$this->data->config->nlsconfig=new baseObject;
				$this->data->config->nlsconfig->default=$default;
				reset($available);
				while (list($key, $lang)=each($available)) {
					$this->data->config->nlsconfig->list[$lang]=$AR->nls->list[$lang];
				}
			}
		} else {
			unset($this->data->config->nlsconfig);
		}
		if (!$this->error) {
			$this->save();
			$this->ClearCache($this->path, false, true);
		}
	}
?>
