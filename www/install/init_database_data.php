<?php
	global $store;

		$properties = array();
		$properties['state'] = array(
			'fields' => array(
				'value' => array(
					'type' => 'string',
					'size' => 16
				),
				'group' => array(
					'type' => 'string',
					'size' => 16
				),
				'operator' => array(
					'type' => 'string',
					'size' => 255
				)
			),
			'indexes' => array(
				0 => array( 0 => 'value'),
				1 => array( 0 => 'group'),
				2 => array( 0 => 'operator')
			)
		);

		$properties['name'] = array(
			'fields' => array(
				'value' => array(
					'type' => 'string',
					'size' => 255
				),
				'nls' => array(
					'type' => 'string',
					'size' => 4,
					'default' => 'none'
				)
			)
		);

		$properties['value'] = array(
			'fields' => array(
				'value' => array(
					'type' => 'string',
					'size' => 255
				)
			)
		);

		$properties['text'] = array(
			'fields' => array(
				'value' => array(
					'type' => 'string',
					'size' => 255
				),
				'nls' => array(
					'type' => 'string',
					'size' => 4,
					'default' => 'none'
				)
			)
		);

		$properties['locked'] = array(
			'fields' => array(
				'id' => array(
					'type' => 'string',
					'size' => 32
				),
				'duration' => array(
					'type' => 'number',
					'size' => 1
				)
			)
		);

		$properties['login'] = array(
			'fields' => array(
				'value' => array(
					'type' => 'string',
					'size' => 32
				)
			)
		);

		$properties['members'] = array(
			'fields' => array(
				'login' => array(
					'type' => 'string',
					'size' => 32
				)
			)
		);

		$properties['time'] = array(
			'fields' => array(
				'ctime' => array(
					'type' => 'number',
					'size' => 1
				),
				'mtime' => array(
					'type' => 'number',
					'size' => 1
				),
				'muser' => array(
					'type' => 'string',
					'size' => 32
				)
			),
			'indexes' => array(
				0 => array( 0 => 'ctime'),
				1 => array( 0 => 'mtime'),
				2 => array( 0 => 'muser')
			)
		);

		$properties['owner'] = array(
			'fields' => array(
				'value' => array(
					'type' => 'string',
					'size' => 32
				)
			)
		);

		$properties['custom'] = array(
			'fields' => array(
				'name' => array(
					'type' => 'string',
					'size' => 32
				),
				'value' => array(
					'type' => 'string',
					'size' => 255
				),
				'nls' => array(
					'type' => 'string',
					'size' => 4,
					'default' => 'none'
				)
			)
		);

		$properties['timeframe'] = array(
			'fields' => array(
				'start' => array(
					'type' => 'number',
					'size' => 1
				),
				'end' => array(
					'type' => 'number',
					'size' => 1
				)
			)
		);

		$properties['priority'] = array(
			'fields' => array(
				'value' => array(
					'type' => 'number',
					'size' => 1
				)
			)
		);

		$properties['article'] = array(
			'fields' => array(
				'start' => array(
					'type' => 'number',
					'size' => 1
				),
				'end' => array(
					'type' => 'number',
					'size' => 1
				),
				'display' => array(
					'type' => 'string',
					'size' => 50
				)
			)
		);

		$properties['published'] = array(
			'fields' => array(
				'value' => array(
					'type' => 'number',
					'size' => 1
				)
			)
		);

		$properties['address'] = array(
			'fields' => array(
				'street' => array(
					'type' => 'string',
					'size' => 50
				),
				'zipcode' => array(
					'type' => 'string',
					'size' => 6
				),
				'city' => array(
					'type' => 'string',
					'size' => 50
				),
				'state' => array(
					'type' => 'string',
					'size' => 50
				),
				'country' => array(
					'type' => 'string',
					'size' => 50
				)
			),
			'indexes' => array(
				0 => array( 0 => 'city', 1 => 'street' ),
				1 => array( 0 => 'zipcode' ),
				2 => array( 0 => 'country', 1 => 'state' )
			)
		);

		$properties['url'] = array(
			'fields' => array(
				'host' => array(
					'type' => 'string',
					'size' => 50
				),
				'port' => array(
					'type' => 'number',
					'size' => 1
				),
				'protocol' => array(
					'type' => 'string',
					'size' => 10
				)
			)
		);

		$properties['mimetype'] = array(
			'fields' => array(
				'type' => array(
					'type' => 'string',
					'size' => 20
				),
				'subtype' => array(
					'type' => 'string',
					'size' => 20
				)
			)
		);

		// create fulltext property (if fulltext search is supported)
		if ($store->is_supported("fulltext")) {
			$properties['fulltext'] = array(
				'fields' => array(
					'value' => array(
						'type' => 'text',
						'size' => 1
					),
					'nls' => array(
						'type' => 'string',
						'size' => 4,
						'default' => 'none'
					)
				)
			);
		}


		$properties['references'] = array(
			'fields' => array(
				'path' => array(
					'type' => 'string',
					'size' => 255
				)
			)
		);

		$cacheproperties['template'] = array(
			'fields' => array(
				'name' => array(
					'type' => 'string',
					'size' => 32
				),
				'value' => array(
					'type' => 'string',
					'size' => 255
				)
			)
		);

		$cacheproperties['objectref'] = array(
			'fields' => array(
				'name' => array(
					'type' => 'string',
					'size' => 32
				),
				'value' => array(
					'type' => 'string',
					'size' => 255
				)
			)
		);
?>
