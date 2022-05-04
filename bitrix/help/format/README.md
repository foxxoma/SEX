# FORMAT
```php
	
//DATA ____________________________________________________________________________________________________
	$data = [
		'ID' => 't_id',
		'NAME' => 't_name',
		'CHECKED' => 't_checked',
		'IGNORE' => 't_ignore',
		'PRICE' => [
			'PRICE' => 'p_price',
			'COUNT' => 'p_count',
			'IGNORE' => 'p_ignore'
		],
		'PROPERTIES' => [
			'THEFORM' => [
				'CODE' => 'i_code_THEFORM',
				'PRODUCT_QUANTITY' => 'i_product_quantity',
				'IGNORE' => 'i_ignore',
				'VALU' => [
					'TEXT' => 'i_text',
					'html' => 'i_html',
					'ignore' => 'i_ignore'
				]
			],
			'THEFORM2' => [
				'CODE' => 'i_code_THEFORM2',
				'PRODUCT_QUANTITY' => 'i_product_quantity',
				'IGNORE' => 'i_ignore',
				'VALU' => 'i_value2'
			],
			'THEFORM3' => [
				'CODE' => 'i_code_THEFORM3',
				'PRODUCT_QUANTITY' => 'i_product_quantity',
				'IGNORE' => 'i_ignore',
				'VALU' => 'i_value3'
			],
		]
	];

//FORMAT ____________________________________________________________________________________________________

	$format = [
		'id' => 'ID',
		'name' => 'NAME',
		'checked' => 'CHECKED',
		'price' => [
			'alias' => 'PRICE',
			'props' => [
				'amount' => 'PRICE',
				'count' => 'COUNT'
			]
		],
		'TEST' => [
			'list' => true,
			'alias' => 'PROPERTIES',
			'props' => 'VALU'
		],
		'properties' => [
			'list' => true,
			'alias' => 'PROPERTIES',
			'key' => 'CODE',
			'props' => 'VALU'
		],
		'properties1' => [
			'list' => true,
			'alias' => 'PROPERTIES',
			'key' => 'CODE',
			'props' => [
				'#empty' => [
					'alias' => 'VALU',
					'props' => 'TEXT'
				]
			]
		],
		'properties2' => [
			'list' => true,
			'alias' => 'PROPERTIES',
			'key' => 'CODE',
			'props' => [
				'code' => 'CODE',
				'valu' => 'VALU',
			]
		],
		'properties3' => [
			'list' => true,
			'alias' => 'PROPERTIES',
			'props' => [
				'code' => 'CODE',
				'valu' => 'VALU',
			]
		]
	];



