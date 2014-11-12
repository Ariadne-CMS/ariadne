<?php
	global $store;

		$properties = Array();
		$properties['state'] = Array(
			'fields' => Array(
				'value' => Array(
					'type' => 'string',
					'size' => 16
				),
				'group' => Array(
					'type' => 'string',
					'size' => 16
				),
				'operator' => Array(
					'type' => 'string',
					'size' => 255
				)
			),
			'indexes' => Array(
				0 => Array( 0 => 'value'),
				1 => Array( 0 => 'group'),
				2 => Array( 0 => 'operator')
			)
		);

		$properties['name'] = Array(
			'fields' => Array(
				'value' => Array(
					'type' => 'string',
					'size' => 255
				),
				'nls' => Array(
					'type' => 'string',
					'size' => 4,
					'default' => 'none'
				)
			)
		);

		$properties['value'] = Array(
			'fields' => Array(
				'value' => Array(
					'type' => 'string',
					'size' => 255
				)
			)
		);

		$properties['text'] = Array(
			'fields' => Array(
				'value' => Array(
					'type' => 'string',
					'size' => 255
				),
				'nls' => Array(
					'type' => 'string',
					'size' => 4,
					'default' => 'none'
				)
			)
		);

		$properties['locked'] = Array(
			'fields' => Array(
				'id' => Array(
					'type' => 'string',
					'size' => 32
				),
				'duration' => Array(
					'type' => 'number',
					'size' => 1
				)
			)
		);

		$properties['login'] = Array(
			'fields' => Array(
				'value' => Array(
					'type' => 'string',
					'size' => 32
				)
			)
		);

		$properties['members'] = Array(
			'fields' => Array(
				'login' => Array(
					'type' => 'string',
					'size' => 32
				)
			)
		);

		$properties['time'] = Array(
			'fields' => Array(
				'ctime' => Array(
					'type' => 'number',
					'size' => 1
				),
				'mtime' => Array(
					'type' => 'number',
					'size' => 1
				),
				'muser' => Array(
					'type' => 'string',
					'size' => 32
				)
			),
			'indexes' => Array(
				0 => Array( 0 => 'ctime'),
				1 => Array( 0 => 'mtime'),
				2 => Array( 0 => 'muser')
			)
		);

		$properties['owner'] = Array(
			'fields' => Array(
				'value' => Array(
					'type' => 'string',
					'size' => 32
				)
			)
		);

		$properties['custom'] = Array(
			'fields' => Array(
				'name' => Array(
					'type' => 'string',
					'size' => 32
				),
				'value' => Array(
					'type' => 'string',
					'size' => 255
				),
				'nls' => Array(
					'type' => 'string',
					'size' => 4,
					'default' => 'none'
				)
			)
		);

		$properties['timeframe'] = Array(
			'fields' => Array(
				'start' => Array(
					'type' => 'number',
					'size' => 1
				),
				'end' => Array(
					'type' => 'number',
					'size' => 1
				)
			)
		);

		$properties['priority'] = Array(
			'fields' => Array(
				'value' => Array(
					'type' => 'number',
					'size' => 1
				)
			)
		);

		$properties['article'] = Array(
			'fields' => Array(
				'start' => Array(
					'type' => 'number',
					'size' => 1
				),
				'end' => Array(
					'type' => 'number',
					'size' => 1
				),
				'display' => Array(
					'type' => 'string',
					'size' => 50
				)
			)
		);

		$properties['published'] = Array(
			'fields' => Array(
				'value' => Array(
					'type' => 'number',
					'size' => 1
				)
			)
		);

		$properties['address'] = Array(
			'fields' => Array(
				'street' => Array(
					'type' => 'string',
					'size' => 50
				),
				'zipcode' => Array(
					'type' => 'string',
					'size' => 6
				),
				'city' => Array(
					'type' => 'string',
					'size' => 50
				),
				'state' => Array(
					'type' => 'string',
					'size' => 50
				),
				'country' => Array(
					'type' => 'string',
					'size' => 50
				)
			),
			'indexes' => Array(
				0 => Array( 0 => 'city', 1 => 'street' ),
				1 => Array( 0 => 'zipcode' ),
				2 => Array( 0 => 'country', 1 => 'state' )
			)
		);

		$properties['url'] = Array(
			'fields' => Array(
				'host' => Array(
					'type' => 'string',
					'size' => 50
				),
				'port' => Array(
					'type' => 'number',
					'size' => 1
				),
				'protocol' => Array(
					'type' => 'string',
					'size' => 10
				)
			)
		);

		$properties['mimetype'] = Array(
			'fields' => Array(
				'type' => Array(
					'type' => 'string',
					'size' => 20
				),
				'subtype' => Array(
					'type' => 'string',
					'size' => 20
				)
			)
		);

		// create fulltext property (if fulltext search is supported)
		if ($store->is_supported("fulltext")) {
			$properties['fulltext'] = Array(
				'fields' => Array(
					'value' => Array(
						'type' => 'text',
						'size' => 1
					),
					'nls' => Array(
						'type' => 'string',
						'size' => 4,
						'default' => 'none'
					)
				)
			);
		}


		$properties['references'] = Array(
			'fields' => Array(
				'path' => Array(
					'type' => 'string',
					'size' => 255
				)
			)
		);

		$cacheproperties['template'] = Array(
			'fields' => Array(
				'name' => Array(
					'type' => 'string',
					'size' => 32
				),
				'value' => Array(
					'type' => 'string',
					'size' => 255
				)
			)
		);

		$cacheproperties['objectref'] = Array(
			'fields' => Array(
				'name' => Array(
					'type' => 'string',
					'size' => 32
				),
				'value' => Array(
					'type' => 'string',
					'size' => 255
				)
			)
		);
?>