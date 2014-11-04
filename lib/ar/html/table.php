<?php
	ar_pinp::allow('ar_html_table');

	class ar_html_table extends ar_htmlElement {

		private $rowHeaderAttributes = null;
		private $rowHeaders = null;

		public function __construct( $data= null, $attributes = null, $childNodes = null, $parentNode = null ) {
			parent::__construct( 'table', $attributes , $childNodes, $parentNode );
			if ( isset($data) ) {
				$this->body( $data );
			}
		}

		public function body( $data ) {
			if (!is_array($data) ) {
				$data = array( $data );
			}
			$rows =	$this->getRows($data)->setAttribute( 'class', array(
				'tableFirstLast' => ar::listPattern( 'tableFirst .*', '.* tableLast'),
				'tableOddEven'   => ar::listPattern( '(tableOdd tableEven?)*' )
			) );
			$this->appendChild(
				ar_html::tag(
					'tbody', $rows
				)
			);
			return $this;
		}

		private function getRows( $list ) {
			$nodes   = ar_html::nodes();
			foreach ( $list as $key => $content ) {
				if ( !is_array( $content ) ) {
					$content = array( $content );
				}
				$cells = $this->getCells( $content, 'td')->setAttribute('class', array(
					'tableOddEven'   => ar::listPattern( '(tableOdd tableEven?)*' ),
					'tableFirstLast' => ar::listPattern( 'tableFirst .*', '.* tableLast')
				) );
				$nodes[] = ar_html::tag( 'tr', ar_html::nodes( $header, $cells ) );
			}
			return $nodes;
		}

		private function getCells( $list, $tag = 'td') {
			$nodes   = ar_html::nodes();
			foreach ($list as $key => $content ) {
				$nodes[] = ar_html::el($tag, $content);
			}
			return $nodes;
		}

		public function head( $list, $attributes = null ) {
			if ( is_array($list) ) {
				$nodes = $this->getCells( $list, 'th' );
				$head = $this->thead->firstChild; //current( $this->getElementsByTagName('thead') );
				if ( !isset($head) ) {
					$head = ar_html::tag( 'thead', $attributes );
					if ( $foot = $this->tfoot->firstChild ) {
						$this->insertBefore( $head, $foot );
					} else if ( $body = $this->tbody->firstChild ) {
						$this->insertBefore( $head, $body );
					} else {
						$this->appendChild( $head );
					}
				} else if (isset($attributes)) {
					$head->setAttributes( $attributes );
				}
				$head->appendChild( ar_html::tag( 'tr', $nodes ) );
			}
			return $this;
		}

		public function foot( $list, $attributes = null ) {
			if ( is_array( $list ) ) {
				$nodes = $this->getCells( $list, 'td' );
				$foot = $this->tfoot->lastChild;
				if ( !isset($foot) ) {
					$foot = ar_html::tag( 'tfoot', $attributes );
					if ( $head = $this->thead->lastChild ) {
						$this->insertBefore( $foot, $head->nextSibling );
					} else if ( $body = $this->tbody->firstChild ) {
						$this->insertBefore( $foot, $body );
					} else {
						$this->appendChild( $foot );
					}
				} else if (isset( $attributes ) ) {
					$foot->setAttributes( $attributes );
				}
				$foot->appendChild( ar_html::tag( 'tr', $nodes ) );
			}
			return $this;
		}

		public function rowHeaders( $list, $attributes = array() ) {
			foreach ( $list as $key => $value ) {
				if ( ! ($value instanceof ar_htmlNode ) || $value->tagName != 'th' ) {
					$list[$key] = ar_html::tag( 'th',
						array_merge(
							(array) $attributes,
							array( 'scope' => 'row' )
						),
						$value
					);
				}
			}
			reset($list);
			foreach( $this->tbody->lastChild->childNodes as $row ) {
				$row->insertBefore( current($list), $row->firstChild );
				next($list);
			}
			$this->rowHeaders = $list;
			$this->rowHeaderAttributes = $attributes;
			return $this;
		}

		public function cols() {
			$args = func_get_args();
			$cols = call_user_func_array( array('ar_html', 'nodes'), $args );
			$this->removeChild( $this->colgroup );
			$this->removeChild( $this->col );
			$this->insertBefore( $cols, $this->firstChild );
			return $this;
		}

		public function caption($content, $attributes = null) {
			$newCaption = ar_html::tag( 'caption', $content, $attributes );
			if ( $caption = $this->caption->firstChild ) {
				$this->replaceChild( $newCaption, $caption );
			} else {
				$this->insertBefore( $newCaption, $this->firstChild );
			}
			return $this;
		}
	}
?>