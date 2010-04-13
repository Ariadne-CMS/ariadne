<?php
	ar_pinp::allow('ar_html_table');

	class ar_html_table extends arBase {
		protected $attributes;
		protected $content;
		protected $caption = '';
		
		public function __construct( $content, $attributes = null ) {
			$this->attributes = $attributes;
			$this->content    = $content;
		}
		
		public function body( $content ) {
			if (!is_array($content) ) {
				$content = array( $content );
			}
			$content = $this->getRows($content, array( 'body' => true ) );
			$this->body = $this->decorate( array(
				'name'    => 'tbody',
				'content' => $content
			) );
			return $this;
		}

		private function getRows( $list, $flags = array(), $attributes = array() ) {
			$nodes   = ar_html::nodes();
			$total   = count($list);
			$current = 1;
			foreach ( $list as $key => $content ) {
				$firstRow = ( 1 == $current );
				$lastRow  = ( $total == $current );
				$oddRow   = ( $current % 2 );
				$currentFlags = array_merge( (array) $flags, array(
					'currentRow' => $current,
					'firstRow'   => $firstRow,
					'lastRow'    => $lastRow,
					'oddRow'     => $oddRow
				) );
				$currentAttributes = $attributes;
				if ( !is_array($content) ) {
					$content = array($content);
				}
				if ( isset($this->rowHeaders) ) {
					$header = $this->decorate( array(
						'name'       => 'th',
						'attributes' => array_merge( 
							(array) $currentAttributes, 
							(array) $this->rowHeaderAttributes, 
							array( 'scope' => 'row' ) 
						),
						'content'    => $this->rowHeaders[$key],
						'flags'      => $currentFlags
					) );
				} else {
					$header = '';
				}
				$content = $this->getCells( $content, $currentFlags, 'td', $currentAttributes);
				$currentAttributes['class'] = array_merge( (array) $attributes['class'], array(
					( $firstRow ? 'tableFirst' : 
						( $lastRow ? 'tableLast' : '' )
					),
					( is_numeric($key) ? '' : 'tableRow_'.$key ),
					( $oddRow ? 'tableOdd' : 'tableEven' )
				) );
				$nodes[] = $this->decorate( array(
					'name'       => 'tr',
					'attributes' => $currentAttributes,
					'content'    => ar('html')->nodes($header, $content),
					'flags'      => $currentFlags
				) );
				$current++;
			}
			return $nodes;
		}
		
		private function getCells( $list, $flags = array(), $tag, $attributes = array() ) {
			$nodes   = ar_html::nodes();
			$total   = count($list);
			$current = 1;
			foreach ($list as $key => $content ) {
				$firstCell = (1==$current);
				$lastCell  = ($total==$current);
				$oddCell   = ($current % 2);
				$currentFlags = array_merge( (array) $flags, array(
					'currentColumn' => $current,
					'firstCell'     => $firstCell,
					'lastCell'      => $lastCell,
					'oddCell'       => $oddCell
				) );
				$currentAttributes = $attributes;
				$currentAttributes['class'] = array_merge( (array) $attributes['class'], array(
					( $firstCell ? 'tableFirst' : 
						( $lastCell ? 'tableLast' : '' ) 
					),
					( $oddCell ? 'tableOdd' : 'tableEven' )
				) );
				$nodes[] = $this->decorate( array(
					'name'       => $tag,
					'attributes' => $currentAttributes,
					'content'    => $content,
					'flags'      => $currentFlags
				) );
				$current++;
			}
			return $nodes;
		}
		
		public function head( $list, $attributes = null ) {
			if ( is_array($list) ) {
				$flags = array(
					'firstRow'   => true,
					'lastRow'    => true,
					'oddRow'     => false,
					'head'       => true,
					'currentRow' => 1
				);
				$nodes = $this->getCells( $list, $flags, 'th' );
				$this->head = $this->decorate( array(
					'name'       => 'thead', 
					'attributes' => $attributes,
					'content'    => $this->decorate( array(
						'name'        => 'tr',
						'content'     => $nodes,
						'flags'       => $flags
					) ) 
				) );
			}
			return $this;
		}
		
		protected function decorate( $tag ) {
			if ( isset( $this->decorator ) && is_callable( $this->decorator ) ) {
				$decorator = $this->decorator;
				return $decorator( $tag );
			} else {
				return ar_html::tag( $tag['name'], $tag['attributes'], $tag['content'] );
			}
		}
		
		public function setDecorator( $callback ) {
			if ( !is_callable( $callback ) ) {
				if ( is_string( $callback ) ) {
					$callback = ar_pinp::getCallback( $callback, array( 'tag' ) );
				} else {
					return $this;
				}
			}				
			$this->decorator = $callback;
			return $this;
		}
		
		public function foot( $list, $attributes = null ) {
			if ( is_array( $list ) ) {
				$flags = array(
					'firstRow'   => true,
					'lastRow'    => true,
					'oddRow'     => true,
					'foot'       => true,
					'currentRow' => 1
				);
				$nodes = $this->getCells( $list, $flags, 'td' );
				$this->foot = $this->decorate( array(
					'name'       => 'tfoot',
					'attributes' => $attributes,
					'content'    => $this->decorate( array(
						'name'       => 'tr',
						'content'    => $nodes,
						'flags'      => $flags
					) )
				) );
			}
			return $this;
		}
		
		public function rowHeaders( $list, $attributes = array() ) {
			$this->rowHeaders = $list;
			$this->rowHeaderAttributes = $attributes;
			return $this;
		}
			
		public function cols() {
			$args = func_get_args();
			$this->cols = call_user_func_array( array('ar_html', 'nodes'), $args );
			return $this;
		}
		
		public function caption() {
			$args = func_get_args();
			array_unshift($args, 'caption');
			$this->caption = call_user_func_array( array('ar_html', 'tag'), $args );
			return $this;
		}
		
		public function __toString() {
			if (!isset($this->body)) {
				$this->body( $this->content );
			}
			return $this->decorate( array(
				'name'       => 'table',
				'attributes' => $this->attributes,
				'content'    => ar_html::nodes(
					$this->caption, 
					$this->cols, 
					$this->head, 
					$this->body, 
					$this->foot 
				)
			) );
		}
	}
?>