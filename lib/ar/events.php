<?php

	ar_pinp::allow( 'ar_events', array(
		'listen', 'capture', 'fire', 'get', 'event'
	) );
	ar_pinp::allow( 'ar_eventsInstance' );
	ar_pinp::allow( 'ar_eventsEvent' );
	ar_pinp::allow( 'ar_eventsListener' );

	class ar_events extends arBase {
		protected static $listeners = array();
		protected static $event = null;

		public static function listen( $eventName, $objectType = null, $capture = false ) {
			$path = ar::context()->getPath();
			return new ar_eventsInstance( $path, $eventName, $objectType, $capture );
		}

		public static function capture( $eventName, $objectType = null ) {
			return self::listen( $eventName, $objectType, true );
		}

		public static function fire( $eventName, $eventData = array(), $objectType = null, $path = '') {
			if ( !isset(self::$listeners['capture'][$eventName])
				&& !isset(self::$listeners['listen'][$eventName]) ) {
				return $eventData; // no listeners for this event, so dont bother searching
			}
			$prevEvent = null;
			if ( self::$event ) {
				$prevEvent = self::$event;
			}
			$path = ar::context()->getPath( array( 'path' => $path ) );
			$me = ar::context()->getObject( array( 'path' => $path ) );
			if ( $me && !isset($objectType) ) {
				$objectType = $me->type;
			} else if ( !$objectType ) { // when set to false to prevent automatic filling of the objectType, reset it to null
				$objectType = null;
			}
			self::$event = new ar_eventsEvent( $eventName, $eventData, $objectType, $path, $me );
			if ( self::walkListeners( self::$listeners['capture'][$eventName]??null, $path, $objectType, true ) ) {
				self::walkListeners( self::$listeners['listen'][$eventName]??null, $path, $objectType, false );
			}

			if ( self::$event->preventDefault ) {
				$result = false;
			} else if ( self::$event->data ) {
				$result = self::$event->data;
			} else {
				$result = true;
			}
			self::$event = $prevEvent;
			return $result;
		}

		protected static function walkListeners( $listeners, $path, $objectType, $capture ) {
			$strObjectType = (string) $objectType;
			$objectTypeStripped = $strObjectType;
			$pos = strpos( $strObjectType, '.');
			if ( $pos !== false ) {
				$objectTypeStripped = substr($strObjectType, 0, $pos);
			}
			$pathticles = explode( '/', $path );
			$pathlist = array( '/' );
			$prevpath = '/';
			foreach ( $pathticles as $pathticle ) {
				if ( $pathticle ) {
					$prevpath  .= $pathticle . '/';
					$pathlist[] = $prevpath;
				}
			}

			if ( !$capture ) {
				$pathlist = array_reverse( $pathlist );
			}
			reset($pathlist);

			do {
				$currentPath = current( $pathlist );
				if ( is_array( $listeners[$currentPath]??null ) ) {
					foreach ( $listeners[$currentPath] as $listener ) {
						if ( !isset($listener['type']) ||
							 ( $listener['type'] == $strObjectType ) ||
							 ( $listener['type'] == $objectTypeStripped ) ||
							 ( is_a( $strObjectType, $listener['type'] ) ) )
						{
							$continue = true;
							if ( count($listener['filters']) ) {
								$continue = false;
								foreach( $listener['filters'] as $filter ) {
									if ( $filter instanceof \Closure ) {
										$continue = $filter( self::$event );
										if ( $continue ) {
											continue;
										}
									} else if ( ar_filter::match(self::$event, $filter) ) {
										$continue = true;
										continue;
									}
								}
							}
							if ( $continue ) {
								$result = call_user_func_array( $listener['method'], $listener['args'] );
								if ( $result === false ) {
									return false;
								}
							}
						}
					}
				}
			} while( next( $pathlist ) );
			return true;
		}

		public static function event() {
			return self::$event;
		}

		public static function get( $path ) {
			return new ar_eventsInstance( $path );
		}

		public static function addListener( $path, $eventName, $objectType, $method, $args, $capture = false, $filters = array() ) {
			$when = ($capture) ? 'capture' : 'listen';
			self::$listeners[$when][$eventName][$path][] = array(
				'type' => $objectType,
				'method' => $method,
				'args' => $args,
				'filters' => $filters
			);
			return new ar_eventsListener( $eventName, $path, $capture, count(self::$listeners[$when][$eventName][$path])-1 );
		}

		public static function removeListener( $name, $path, $capture, $id ) {
			$when = ($capture) ? 'capture' : 'listen';
			unset( self::$listeners[$when][$name][$path][$id] );
		}
	}

	class ar_eventsListener extends arBase {
		private $capture = false;
		private $path = '';
		private $name = '';
		private $id = null;

		public function __construct( $name, $path, $capture, $id ) {
			$this->name = $name;
			$this->path = $path;
			$this->capture = $capture;
			$this->id = $id;
		}

		public function remove() {
			if ( isset($this->id) ) {
				ar_events::removeListener( $this->name, $this->path, $this->capture, $this->id );
			}
		}

		/* FIXME: add a add() method, which re-adds the listener, potentially as last in the list */
	}

	class ar_eventsInstance extends arBase {
		private $path = '/';
		private $eventName = null;
		private $objectType = null;
		private $capture = false;
		private $filters = array();

		public function __construct( $path, $eventName = null, $objectType = null, $capture = false ) {
			$this->path = $path;
			$this->setEventProperties( $eventName, $objectType, $capture);
		}

		public function call( $method, $args = array() ) {
			if ( is_string($method) ) {
				$method = ar_pinp::getCallback( $method, array_keys($args) );
			}
			if ( !( $method instanceof \Closure ) ) {
				return ar_error::raiseError('Illegal event listener method', 500);
			}
			return ar_events::addListener( $this->path, $this->eventName, $this->objectType, $method, $args, $this->capture, $this->filters );
		}

		public function listen( $eventName, $objectType = null ) {
			$this->setEventProperties( $eventName, $objectType, false);
			return $this;
		}

		public function capture( $eventName, $objectType = null ) {
			$this->setEventProperties( $eventName, $objectType, true);
			return $this;
		}

		public function match( $filter ) {
			$this->filters[] = $filter;
			return $this;
		}

		public function fire($eventName, $eventData, $objectType = null) {
			return ar_events::fire($eventName, $eventData, $objectType = null, $this->path);
		}

		private function setEventProperties($eventName, $objectType, $capture) {
			$this->eventName = $eventName;
			$this->objectType = $objectType;
			$this->capture = (bool)$capture;
		}
	}

	class ar_eventsEvent extends arBase {
		public $data = null;
		private $name = '';
		private $type = null;
		private $preventDefault = false;
		private $path = null;
		private $target = null;

		public function __construct( $name, $data = null, $type = null, $path = null, $target = null ) {
			$this->name = $name;
			$this->data = $data;
			$this->type = $type;
			$this->path = $path;
			$this->target = $target;
		}

		public function preventDefault() {
			$this->preventDefault = true;
			return false;
		}

		public function __get( $name ) {
			if ( in_array($name, array('preventDefault','name','type','path','target') ) ) {
				return $this->{$name};
			}
		}

	}
