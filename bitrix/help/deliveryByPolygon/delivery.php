<?php

namespace SF\Module;

use SF\Module\Polygon;

class Delivery
{
	// point: [lat, lng]
	public static function getPrice($point, $orderPrice)
	{
		$poligonPrice = self::getPriceForPoligon($point, $orderPrice);
		if ($poligonPrice !== false)
			return (int) $poligonPrice;

		return false;
	}

	public static function getPriceForPoligon($point, $orderPrice)
	{
		$poligon = self::getIsInPoligon($point);
		if (empty($poligon))
			return false;

		$distance = false;
		if ($poligon['PRICE_TYPE'] == 'KM')
			$distance = self::getDistanceInPoligon($poligon, $point);

		$holiday = self::getHolidayForPoligon($poligon);
		if (!empty($holiday))
		{
			if ($poligon['PRICE_TYPE'] == 'KM')
				return $distance * $holiday['PRICE'];

			return $holiday['PRICE'];
		}

		if (!empty($poligon['FREE_PRICE']) && $orderPrice >= $poligon['FREE_PRICE'])
			return 0;

		if ($poligon['PRICE_TYPE'] == 'KM')
			return $distance * $poligon['PRICE'];

		return $poligon['PRICE'];
	}

	public static function getIsInPoligon($point)
	{
		$poligons = self::getPoligons();

		foreach($poligons as $polygon)
			if (Polygon::isInPoly($polygon['POLYGON'], $point))
				return $polygon;

		return false;
	}

	public static function getDistanceInPoligon($poligon, $point)
	{
		return Polygon::getDistance($poligon['STORE'], $point);
	}

	public static function getPoligons()
	{
		$result = [];
		$dbPoligons = \CIBlockElement::GetList(
			[],
			[
				'IBLOCK_ID' => DELIVERY_POLYGON_IBLOCK_ID,
				'ACTIVE'    => 'Y'
			],
			false,
			false,
			[
				'ID', 
				'PROPERTY_POLYGON',
				'PROPERTY_FREE_PRICE', 
				'PROPERTY_PRICE', 
				'PROPERTY_PRICE_TYPE',
				'PROPERTY_STORE'
			]
		);

		$i = 0;
		while($item = $dbPoligons->Fetch())
		{
			if (empty(json_decode($item['PROPERTY_POLYGON_VALUE'], true)))
				continue;
			if (empty(json_decode($item['PROPERTY_STORE_VALUE'], true)))
				continue;

			$result[$i]['ID'] = $item['ID'];
			$result[$i]['POLYGON'] = json_decode($item['PROPERTY_POLYGON_VALUE'], true);
			$result[$i]['FREE_PRICE'] = $item['PROPERTY_FREE_PRICE_VALUE'];
			$result[$i]['PRICE'] = $item['PROPERTY_PRICE_VALUE'];
			$result[$i]['PRICE_TYPE'] = $item['PROPERTY_PRICE_TYPE_VALUE'];
			$result[$i]['STORE'] = json_decode($item['PROPERTY_STORE_VALUE'], true);

			$i++;
		}

		return $result;
	}

	public static function getHolidayForPoligon($poligon)
	{
		$result = [];

		$holiday = \CIBlockElement::GetList(
			['id' => 'desc'],
			[
				'IBLOCK_ID' => DELIVERY_HOLIDAYS_IBLOCK_ID,
				'ACTIVE'    => 'Y',
				'PROPERTY_POLYGON_ID' => $poligon['ID'],
				'PROPERTY_DATE' => date("Y-m-d")
			],
			false,
			false,
			['ID', 'PROPERTY_PRICE']
		)->Fetch();

		if (empty($holiday))
			return false;

		$result = [
			'ID' => $holiday['ID'],
			'PRICE' => $holiday['PROPERTY_PRICE_VALUE']
		];

		return $result;
	}
}
