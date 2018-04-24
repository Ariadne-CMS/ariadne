<?php

	ar_pinp::allow('ar_html_form', array(
		'configure', 'addField', 'addButton', 'setValue', 'getValue', 'getValues', 'getHTML', 'isValid', 'isSubmitted', 'validate', 'registerInputType', 'registerValidateCheck', 'findField'
	) );

	class ar_html_form extends arBase {
		// todo: file upload field, captcha
		static public $customTypes;
		static public $requiredTitle = 'Required';

		static public $checks = array(
			'alpha'        => '/^[[:alpha:]]+$/iD',
			'alphanumeric' => '/^[[:alnum:]]+$/iD',
			'abs_int'      => '/^\d+$/iD',
			'int'          => '/^[+-]?\d+$/iD',
			'abs_number'   => '/^([0-9]+\.?[0-9]*|\.[0-9]+)$/D',
			'number'       => '/^[+-]?([0-9]+\.?[0-9]*|\.[0-9]+)$/D',
			'abs_money_us' => '/^(\d{1,3}(\,\d{3})*|(\d+))(\.\d{2})?$/D',
			'money_us'     => '/^[+-]?(\d{1,3}(\,\d{3})*|(\d+))(\.\d{2})?$/D',
			'abs_money'    => '/^(\d{1,3}(\.\d{3})*|(\d+))(\,\d{2})?$/D',
			'money'        => '/^[+-]?(\d{1,3}(\.\d{3})*|(\d+))(\,\d{2})?$/D',
			'email'        => '/^[\w!#$%&\'*+\/=?^`{|}~.-]+@(?:[a-z\d][a-z\d-]*(?:\.[a-z\d][a-z\d-]*)?)+\.(?:[a-z][a-z\d-]+)$/iD',
			'domain_name'  => '/^([[:alnum:]]([a-zA-Z0-9\-]{0,61}[[:alnum:]])?\.)+[[:alpha:]]{2,}$/D',
			'url'          => '/^(http|https|ftp)\:\/\/[a-zA-Z0-9\-\.]+\.[[:alpha:]]{2,3}(:[[:alnum:]]*)?\/?([a-zA-Z0-9\-\._\?\,\'\/\\\+&amp;%\$#\=~])*$/D',
			'credit_card'  => '/^(\d{4}-){3}\d{4}$|^(\d{4} ){3}\d{4}$|^\d{16}$/D',
			'date'         => '/^(\d{1,2}[.-/]\d{1,2}[.-/](\d{2}|\d{4})$/D',
			'time'         => '/^(\d{1,2}[:]\d{2}([:]\d{2})?$/D'
		);

		protected $fields = array();
		protected $buttons = array();

		public    $action, $method, $name, $class, $id, $requiredLabel, $encType;

		public function __construct($fields=null, $buttons=null, $action='', $method="POST", $requiredLabel=null) {
			if ( isset($fields) ) {
				$this->fields = $this->parseFields($fields);
			}
			if (ar('loaderSession')->getvar("formSecret")) {
				$this->fields = array_merge($this->fields, $this->parseFields(
					array('formSecret' => array(
						'type' => 'hidden',
						'name' => 'formSecret',
						'value' => ar('loaderSession')->getvar("formSecret"),
						'checks' => '/^'.ar('loaderSession')->getvar("formSecret").'$/D'
					))
				));
			}

			if ( !isset($buttons)) {
				$buttons = array('Ok');
			}
			$this->buttons	= $this->parseButtons($buttons);
			$this->action	= $action;
			$this->method	= $method;
			$this->requiredLabel = isset($requiredLabel) ? $requiredLabel :
				ar_html::el('span', array('title' => self::$requiredTitle, 'class' => 'formRequired'), '*');
		}

		public function configure( $name, $value ) {
			switch ( $name ) {
				case 'requiredTitle' :
					self::$requiredTitle = $value;
				break;
			}
			return $this;
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

		public function getHTML() {
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
			if (isset($this->encType) ) {
				$attributes['enctype'] = $this->encType;
			}
			$content = ar_html::nodes();
			if (is_array($this->fields)) {
				foreach ($this->fields as $key => $field) {
					$content[] = $field->getField();
				}
			}
			if ($this->buttons) {
				$buttonContent = ar_html::nodes();
				foreach ($this->buttons as $key => $button) {
					$buttonContent[] = $button->getButton();
				}
				$content[] = ar_html::el('div', $buttonContent, array('class' => 'formButtons'));
			}
			return ar_html::el('form', $content, $attributes);
		}

		public function __toString() {
			return (string) $this->getHTML();
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
				$values = array_replace_recursive($values, $result);
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
				$type   = null;
				$name   = null;
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
			if ( !$label && $label!==false ) { // false means 'don't add a lable'
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
			$newFields = array();
			if (is_array($fields)) {
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

		public function validate( $inputs = null ) {
			$valid = array();
			foreach ( $this->fields as $key => $field ) {
				$result = $field->validate( $inputs );
				$valid  = array_merge( $valid, $result );
			}
			return $valid;
		}

		public function isValid() {
			$valid = $this->validate();
			return count( $valid ) == 0;
		}

		public function isSubmitted( $name = null ) {
			// check if any of the submit buttons is available, if no submit buttons are set, check if any of the input values are
			if ( isset($name) ) {
				$value = ar('http')->getvar($name);
				return isset( $value );
			} else {
				if ( is_array( $this->buttons ) ) {
					foreach ( $this->buttons as $button ) {
						if ( $button->type=='submit' || $button->type=='image' ) {
							if ( ar('http')->getvar($button->name) == $button->value ) {
								return true;
							}
						}
					}
				}
				foreach ( $this->fields as $field ) {
					if ( ar('http')->getvar($field->name) !== null ) {
						return true;
					}
				}
			}
			return false;
		}

		public static function registerInputType( $type, $getInput, $getValue = null, $getLabel = null, $getField = null ) {
			self::$customTypes[ $type ] = array(
				'getInput' => $getInput,
				'getValue' => $getValue,
				'getLabel' => $getLabel,
				'getField' => $getField
			);
			foreach( self::$customTypes[ $type ] as $name => $method ) {
				if ( isset( $method ) && $method ) {
					if ( !is_callable($method) ) {
						if ( is_string($method) ) {
							$method = ar_pinp::getCallback($method, array('field') );
						} else {
							$method = null;
						}
					}
				} else {
					$method = null;
				}
				self::$customTypes[ $type ][ $name ] = $method;
			}
		}

		public static function registerValidateCheck( $name, $check, $message ) {
			if ( !is_string( $check ) || ( $check[0] != '/' && !is_callable( $check ) ) ) {
				$check = ar_pinp::getCallback( $check, array( 'value' ) );
			}
			self::$checks[ $name ] = array(
				'check' => $check,
				'message' => $message,
			);
		}

		public function __set($name, $value) {
			if ($name[0] == '_') {
				$name = substr($name, 1);
			}
			if ( in_array( $name, array('action', 'method', 'name', 'class', 'id', 'requiredLabel' ) ) ) {
				$this->{$name} = $value;
			}
		}

		public function __get($name) {
			if ($name[0] == '_') {
				$name = substr($name, 1);
			}
			if ( in_array( $name, array('action', 'method', 'name', 'class', 'id', 'requiredLabel') ) ) {
				return $this->{$name};
			}
		}
	}

	class ar_html_formButton {

		protected $form;
		public $type, $name, $value, $class, $id, $title;

		public function __construct($button, $form) {
			$this->form   = $form;
			$this->type   = isset($button->type) ? $button->type : null;
			$this->name   = isset($button->name) ? $button->name : null;
			$this->value  = isset($button->value) ? $button->value : null;
			$this->class  = isset($button->class) ? $button->class : null;
			$this->id     = isset($button->id) ? $button->id : null;
			$this->title  = isset($button->title) ? $button->title : null;
		}

		public function getButton($type=null, $name=null, $value=null, $class=null, $id=null, $title=null, $extra=null) {
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
			if (!isset($title)) {
				$title = $this->title;
			}
			$attributes = array(
				'type'  => $type,
				'name'  => $name,
				'value' => $value
			);
			if (isset($class)) {
				$attributes['class'] = $class;
			}
			if (isset($title)) {
				$attributes['title'] = $title;
			}
			if (isset($id)) {
				$attributes['id'] = $id;
			}
			if ($extra) {
				$attributes = array_merge($attributes, $extra);
			}
			return ar_html::el('input', $attributes);
		}

		public function __toString() {
			return (string) $this->getButton();
		}
	}

	class ar_html_formButtonImage extends ar_html_formButton {
		public $src, $alt;

		public function __construct($button, $form) {
			parent::__construct($button, $form);
			$this->src = isset($button->src) ? $button->src : null;
			$this->alt = isset($button->alt) ? $button->alt : null;
		}

		public function getButton($type=null, $name=null, $value=null, $class=null, $id=null, $title=null, $src=null, $alt=null, $extra=null) {
			if (!isset($src)) {
				$src = $this->src;
			}
			if (!isset($alt)) {
				$alt = $this->alt;
			}
			return parent::getButton($type, $name, $value, $class, $id, $title, array_merge($extra, array('src' => $src, 'alt' => $alt )));
		}
	}

	class ar_html_formInput {

		protected $form;
		public    $type, $name, $class, $id, $label, $disabled, $default, $required, $conditional, $checks, $value, $title, $help;

		public function __construct($field, $form) {
			$this->form		= $form;
			$this->type		= isset($field->type) ? $field->type : null;
			$this->name		= isset($field->name) ? $field->name : null;
			$this->class	= isset($field->class) ? $field->class : null;
			$this->id		= isset($field->id) ? $field->id : null;
			$this->label	= isset($field->label) ? $field->label : null;
			$this->disabled	= isset($field->disabled) ? $field->disabled : false;
			$this->default	= isset($field->default) ? $field->default : null;
			$this->required = isset($field->required) ? $field->required : false;
			$this->checks   = isset($field->checks) ? $field->checks : array();
			$this->title    = isset($field->title) ? $field->title : null;
			$this->conditional = isset($field->conditional) ? $field->conditional : false;

			$this->help		= isset($field->help) ? $field->help : null;

			if ( isset($this->checks) && !is_array($this->checks) ) {
				$this->checks = array( $this->checks );
			}
			if (isset($field->value)) {
				$this->value = $field->value;
			} else {
				// Find the value, also allow for format like field[subfield]
				$name = $this->name;
				if (preg_match("/(\w+)\[(.*?)]/", $name, $matches)) {
					$arrayvalue = ar::getvar($matches[1]);
					$value = $this->getArrayValue( substr( $name, strlen( $matches[1] ) ), $arrayvalue );
				} else {
					$value = ar()->getvar($name);
				}

				if (isset($value)) {
					$this->value = $value;
				} else if (isset($this->default)) {
					$this->value = $this->default;
				} else {
					$this->value = null;
				}
			}
		}

		protected function getArrayValue( $name, $value ) {
			if ( !$name ) {
				return $value;
			}
			if ( preg_match( '/^\[([^\]]*)\]/', $name, $matches ) ) {
				$index = $matches[1];
				if ( isset($index) && isset( $value[ $index ] ) ) {
					return $this->getArrayValue( substr( $name, strlen( $index )+2 ), $value[ $index ] );
				} else {
					return null;
				}
			} else {
				return null;
			}
		}

		protected function getHelp($help=null) {
			if (!isset($help)) {
				$help = $this->help;
			}
			$class = array('formHelp', 'formHelp'.ucfirst($this->type) );
			if ($this->class) {
				$class[] = $this->class;
			}
			$attributes = array('class' => $class);

			if ( trim($help) ) {
				$help = ar_html::el('div', $help, array('class' => 'helpContent'));
				return ar_html::el('div', $help, $attributes);
			} else {
				return '';
			}
		}

		protected function getLabel($label=null, $id=null, $attributes=null) {
			if (!isset($attributes)) {
				$attributes = array();
			}
			if (!isset($label)) {
				$label = $this->label;
			}
			if (!isset($id)) {
				$id = $this->name;
			}
			if ($label!==false) {
				if ($this->required) {
					$label .= $this->form->requiredLabel;
				}
				if ($id) {
					$attributes['for'] = $id;
				}
				return ar_html::el('label', $label, $attributes);
			} else {
				return '';
			}
		}

		protected function getInput($type=null, $name=null, $value=null, $disabled=null, $id=null, $title=null) {
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
			if (!isset($title)) {
				$title = $this->title;
			}
			$attributes = array(
				'type'	=> $type,
				'name'	=> $name,
				'id'	=> $id,
				'value'	=> $value
			);
			if ($title) {
				$attributes['title'] = $title;
			}
			$content = ar_html::nodes();
			if ($disabled) {
				$attributes['disabled'] = true;
				$content[] = ar_html::el('input', array('type' => 'hidden', 'name' => $name, 'value' => $value));
			}
			$content[] = ar_html::el('input', $attributes);
			return $content;
		}

		public function getValue() {
			return $this->value;
		}

		public function setValue($value) {
			$this->value = $value;
			return true;
		}

		private function buildQuery( $name, $value ) {
			if ( is_array( $value ) ) {
				$result = '';
				foreach ( $value as $index => $subvalue ) {
					$result .= $this->buildQuery( $name.'['.$index.']', $subvalue ) . '&';
				}
			} else {
				$result = $name . '=' . RawUrlEncode( $value );
			}
			return $result;
		}

		public function getNameValue() {
			$nameArrayIndex = strpos( $this->name, '[' );
			if ( $nameArrayIndex ) {
				//FIXME: this results in \r\n being encoded as &#13;&#10;
				$urlQuery = $this->buildQuery( $this->name, $this->getValue() );
				parse_str( $urlQuery, $result );
				return $result;
			} else {
				return array( $this->name => $this->getValue() );
			}
		}

		public function getField( $content = null ) {
			if (!isset($content)) {
				$content = ar_html::nodes($this->getLabel(), $this->getInput());
			}
			$help = $this->getHelp();
			if ($help) {
				$content[] = $help;
			}
			$class = array('formField', 'form'.ucfirst($this->type) );
			if ($this->class) {
				$class[] = $this->class;
			}
			$attributes = array('class' => $class);
			if ($this->id) {
				$attributes['id'] = $this->id;
			}
			return ar_html::el('div', $content, $attributes);
		}

		public function validateConditional() {
			$conditionMet = true;
			if ($this->conditional) {
				$conditionMet = false;
				if (is_string($this->conditional)) {
					try {
						$condition = json_decode($this->conditional, true);
					} catch(Exception $e) {
						$condition = array();
					}
				}

				foreach ($condition as $key => $value) {
					if (is_string($value)) {
						$checkValues = array($value);
					} else {
						$checkValues = $value;
					}

					foreach ($checkValues as $checkValue) {
						$conditionValue = $this->form->getValue($key);

						if (
							$conditionValue == $checkValue || 
							(is_array($conditionValue) && $conditionValue[$checkValue])
						) {
							$conditionMet = true;
						}
					}
				}
			}

			return $conditionMet;
		}

		public function validate() {
			$result = array();
			if (!$this->validateConditional()) {
				return $result;
			}

			$value  = $this->getValue();

			if ( $this->required && ( !isset($value) || $value === '' ) ) {
				$result[ $this->name ] = ar::error( 'Required input missing', 'required' );
			} else if ( is_array( $this->checks ) ) {
				foreach( $this->checks as $check ) {
					$regex = false;
					if ( isset(ar_html_form::$checks[$check]) ) {
						if ( is_array(ar_html_form::$checks[$check])
							&& isset(ar_html_form::$checks[$check]['check']) ) {
							$checkMethod = ar_html_form::$checks[$check]['check'];
							$message	 = ar_html_form::$checks[$check]['message'];
							if ( is_callable( $checkMethod ) ) {
								if ( !$checkMethod( $value ) ) {
									$result[ $this->name ] = ar::error(
										sprintf( $message, $value ),
										$check
									);
								}
							} else if ( is_string($checkMethod) && $checkMethod[0]=='/' ) {
								$regex = $checkMethod;
							}
						} else {
							$regex   = ar_html_form::$checks[$check];
							$message = 'Failed to match expected input: '.$check;
						}
					} else {
						$regex   = $check;
						$message = 'Failed to match expected input';
					}
					if ( $regex && !preg_match( $regex, $value ) ) {
						$result[ $this->name ] = ar::error( sprintf( $message, $value ), $check );
					}
				}
			}
			return $result;
		}

		public function __toString() {
			return (string)$this->getField();
		}

	}

	class ar_html_formInputMissing extends ar_html_formInput {

		public function __construct($field, $form) {
			parent::__construct( $field, $form );
			$fieldProps = get_object_vars($field);
			foreach ( $fieldProps as $name => $value ) {
				if ( !isset($this->{$name}) ) {
					$this->{$name} = $value;
				}
			}
		}

		public function getField( $content = null ) {
			if ( isset(ar_html_form::$customTypes[ $this->type ]) ) {
				$getField = ar_html_form::$customTypes[ $this->type ]['getField'];
				if ( isset( $getField) ) {
					return $getField($this, $content);
				}
			}
			return parent::getField(
				ar_html::nodes( $this->getLabel(), $this->getInput() )
			);
		}

		public function getLabel($label=null, $id=null, $attributes=null) {
			if ( isset(ar_html_form::$customTypes[ $this->type ]) ) {
				$getLabel = ar_html_form::$customTypes[ $this->type ]['getLabel'];
				if ( isset( $getLabel ) ) {
					return $getLabel($this);
				}
			}
			return parent::getLabel();
		}

		public function getInput($type=null, $name=null, $value=null, $disabled=null, $id=null, $title=null) {
			if ( isset(ar_html_form::$customTypes[ $this->type ]) ) {
				$getInput = ar_html_form::$customTypes[ $this->type ]['getInput'];
				return $getInput($this);
			}
			return ar_html::el('strong', 'Error: Field type ' . $this->type . ' does not exist.');
		}

		public function getValue() {
			if ( isset(ar_html_form::$customTypes[ $this->type ]) ) {
				$getValue = ar_html_form::$customTypes[ $this->type ]['getValue'];
				if ( isset($getValue) ) {
					return $getValue($this);
				}
			}
			return parent::getValue();
		}

		public function __set($name, $value) {
			if ($name[0] == '_') {
				$name = substr($name, 1);
			}
			$this->{$name} = $value;
		}

		public function __get($name) {
			if ($name[0] == '_') {
				$name = substr($name, 1);
			}
			return $this->{$name};
		}

	}

	class ar_html_formInputButton extends ar_html_formInput {
		public $buttonType, $buttonLabel;

		public function __construct( $field, $form ) {
			parent::__construct( $field, $form );
			$this->buttonType = isset($field->buttonType) ? $field->buttonType : null;
			$this->buttonLabel = isset($field->buttonLabel) ? $field->buttonLabel : $field->value;
		}

		protected function getInput($type=null, $name=null, $value=null, $disabled=null, $id=null, $title=null, $buttonType=null, $buttonLabel=null ) {
			if ( !isset($buttonType) ) {
				$buttonType = $this->buttonType;
			}
			if ( !isset($buttonLabel) ) {
				$buttonLabel = $this->buttonLabel;
			}
			if ( !isset($name) ) {
				$name = $this->name;
			}
			if ( !isset($value) ) {
				$value = $this->value;
			}
			if ( !isset($disabled) ) {
				$disabled = $this->disabled;
			}
			if ( !isset($title) ) {
				$title = $this->title;
			}
			$attributes = array(
				'type'	=> $buttonType,
				'name'	=> $name,
				'value'	=> $value
			);
			if ( $disabled ) {
				$attributes['disabled'] = $disabled;
			}
			if ( isset( $title ) ) {
				$attributes['title'] = $title;
			}
			return ar_html::el('button', $attributes, $buttonLabel);
		}
	}

	class ar_html_formInputText extends ar_html_formInput {
		public $maxlength, $size;

		public function __construct( $field, $form ) {
			parent::__construct( $field, $form );
			$this->maxlength = isset($field->maxlength) ? $field->maxlength : null;
			$this->size = isset($field->size) ? $field->size : null;
		}

		protected function getInput($type=null, $name=null, $value=null, $disabled=null, $id=null, $title=null, $maxlength=null, $size=null ) {
			if ( !isset($type) ) {
				$type = $this->type;
			}
			if ( !isset($name) ) {
				$name = $this->name;
			}
			if ( !isset($value) ) {
				$value = $this->value;
			}
			if ( !isset($id) ) {
				$id = $name; //this->id is for the field div, not the input tag
			}
			if ( !isset($disabled) ) {
				$disabled = $this->disabled;
			}
			if ( !isset($title) ) {
				$title = $this->title;
			}
			if ( !isset($maxlength) ) {
				$maxlength = $this->maxlength;
			}
			if ( !isset($size) ) {
				$size = $this->size;
			}
			$attributes = array(
				'type'	=> $type,
				'name'	=> $name,
				'id'	=> $id,
				'value'	=> $value
			);
			if ( $title ) {
				$attributes['title'] = $title;
			}
			if ( $maxlength ) {
				$attributes['maxlength'] = $maxlength;
			}
			if ( $size ) {
				$attributes['size'] = $size;
			}
			$content = ar_html::nodes();
			if ($disabled) {
				$attributes['disabled'] = true;
				$content[] = ar_html::el('input', array('type' => 'hidden', 'name' => $name, 'value' => $value));
			}
			$content[] = ar_html::el('input', $attributes);
			return $content;
		}

	}

	class ar_html_formInputPassword extends ar_html_formInputText {

		protected function getInput($type=null, $name=null, $value=null, $disabled=null, $id=null, $title=null, $maxlength=null, $size=null) {
			$value = ''; // never display a password's value
			return parent::getInput($type, $name, $value, $disabled, $id, $title);
		}

	}

	class ar_html_formInputFile extends ar_html_formInput {

		public $multiple = false;

		public function __construct( $field, $form ) {
			parent::__construct( $field, $form );
			$this->multiple = isset($field->multiple) ? $field->multiple : false;
		}

		protected function getInput($type=null, $name=null, $value=null, $disabled=null, $id=null, $title=null, $multiple=null ) {
			$content = parent::getInput( $type, $name, $value, $disabled, $id, $title );
			if ( !isset($multiple) ) {
				$multiple = $this->multiple;
			}
			if ( $multiple ) {
				$content->attributes['multiple'] = true;
			}
			return $content;
		}

	}

	class ar_html_formInputHidden extends ar_html_formInput {

		public function __construct($field, $form) {
			if ($field->label == $field->name) {
				$field->label = false;
			}
			parent::__construct($field, $form);
			$this->disabled = false;
		}

		public function __toString() {
			return (string)$this->getField($this->getInput());
		}
	}

	class ar_html_formInputTextarea extends ar_html_formInputText {

		public $rows, $cols;

		public function __construct( $field, $form ) {
			parent::__construct( $field, $form );
			$this->maxlength = ( isset($field->maxlength) ? $field->maxlength : null );
			$this->rows = ( isset($field->rows) ? $field->rows : null );
			$this->cols = ( isset($field->cols) ? $field->cols : null );
		}

		protected function getInput( $type=null, $name=null, $value=null, $disabled=null, $id=null, $title=null, $maxlength=null, $rows=null, $cols=null ) {
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
			if ( !isset($maxlength) ) {
				$maxlength = $this->maxlength;
			}
			if ( !isset($rows) ) {
				$rows = $this->rows;
			}
			if ( !isset($cols) ) {
				$cols = $this->cols;
			}
			$attributes = array(
				'name'	=> $name,
				'id'	=> $id
			);
			if (!isset($title)) {
				$title = $this->title;
			}
			if ($title) {
				$attributes['title'] = $title;
			}
			if ($disabled) {
				$attributes['disabled'] = true;
			}
			if ( $maxlength ) {
				$attributes['maxlength'] = $maxlength;
			}
			if ( $cols ) {
				$attributes['cols'] = $cols;
			}
			if ( $rows ) {
				$attributes['rows'] = $rows;
			}
			return ar_html::el('textarea', htmlspecialchars($value, ENT_QUOTES, 'UTF-8' ), $attributes);
		}
	}

	class ar_html_formInputSelect extends ar_html_formInput {
		public $options;

		public function __construct($field, $form) {
			parent::__construct($field, $form);
			$this->options	= isset($field->options) ? $field->options : array();
			$this->multiple = isset($field->multiple) ? $field->multiple : false;
		}

		protected function getInput($type=null, $name=null, $value=null, $disabled=null, $id=null, $title=null, $options=null, $multiple=null) {
			if (!isset($name)) {
				$name = $this->name;
			}
			if (!isset($value)) {
				$value = $this->value;
			}
			if (!isset($id)) {
				$id = $name;
			}
			if (!isset($multiple)) {
				$multiple = $this->multiple;
			}
			if (!isset($disabled)) {
				$disabled = $this->disabled;
			}
			$attributes = array(
				'name'	=> $name,
				'id'	=> $id
			);
			if (!isset($title)) {
				$title = $this->title;
			}
			if ($title) {
				$attributes['title'] = $title;
			}

			if ($multiple) {
				$attributes['multiple'] = "multiple";
			}
			$content = ar_html::nodes();
			if ($disabled) {
				$attributes['disabled'] = true;
			}
			$content[] = ar_html::el('select', $this->getOptions($options, $value), $attributes);
			return $content;
		}

		protected function getOptions($options=null, $selectedValues=false) {
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
					$content[] = $this->getOption($option['name'], $option['value'], $selectedValues);
				}
			}
			return $content;
		}

		protected function getOption($name, $value='', $selectedValues=false) {
			$attributes = array(
				'value' => $value
			);
			if ($selectedValues!==false && ( (!$this->multiple && $selectedValues == $value) || ( is_array($selectedValues) && $selectedValues[$name] == $value ) ) ){
				$attributes[] = 'selected';
			}
			return ar_html::el('option', $name, $attributes);
		}
	}

	class ar_html_formInputButtonList extends ar_html_formInputSelect {

		protected function getInput($type=null, $name=null, $value=null, $disabled=null, $id=null, $title=null, $options=null, $multiple=null) {
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
				'class' => 'formButtonListButtons',
				'id'    => $id
			);
			$buttonAttributes = array(
				'name'	=> $name
			);
			if ($disabled) {
				$buttonAttributes['disabled'] = true;
			}
			if (!isset($title)) {
				$title = $this->title;
			}
			if ($title) {
				$attributes['title'] = $title;
			}
			$content = ar_html::nodes();
			$content[] = ar_html::el('div', $this->getButtons($name, $options, $value, $buttonAttributes), $attributes);
			return $content;
		}

		protected function getButtons( $name, $options, $value, $attributes ) {
			$content = ar_html::nodes();
			if ( !isset($options) ) {
				$options = $this->options;
			}
			if ( is_array($options) ) {
				foreach ( $options as $key => $button ) {
					if ( !is_array($button) ) {
						$button = array(
							'label' => $button
						);
					}
					if ( !isset($button['value']) ) {
						$button['value'] = $key;
					}
					$content[] = $this->getButton($key, $name, $button, $value, $attributes);
				}
			}
			$content->setAttribute('class', array(
				'formButtonList' => ar::listPattern( 'formButtonListFirst .*', '.* formButtonListLast' )
			) );
			if ( !$this->multiple ) {
				$content->insertBefore( ar_html::el('input', array(
					'type' => 'hidden',
					'name' => $name,
					'value' => $value
				) ), $content->firstChild );
			}
			return $content;
		}

		protected function getButton( $index, $name, $button, $selectedValues, $attributes ) {
			// FIXME: add hidden inputs with current value when multiple values are allowed
			// pressing button again will unset the corresponding value
			if ($button['label']) {
				$buttonLabel = $button['label'];
				unset( $button['label'] );
			} else {
				$buttonLabel = $button['value'];
			}
			$attributes = array_merge( $button, $attributes );
			$buttonEl = ar_html::el('button', $attributes, $buttonLabel);
			if ( $selectedValues!==false
				&& ( (!$this->multiple && $selectedValues == $button['value'])
				|| ( is_array($selectedValues) && $selectedValues[$name] == $button['value'] ) )
			) {
				$buttonEl->setAttribute('class', array(
					'formButtonListSelected' => 'formButtonListSelected'
				) );
			}
			return $buttonEl;
		}

	}

	class ar_html_formInputCheckbox extends ar_html_formInput {
		public $checkedValue, $uncheckedValue;

		public function __construct($field, $form) {
			parent::__construct($field, $form);
			$this->checkedValue = $field->checkedValue;
			$this->uncheckedValue = $field->uncheckedValue;
		}

		public function __toString() {
			return (string) $this->getField();
		}

		public function getField($content=null) {
			$content = ar_html::nodes();
			if (isset($this->uncheckedValue)) {
				$content[] = $this->getInput('hidden', $this->name, $this->uncheckedValue, false,
					$this->name.'_uncheckedValue');
			}
			$content[] = $this->getCheckBox($this->name, $this->checkedValue,
				($this->checkedValue==$this->value), $this->disabled, $this->uncheckedValue, $this->id);
			$content[] = $this->getLabel($this->label, $this->name);
			return parent::getField($content);
		}

		protected function getCheckBox($name=null, $value=null, $checked=false, $disabled=null, $uncheckedValue=false, $id=null) {
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
					$content[] = ar_html::el('input', array('type' => 'hidden', 'name' => $name, 'value' => $hiddenvalue));
				}
			}
			$content[] = ar_html::el('input', $attributes );
			return $content;
		}
	}

	class ar_html_formInputRadio extends ar_html_formInputSelect {
		public function __construct($field, $form) {
			parent::__construct($field, $form);
			$this->options	= isset($field->options) ? $field->options : array();
		}

		protected function getInput($type=null, $name=null, $value=null, $disabled=null, $id=null, $title=null, $options=null, $multiple=null) {
			if (!isset($name)) {
				$name = $this->name;
			}
			if (!isset($value)) {
				$value = $this->value;
			}
			if (!isset($id)) {
				$id = $name;
			}
			$attributes = array(
				'class'    => 'formRadioButtons',
				'id'       => $id
			);
			if (!isset($disabled)) {
				$disabled = $this->disabled;
			}
			if ( $disabled ) {
				$attributes['disabled'] = true;
			}
			$content[] = ar_html::el('div', $this->getRadioButtons($name, $options, $value), $attributes);

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
					$content[] = $this->getRadioButton(
						$name,
						$option['value'],
						$option['name'],
						$selectedValue,
						$option['disabled'],
						'radioButton',
						$name.'_'.$count
					);
					$count++;
				}
			}

			return $content;
		}

		protected function getRadioButton( $name, $value='', $label=null, $selectedValue=false, $disabled=null, $class=null, $id=null ) {
			if (isset($class)) {
				$class = array('class' => $class);
			}
			$attributes = array(
				'type'	=> 'radio',
				'value' => $value,
				'name'	=> $name,
				'id'	=> $id
			);
			if ($selectedValue!==false && $selectedValue == $value) {
				$attributes[] = 'checked';
			}
			if ($disabled) {
				$attributes['disabled'] = true;
			}

			return ar_html::el('div', $class, ar_html::nodes(
				ar_html::el('input', $attributes),
				$this->getLabel($label, $id)));
		}
	}

	class ar_html_formInputHtml extends ar_html_formInput {

		public function getField($content=null) {
			$content = ar_html::nodes();
			if ($this->label) {
				$content[] = $this->getLabel($this->label);
			}
			$content[] = $this->value;
			return parent::getField($content);
		}

		public function __toString() {
			return (string) $this->getField();
		}
	}

	class ar_html_formInputFieldset extends ar_html_formInput {
		protected $children = null;

		public function __construct($field, $form) {
			parent::__construct($field, $form);
			$this->children = $this->form->parseFields($field->children);
		}

		public function hasChildren() {
			return count($this->children)>0;
		}

		public function getField($content=null) {
			$legend = null;
			if ($this->label) {
				$legend = ar_html::el('legend', $this->label);
			}
			if (!isset($content)) {
				$content = ar_html::nodes();
				foreach( $this->children as $child ) {
					$content[] = $child->getField();
				}
			}
			$content = ar_html::nodes($legend, $content);
			$help = $this->getHelp();
			if ( $help ) {
				$content[] = $help;
			}
			$class = array('formField', 'form' . ucfirst($this->type) );
			if ($this->class) {
				$class[] = $this->class;
			}
			$attributes = array('class' => $class);
			if ($this->id) {
				$attributes['id'] = $this->id;
			}
			return ar_html::el('fieldset', $content, $attributes);
		}

		public function getNameValue() {
			$result = Array();
			foreach ($this->children as $child) {
				$childResult = $child->getNameValue();
				$result = array_replace_recursive($result, $childResult);
			}
			return $result;
		}

		public function validate( $inputs = null ) {
			$valid = array();
			if (!$this->validateConditional()) {
				return $valid;
			}
			foreach ( $this->children as $key => $child ) {
				$result = $child->validate( $inputs );
				$valid  = array_merge( $valid, $result );
			}

			$value  = $this->getValue();
			if ( is_array( $this->checks ) ) {
				foreach( $this->checks as $check ) {
					if ( is_array(ar_html_form::$checks[$check])
						&& isset(ar_html_form::$checks[$check]['check'])
					) {
						$checkMethod = ar_html_form::$checks[$check]['check'];
						$message = ar_html_form::$checks[$check]['message'];
						if ( is_callable( $checkMethod ) ) {
							if ( !$checkMethod( $value ) ) {
								$valid[ $this->name ] = ar::error(
									sprintf( $message, $value ),
									$check
								);
							}
						} else {
							$valid[ $this->name ] = ar::error('incompatible check for this field',$check);
						}
					}
				}
			}
			return $valid;
		}

	}

	class ar_html_formInputFieldList extends ar_html_formInputFieldset {
		public $defaultField, $newField;

		protected function normalizeChildren( $value ) {
			// make sure the children are a simple array, with numeric keys and that the name of the field
			// is always an array
			// and apply the default formfield on the given values and add those as children
			$this->children = array();
			$count = 0;
			if ( is_array($value) ) {
				foreach ( $value as $key => $child ) {
					if ( !$child ) {
						continue;
					}
					if ( !is_numeric( $key ) ) {
						continue; // skip [Add] and [Delete] entries
					}
					ar::untaint( $child, FILTER_UNSAFE_RAW );
					if ( !is_array($child) || !isset($child['value']) ) {
						$child = array(
							'value' => $child
						);
					}
					$child['name'] = $this->name.'['.$count.']';
					if ( $this->defaultField ) {
						$childOb = clone( $this->defaultField );
						$childOb->name = $child['name'];
						$childOb->setValue( $child['value'] );
					} else {
						$childOb = $this->form->parseField( 0, $child );
					}
					$this->children[] = $childOb;
					$count++;
				}
			}
		}

		protected function getvar( $name ) {
			if (preg_match("/(\w+)\[(.*?)]/", $name, $matches)) {
				$arrayvalue = ar::getvar($matches[1]);
				$value = $this->getArrayValue( substr( $name, strlen( $matches[1] ) ), $arrayvalue );
			} else {
				$value = ar()->getvar($name);
			}
			return $value;
		}

		protected function handleUpdates($default) {
			$check = $this->getvar( $this->name ); //$check = ar('http')->getvar( $this->name );
			if ( isset($check['Delete']) ) {
				$delete = $check['Delete'];
				ar::untaint($delete);
				if ( $this->children[$delete] ) {
					unset( $this->children[$delete] );
				}
			} else if ( isset( $check['Add'] ) ) {
				$addedField = $this->getvar( $this->newField->name ); //ar('http')->getvar( $this->newField->name );
				if ( $addedField ) {
					// add a copy of default to the children of this field
					$newField = $default;
					$newField['value'] = $addedField; // FIXME: generiek maken
					$this->children[] = $this->form->parseField(0, $newField );
				}
			}
		}

		public function __construct($field, $form) {
			parent::__construct ($field, $form);
			if ( isset( $field->value ) ) { // apply default behaviour, step 1
				if ( !isset( $field->newField ) ) {
					$field->newField = array(
						'name' => $this->name.'[]',
						'value' => '',
						'label' => false
					);
				}

				if ( !isset( $field->defaultField ) ) {
					$field->defaultField = array(
						'name' => $this->name.'[]',
						'label' => false
					);
				}
			}

			if ( $field->newField ) {
				$this->newField = $form->parseField( 0, $field->newField );
			}
			if ( $field->defaultField ) {
				$this->defaultField = $form->parseField( 0, $field->defaultField );
			}

			if ( isset( $field->value ) ) { // apply default behaviour, step 2
				$this->normalizeChildren( $field->value );
				$this->handleUpdates( $field->default );
			}
		}

		public function getField($content=null) {
			$fieldset = parent::getField($content);
			$count = 0;
			foreach( $fieldset->div as $field ) {
				$field->appendChild( ar_html::el('button', array(
					'class' => 'formFieldListDelete',
					'type' => 'submit',
					'name' => $this->name.'[Delete]',
					'value' => $count
				), '-' ) );
				$count++;
			}
			if ( $this->newField ) {
				$newField = $this->newField->getField();
				$newField->appendChild( ar_html::el('button', array(
					'class' => 'formFieldListAdd',
					'type' => 'submit',
					'name' => $this->name.'[Add]',
					'value' => $this->name.'NewField'
				), '+' ) );
				$fieldset->appendChild(
					ar_html::el('div', array('class' => 'formFieldListAdd'), $newField )
				);
			}
			return $fieldset;
		}

	}
