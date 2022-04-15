<?php

namespace SF\Helper;

class Points
{
	public static $currency = "RUR";

	public static function payOrder($orderId, $price)
	{
		global $USER;

		if(!$USER->IsAuthorized())
			return false;

		$orderPrice = self::getOrderPrice($orderId, false);

		if (empty($orderPrice))
			return false;

		$maxPrice = $orderPrice * MAX_PERCENT_ORDER_PAY;

		if ($price > $maxPrice)
			$price = (int) $maxPrice;

		$myPoints = self::get();

		if ($price > $myPoints)
			$price = (int) $myPoints;

		$withdrawSum = \CSaleUserAccount::Withdraw(
			$USER->GetID(),
			$price,
			self::$currency,
			$orderId,
		);

		if ($withdrawSum > 0)
		{
			$arFields = [
				"SUM_PAID" => $withdrawSum,
				"USER_ID" => $USER->GetID()
			];

			\CSaleOrder::Update($orderId, $arFields);

			return true;
		}

		return false;
	}

	public static function get()
	{
		global $USER;

		if(!$USER->IsAuthorized())
			return false;

		$points = \CSaleUserAccount::GetByUserID($USER->GetID(), self::$currency);

		if (empty($points))
		{
			self::create(DEFAULT_POINTS);
			$points = \CSaleUserAccount::GetByUserID($USER->GetID(), self::$currency);
		}

		return (int) $points['CURRENT_BUDGET'];
	}

	public static function create($points = 0)
	{

		global $USER;

		if(!$USER->IsAuthorized())
			return false;

		if(\CSaleUserAccount::GetByUserID($USER->GetID(), "RUB"))
			return false;

		$arFields = ["USER_ID" => $USER->GetID(), "CURRENCY" => self::$currency, "CURRENT_BUDGET" => $points];
		$accountID = \CSaleUserAccount::Add($arFields);  

		if (empty($accountID))
			return false;
		else
			return $accountID;
	}

	public static function update($orderId)
	{
		global $USER;

		if(!$USER->IsAuthorized())
			return false;

		$orderPrice = self::getOrderPrice($orderId);

		if (empty($orderPrice))
			return false;

		$percent = self::getPercent();

		if ($percent > 1)
			$percent = ($percent/100);

		$points = ($orderPrice * $percent);

		$result = \CSaleUserAccount::UpdateAccount(
			$USER->GetID(),
			$points,
			self::$currency,
			"points: " . $points,
			$orderId
		);

		if ($result)
			return true;

		return false;
	}

	public static function getRegulations($filter = [], $price = false)
	{
		$result = [];

		$filter['IBLOCK_ID'] = REGULATIONS_IBLOCK_ID;

		$regulations = \CIBlockElement::GetList(['PROPERTY_PRICE' => 'asc'],$filter);

		$value = ['PRICE' => 0];

		while($item = $regulations->GetNextElement())
		{
			$resultItem = $item->GetFields();
			$resultItem['PROPERTIES'] = $item->getProperties();

			$regPrice = (int)$resultItem['PROPERTIES']['PRICE']['VALUE'];

			if ($regPrice >= $value['PRICE'] && $regPrice <= $price)
			{
				$value['PRICE'] = $regPrice;
				$value['PERCENT'] = $resultItem['PROPERTIES']['PERCENT']['VALUE'];
			}

			$result[] = $resultItem;
		}

		if ($price === false)
			return $result;
		else
			return $value;
	}

	public static function getOrdersPrice()
	{
		global $USER;

		if(!$USER->IsAuthorized())
			return false;

		$rOrders = \Bitrix\Sale\Order::loadByFilter(
			[
				'filter'  => [
					'USER_ID' => $USER->GetID(),
					'PAYED' => 'Y'
				],
			]);

		if(empty($rOrders))
			return 0;

		$price = 0;

		foreach($rOrders as $order)
			$price += (int) $order->getField('PRICE');

		return $price;
	}

	public static function getPercent()
	{
		global $USER;

		if(!$USER->IsAuthorized())
			return false;

		$ordersPrice = self::getOrdersPrice();;

		$regulations = self::getRegulations($filter, $ordersPrice);

		$percent = $regulations['PERCENT'];

		return $percent;
	}

	public static function getOrderPrice($id, $payed = true)
	{
		global $USER;

		if(!$USER->IsAuthorized())
			return false;

		$rOrders = \Bitrix\Sale\Order::loadByFilter(
			[
				'filter'  => [
					'ID' => $id,
					'USER_ID' => $USER->GetID(),
					'PAYED' => $payed?'Y':'N'
				],
			]);

		if(empty($rOrders))
			return 0;

		$price = 0;

		foreach($rOrders as $order)
			$price += (int) $order->getField('PRICE');

		return $price;
	}
}
