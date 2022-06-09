<?php

namespace SF\Module;

class Polygon
{
	public static $EARTH_RADIUS = 6378.137;
	public static $PI = 3.1415926;

	public static function getMin($a,$b)
	{
		return ($a<$b)?$a:$b;
	}

	public static function getMax($a,$b)
	{
		return ($a>$b)?$a:$b;
	}

	/**
	* Проверка вхождения координат в зону
	* параметры: polygon: [[lat, lng]...]; point: [lat, lng];
	* return true or false;
	*/
	public static function isInPoly($polygon, $point)
	{
		$i = 1;
		$N = count($polygon);
		$isIn = false;
		$p1 = $polygon[0];
		$p2 = null;

		for(;$i <= $N; $i++)
		{
			$p2 = $polygon[$i % $N];
			if ($point[0] > self::getMin($p1[0],$p2[0])) 
			{
				if ($point[0] <= self::getMax($p1[0],$p2[0])) 
				{
					if ($point[0] <= self::getMax($p1[0],$p2[0])) 
					{
						if ($p1[0] != $p2[0]) 
						{
							$xinters = ($point[0]-$p1[0])*($p2[1]-$p1[1])/($p2[1]-$p1[1])+$p1[1];
							if ($p1[1] == $p2[1] || $point[1] <= $xinters)
								$isIn = !$isIn;
						}
					}
				}
			}
			$p1 = $p2;
		}

		return $isIn;
	}

	/**
	* Рассчитать расстояние между двумя наборами координат широты и долготы
	* параметры: start: [lat, lng]; end: [lat, lng]; len_type: (1: м или 2: км);
	* return m or km
	*/
	public static function getDistance($start, $end, $len_type = 2, $decimal = 2)
	{
		$radLat1 = $start[1] * self::$PI / 180.0;
		$radLat2 = $end[1] * self::$PI / 180.0;
		$a = $radLat1 - $radLat2;
		$b = ($start[0] * self::$PI / 180.0) - ($end[0] * self::$PI / 180.0);
		$s = 2 * asin(sqrt(pow(sin($a/2),2) + cos($radLat1) * cos($radLat2) * pow(sin($b/2),2)));
		$s = $s * self::$EARTH_RADIUS;
		$s = round($s * 1000);

		if ($len_type > 1)
		   $s /= 1000;

		return round($s, $decimal);
	}
}
