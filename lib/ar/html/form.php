<?php
	class ar_html_form extends arBase {
		// todo: check, getdata, gettext methods, required fields, regular expression checks, related fields
		// custom fields api, custom validation api, file upload field, captcha
		protected $fields, $buttons, $action, $method;
		
		function __construct($fields, $buttons=null, $action=null, $method="POST") {
			$this->fields = $this->parseFields($fields);
			if (!isset($buttons)) {
				$buttons = array('Ok', 'Cancel');
			}
			$this->buttons	= $this->parseButtons($buttons);
			$this->action	= $action;
			$this->method	= $method;
		}

		public function setName($name) {
			$this->name	= $name;
		}
		
		public function setClassName($className) {
			$this->class = $className;
		}
		
		public function setId($id) {
			$this->id = $id;
		}
		
		public function addField($value) {
			$this->fields[] = $this->parseField(0, $value);
		}

		public function addButton($value) {
			$this->buttons[] = $this->parseButton(0, $value);
		}
		
		public function setValue($name, $value) {
			$field = $this->findField($name);
			if ($field) {
				return $field->setValue($value);
			} else {
				return false; // FIXME exceptions gebruiken?
			}
		}

		public function __toString() {
			$content = '';
			$buttonContent = '';
			$attributes = array();

			if (isset($this->name)) {
				$attributes['id'] = $this->name;
			}
			if (isset($this->class)) {
				$attributes['class'] = $this->class;
			}
			if (isset($this->action)) {
				$attributes['action'] = $this->action;
			}
			if (isset($this->method)) {
				$attributes['method'] = $this->method;
			}
			if (is_array($this->fields)) {
				foreach ($this->fields as $key => $field) {
					$content .= $field;
				}
			}
			if ($this->buttons) {
				foreach ($this->buttons as $key => $button) {
					$buttonContent .= $button;
				}
				$content .= ar_html::tag('div', $buttonContent, array('class' => 'formButtons'));
			}
			return (string)ar_html::tag('form', $content, $attributes);
		}

		public function getValue($name) {
			$field = $this->findField($name);
			if ($field) {
				return $field->getValue();
			} else {
				return null;
			}
		}

		public function getValues() {
			$values = array();
			foreach ($this->fields as $key => $field) {
				$result = $field->getNameValue();
				$values = array_merge($values, $result);
			}
			return $values;			
		}
		
		public function findField($searchName) {
			foreach ($this->fields as $key => $field) {
				$name = $field->name;
				if (!$name) {
					$name = $key;
				}
				if ($searchName === $name) {
					return $field;
				} else if ($field->hasChildren) {
					$result = $field->findField($searchName);
					if ($result) {
						return $result;
					}
				}
			}
			return false;
		}
		
		public function parseField($key, $field) {
			if (is_array($field)) {
				$type	= isset($field['type']) ? $field['type'] : null;
				$name	= isset($field['name']) ? $field['name'] : null;
				$label	= isset($field['label']) ? $field['label'] : null;
			} else {
				$label	= $field;
			}
			if (!$type) {
				$type	= 'text';
			}
			if (!$name) {
				if (!is_numeric($key)) {
					$name = $key;
				} else {
					$name = $label;
				}
			}
			if (!$label) {
				$label	= $name;
			}
			if (!is_array($field)) {
				$field	= array();
			}
			$field = $this->getField( new arObject( array_merge( $field, array(
				'type'	=> $type,
				'name'	=> $name,
				'label'	=> $label
			) ) ) );
			return $field;
		}
		
		public function parseFields($fields) {
			if (is_array($fields)) {
				$newFields = array();
				foreach ($fields as $key => $field) {
					$newFields[$key] = $this->parseField($key, $field);
				}
			}
			return $newFields;
		}
		
		protected function parseButton($key, $button) {
			if (is_array($button)) {
				$type	= isset($button['type']) ? $button['type'] : null;
				$name	= isset($button['name']) ? $button['name'] : null;
				$value	= isset($button['value']) ? $button['value'] : null;
			} else {
				$value	= $button;
				$button	= array();
			}
			if (!isset($type)) {
				$type	= 'submit';
			}
			if (!isset($name)) {
				if (!is_numeric($key)) {
					$name = $key;
				} else {
					$name = 'button_'.$key;
				}
			}
			if (!isset($value)) {
				$value	= $name;
			}
			$button = $this->getButton( new arObject( array_merge( $button, array(
				'type'	=> $type,
				'name'	=> $name,
				'value'	=> $value
			) ) ) );
			return $button;
		}
		
		protected function parseButtons($buttons) {
			if (is_array($buttons)) {
				$newButtons = array();
				foreach ($buttons as $key => $button) {
					$newButtons[$key] = $this->parseButton($key, $button);
				}
			}
			return $newButtons;
		}
		
		protected function getButton($button) {
			$class = 'ar_html_formButton'.ucfirst($button->type);
			if (class_exists($class)) {
				return new $class($button, $this);
			} else {
				return new ar_html_formButton($button, $this);
			}
		}
		
		protected function getField($field) {
			$class	= 'ar_html_formInput'.ucfirst($field->type);
		
			if (class_exists($class)) {
				return new $class($field, $this);
			} else {
				return new ar_html_formInputMissing($field, $this);
			}
		}
		
	}
	
	class ar_html_formButton {
		public function __construct($button, $form) {
			$this->form 	= $form;
			$this->type	= isset($button->type) ? $button->type : null;
			$this->name	= isset($button->name) ? $button->name : null;
			$this->value	= isset($button->value) ? $button->value : null;
			$this->class	= isset($button->class) ? $button->class : null;
			$this->id	= isset($button->id) ? $button->id : null;
		}
		public function getButton($type=null, $name=null, $value=null, $class=null, $id=null, $extra=null) {
			$attributes = array();
			if (!isset($type)) {
				$type = $this->type;
			}
			if (!isset($name)) {
				$name = $this->name;
			}
			if (!isset($value)) {
				$value = $this->value;
			}
			if (!isset($class)) {
				$class = $this->class;
			}
			if (!isset($id)) {
				$id = $this->id;
			}
			$attributes = array(
				'type'	=> $type,
				'name'	=> $name,
				'value'	=> $value
			);
			if (isset($class)) {
				$attributes['class'] = $class;
			}
			$attributes['id'] = $id;
			if ($extra) {
				$attributes = array_merge($attributes, $extra);
			}
			return ar_html::tag('input', $attributes);
		}
		
		public function __toString() {
			return $this->getButton();
		}
	}

	class ar_html_formButtonImage extends ar_html_formButton {

		public function __construct($button, $form) {
			parent::__construct($button, $form);
			$this->src = isset($button->src) ? $button->src : null;
		}
		
		public function getButton($type=null, $name=null, $value=null, $class=null, $id=null, $src=null, $extra=null) {
			if (!isset($src)) {
				$src = $this->src;
			}
			return parent::getButton($type, $name, $value, $class, $id, array_merge($extra, array('src' => $src)));
		}	
	}
	
	class ar_html_formInput {

		public function __construct($field, $form) {
			$this->form	= $form;
			$this->type	= isset($field->type) ? $field->type : null;
			$this->name	= isset($field->name) ? $field->name : null;
			$this->class	= isset($field->class) ? $field->class : null;
			$this->id	= isset($field->id) ? $field->id : null;
			$this->label	= isset($field->label) ? $field->label : null;
			$this->disabled	= isset($field->disabled) ? $field->disabled : false;
			$this->default	= isset($field->default) ? $field->default : null; 
			if (isset($field->value)) {
				$this->value = $field->value;
			} else {
				$value = ar()->http->getvar($this->name);
				if (isset($value)) {
					$this->value = $value;
				} else if (isset($this->default)) {
					$this->value = $this->default;					
				} else {
					$this->value = null;
				}
			}
		}

		protected function getLabel($label=null, $id='', $attributes=null) {
			if (!isset($attributes)) {
				$attributes = array();
			}
			if (!isset($label)) {
				$label = $this->label;
			}
			if ($id) {
				$attributes['for'] = $id;
			}
			return ar_html::tag('label', $label, $attributes);
		}

		protected function getInput($type=null, $name=null, $value=null, $disabled=null, $id=null) {
			if (!isset($type)) {
				$type = $this->type;
			}
			if (!isset($name)) {
				$name = $this->name;
			}
			if (!isset($value)) {
				$value = $this->value;
			}
			if (!isset($id)) {
				$id = $name; //this->id is for the field div, not the input tag
			}
			if (!isset($disabled)) {
				$disabled = $this->disabled;
			}
			$attributes = array(
				'type'	=> $type,
				'name'	=> $name,
				'id'	=> $id,
				'value'	=> $value
			);
			$content = ar_html::nodes();
			if ($disabled) {
				$attributes['disabled'] = true;
				$content[] = ar_html::tag('input', array('type' => 'hidden', 'name' => $name, 'value' => $value));
			}
			$content[] = ar_html::tag('input', $attributes);
			return $content;
		}

		public function getValue() {
			return $this->value;
		}

		public function setValue($value) {
			$this->value = $value;
			return true;
		}
		
		public function getNameValue() {
			return array( $this->name => $this->getValue() );
		}
		
		public function getField($content=null) {
			if (!isset($content)) {
				$content = ar_html::nodes($this->getLabel(), $this->getInput());
			}
			$class = array('field', $this->type);
			if ($this->class) {
				$class[] = $this->class;
			}
			$attributes = array('class' => $class);
			if ($this->id) {
				$attributes['id'] = $id;
			}
			return ar_html::tag('div', $content, $attributes);
		}
		
		public function __toString() {
			return (string)$this->getField();
		}
		
	}
	
	class ar_html_formInputMissing extends ar_html_formInput {

		public function __toString() {
			return (string)$this->getField("<strong>Erro: Field type ".$this->type." doesn not exist.</strong>");
		}
		
	}
	
	class ar_html_formInputText extends ar_html_formInput {
		
	}

	class ar_html_formInputPassword extends ar_html_formInputText {
		
	}
	
	class ar_html_formInputHidden extends ar_html_formInput {
			
		public function __construct($field, $form) {
			parent::__construct($field, $form);
			$this->disabled = false;
		}
		
		public function __toString() {
			return (string)$this->getField($this->getInput());
		}
	}
	
	class ar_html_formInputTextarea extends ar_html_formInputText {

		protected function getInput($type=null, $name=null, $value=null, $disabled=null, $id=null) {
			if (!isset($name)) {
				$name = $this->name;
			}
			if (!isset($value)) {
				$value = $this->value;
			}
			if (!isset($id)) {
				$id = $name; 
			}
			if (!isset($disabled)) {
				$disabled = $this->disabled;
			}
			$attributes = array(
				'name'	=> $name,
				'id'	=> $id
			);
			if ($disabled) {
				$attributes['disabled'] = true;
			}
			return ar_html::tag('textarea', $value, $attributes);
		}
		
	}
	
	class ar_html_formInputSelect extends ar_html_formInput {
		
		public function __construct($field, $form) {
			parent::__construct($field, $form);
			$this->options	= isset($field->options) ? $field->options : array();
		}
		
		protected function getInput($type=null, $name=null, $value=null, $disabled=null, $id=null, 
						$options=null, $selectedValue=null) {
			if (!isset($name)) {
				$name = $this->name;
			}
			if (!isset($value)) {
				$value = $this->value;
			}
			if (!isset($id)) {
				$id = $name; 
			}
			if (!isset($disabled)) {
				$disabled = $this->disabled;
			}
			$attributes = array(
				'name'	=> $name,
				'id'	=> $id
			);
			$content = ar_html::nodes();
			if ($disabled) {
				$attributes['disabled'] = true;
				$content[] = ar_html::tag('input', array('name' => $name, 'type' => 'hidden', 'value' => $selectedValue));
			}
			$content[] = ar_html::tag('select', $this->getOptions($options, $selectedValue), $attributes);
			return $content;
		}

		protected function getOptions($options=null, $selectedValue=false) {
			$content = ar_html::nodes();
			if (!isset($options)) {
				$options = $this->options;
			}
			if (is_array($options)) {
				foreach($options as $key => $option) {
					if (!is_array($option)) {
						$option = array(
							'name' => $option
						);
					}
					if (!isset($option['value'])) {
						$option['value'] = $key;
					}
					$content[] = $this->getOption($option['name'], $option['value'], $selectedValue);
				}
			}
			return $content;
		}
		
		protected function getOption($name, $value='', $selectedValue=false) {
			$attributes = array(
				'value' => $value
			);
			if ($selectValue!==false && $selectedValue == $value) {
				$attributes[] = 'selected';
			}
			return ar_html::tag('option', $name, $attributes);
		}
	}
	
	class ar_html_formInputCheckbox extends ar_html_formInput {
	
		public function __construct($field, $form) {
			parent::__construct($field, $form);
			$this->checkedValue = $field->checkedValue;
			$this->uncheckedValue = $field->uncheckedValue;
		}

		public function __toString() {
			$content = ar_html::nodes();
			if (isset($this->uncheckedValue)) {
				$content[] = $this->getInput('hidden', $this->name, $this->uncheckedValue, false, 
					$this->name.'_uncheckedValue');
			}
			$content[] = $this->getCheckBox($this->name, $this->checkedValue, 
				($this->checkedValue==$this->value), $this->disabled, $this->uncheckedValue);
			$content[] = $this->getLabel($this->label, $this->name);
			return (string)$this->getField($content);
		}
		
		protected function getCheckBox($name=null, $value=null, $checked=false, $disabled=null, $uncheckedValue=false) {
			$content = ar_html::nodes();
			if (!isset($name)) {
				$name = $this->name;
			}
			if (!isset($value)) {
				$value = $this->value;
			}
			if (!isset($id)) {
				$id = $name; 
			}
			if (!isset($disabled)) {
				$disabled = $this->disabled;
			}
			$attributes = array(
				'type'	=> 'checkbox',
				'name'	=> $name,
				'id'	=> $id,
				'value'	=> $value
			);
			if ($checked) {
				$attributes[] = 'checked';
			}
			if ($disabled) {
				$attributes['disabled'] = true;
				if (!$checked && $uncheckedValue) {
					$hiddenvalue = $uncheckedValue;
				} else if ($checked) {
					$hiddenvalue = $value;
				} else {
					$hiddenvalue = false;
				}
				if ($hiddenvalue) {
					$content[] = ar_html::tag('input', array('type' => 'hidden', 'name' => $name, 'value' => $hiddenvalue));
				}
			}
			$content[] = ar_html::tag('input', $attributes );
			return $content;
		}
	}

	class ar_html_formInputRadio extends ar_html_formInputSelect {
		public function __construct($field, $form) {
			parent::__construct($field, $form);
			$this->options	= isset($field->options) ? $field->options : array();
		}
		
		protected function getInput($type=null, $name=null, $value=null, $disabled=null, $id=null, 
						$options=null, $selectedValue=null) {
			if (!isset($name)) {
				$name = $this->name;
			}
			if (!isset($value)) {
				$value = $this->value;
			}
			if (!isset($id)) {
				$id = $name; 
			}
			if (!isset($disabled)) {
				$disabled = $this->disabled;
			}
			$attributes = array(
				'class' => 'radioButtons'
			);
			$content[] = ar_html::tag('div', $this->getRadioButtons($name, $options, $selectedValue), $attributes);
			return $content;
		}

		protected function getRadioButtons($name=null, $options=null, $selectedValue=null) {
			$content = ar_html::nodes();
			if (!isset($name)) {
				$name = $this->name;
			}
			if (!isset($options)) {
				$options = $this->options;
			}
			if (is_array($options)) {
				$count = 0;
				foreach($options as $key => $option) {
					if (!is_array($option)) {
						$option = array(
							'name' => $option
						);
					}
					if (!isset($option['value'])) {
						$option['value'] = $key;
					}
					$content[] = $this->getRadioButton($name, $option['value'], $option['name'],
								$selectedValue, $option['disabled'], 'radioButton', $name.'_'.$count);
					$count++;
				}
			}
			return $content;
		}
		
		protected function getRadioButton($name, $value='', $label=null, $selectedValue=false, $disabled=null, 
							$class=null, $id=null) {
			if (isset($class)) {
				$class = array('class' => $class);
			}
			$attributes = array(
				'type'	=> 'radio',
				'value' => $value,
				'name'	=> $name,
				'id'	=> $id
			);
			if ($selectValue!==false && $selectedValue == $value) {
				$attributes[] = 'checked';
			}
			if ($disabled) {
				$attributes['disabled'] = true;
			}
			return ar_html::tag('div', $class, ar_html::nodes(
				ar_html::tag('input', $attributes),
				$this->getLabel($label, $id)));
		}	
	}
	
	class ar_html_formInputHtml extends ar_html_formInput {
		
		public function __toString() {
			$content = ar_html::nodes();
			if ($this->label) {
				$content[] = $this->getLabel($this->label);
			}
			$content[] = $this->value;
			return (string)$this->getField($content);
		}
	}
	
	class ar_html_formInputFieldset extends ar_html_formInput {
		private $children = null;

		public function __construct($field, $form) {
			parent::__construct($field, $form);
			$this->children = $this->form->parseFields($field->children);
		}
				
		public function hasChildren() {
			return sizeof($this->children)>0;
		}
		
		public function getField($content=null) {
			if ($this->label) {
				$legend = ar_html::tag('legend', $this->label);
			}
			if (!isset($content)) {
				$content = $this->children;
			}
			$content = ar_html::nodes($legend, $content);
			$class = array('field', $this->type);
			if ($this->class) {
				$class[] = $this->class;
			}
			$attributes = array('class' => $class);
			if ($this->id) {
				$attributes['id'] = $id;
			}
			return ar_html::tag('fieldset', $content, $attributes);
		}
	}
?>