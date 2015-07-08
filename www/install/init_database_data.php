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
				),
				'scope' => array(
					'type' => 'string',
					'size' => 64
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
				),
				'scope' => array(
					'type' => 'string',
					'size' => 64
				)
			)
		);

		$properties['value'] = array(
			'fields' => array(
				'value' => array(
					'type' => 'string',
					'size' => 255
				),
				'scope' => array(
					'type' => 'string',
					'size' => 64
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
				),
				'scope' => array(
					'type' => 'string',
					'size' => 64
				)
			)
		);

		$properties['locked'] = array(
			'fields' => array(
				'id' => array(
					'type' => 'string',
					'size' => 128
				),
				'duration' => array(
					'type' => 'number',
					'size' => 1
				),
				'scope' => array(
					'type' => 'string',
					'size' => 64
				)			)
		);

		$properties['login'] = array(
			'fields' => array(
				'value' => array(
					'type' => 'string',
					'size' => 128
				),
				'scope' => array(
					'type' => 'string',
					'size' => 64
				)
			)
		);

		$properties['members'] = array(
			'fields' => array(
				'login' => array(
					'type' => 'string',
					'size' => 128
				),
				'scope' => array(
					'type' => 'string',
					'size' => 64
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
					'size' => 128
				),
				'scope' => array(
					'type' => 'string',
					'size' => 64
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
					'size' => 128
				),
				'scope' => array(
					'type' => 'string',
					'size' => 64
				)
			)
		);

		$properties['custom'] = array(
			'fields' => array(
				'name' => array(
					'type' => 'string',
					'size' => 64
				),
				'value' => array(
					'type' => 'string',
					'size' => 255
				),
				'nls' => array(
					'type' => 'string',
					'size' => 4,
					'default' => 'none'
				),
				'scope' => array(
					'type' => 'string',
					'size' => 64
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
				),
				'scope' => array(
					'type' => 'string',
					'size' => 64
				)
			)
		);

		$properties['priority'] = array(
			'fields' => array(
				'value' => array(
					'type' => 'number',
					'size' => 1
				),
				'scope' => array(
					'type' => 'string',
					'size' => 64
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
				),
				'scope' => array(
					'type' => 'string',
					'size' => 64
				)
			)
		);

		$properties['published'] = array(
			'fields' => array(
				'value' => array(
					'type' => 'number',
					'size' => 1
				),
				'scope' => array(
					'type' => 'string',
					'size' => 64
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
				),
				'scope' => array(
					'type' => 'string',
					'size' => 64
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
				),
				'scope' => array(
					'type' => 'string',
					'size' => 64
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
				),
				'scope' => array(
					'type' => 'string',
					'size' => 64
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
					),
					'scope' => array(
						'type' => 'string',
						'size' => 64
					)
				)
			);
		}


		$properties['references'] = array(
			'fields' => array(
				'path' => array(
					'type' => 'string',
					'size' => 255
				),
				'scope' => array(
					'type' => 'string',
					'size' => 64
				)
			)
		);

		$properties['geo'] = array(
			'fields' => array(
				'lat' => array(
					'type' => 'decimal',
					'size' => '10,8'
				),
				'long' => array(
					'type' => 'decimal',
					'size' => '11,8'
				),
				'zoom' => array(
					'type' => 'number',
					'size' => 1
				),
				'scope' => array(
					'type' => 'string',
					'size' => 64
				)
			)
		);

		$properties['tags'] = array(
			'fields' => array(
				'value' => array(
					'type' => 'string',
					'size' => 64
				),
				'scope' => array(
					'type' => 'string',
					'size' => 64
				)
			)
		);

		$properties['number'] = array(
			'fields' => array(
				'value' => array(
					'type' => 'number',
					'size' => 1
				),
				'scope' => array(
					'type' => 'string',
					'size' => 64
				)
			)
		);

		$cacheproperties['template'] = array(
			'fields' => array(
				'name' => array(
					'type' => 'string',
					'size' => 64
				),
				'value' => array(
					'type' => 'string',
					'size' => 255
				),
				'scope' => array(
					'type' => 'string',
					'size' => 64
				)
			)
		);

		$cacheproperties['objectref'] = array(
			'fields' => array(
				'name' => array(
					'type' => 'string',
					'size' => 64
				),
				'value' => array(
					'type' => 'string',
					'size' => 255
				),
				'scope' => array(
					'type' => 'string',
					'size' => 64
				)
			)
		);
?>
