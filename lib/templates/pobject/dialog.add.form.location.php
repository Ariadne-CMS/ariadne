<?php
	if ($this->CheckLogin("read") && $this->CheckConfig()) {
		echo '<fieldset id="location">';
		echo '<legend>' . $ARnls['ariadne:new:location'] . '</legend>';
		$fields = array(
			"location" => array(
				"type" => "radio",
				"options" => array(
					$this->path => $ARnls['ariadne:new:below'],
					$this->parent => $ARnls['ariadne:new:beside']
				),
				'class' => 'field',
				'value'=> $this->getvar( 'location' ),
				'label' => false
			)
		);
		$snippit = ar('html')->form($fields, false)->getHTML()->childNodes;
		foreach ($snippit->getElementsByTagName("input") as $radioOption) {
			$radioOption->setAttribute("onclick", "this.form.submit();");
		}
		echo $snippit;
		echo '</fieldset>';
	}
?>
