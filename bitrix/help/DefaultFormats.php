<?php

namespace SF\Helper;

class DefaultFormats
{
	public static function product()
	{
		return [
			'id' => 'ID',
			'name' => 'NAME',
			'price' => [
				'alias' => 'PRICE',
				'props' => [
					'amount' => 'PRICE',
					'count' => 'PRODUCT_QUANTITY'
				]
			],
			'properties' =>
			[
				'alias' => 'PROPERTIES',
				'list' => true,
				'key' => 'CODE',
				'props' => 'VALUE'
			],
		];
	}

	public static function base()
	{
		return [
			'id' => 'ID',
			'name' => 'NAME',
			'properties' =>
			[
				'alias' => 'PROPERTIES',
				'list' => true,
				'key' => 'CODE',
				'props' => 'VALUE'
			],
		];
	}
}