<?php

namespace SF\Helper;
//класс для форматирования
class Format
{
	public static function item($format, $response)
	{
		$result = [];

		foreach($format as $f_key => $prop)
			$result[$f_key] = self::firstLevel($prop, $response);

		return $result;
	}

	public static function firstLevel($prop, $response)
	{
		if (is_array($prop))
			return self::arrayLevel($prop, $response);

		if (!isset($response[$prop]) && !is_array($response))
			return $response;

		return $response[$prop];
	}

	public static function arrayLevel($prop, $response)
	{
		$result = [];

		if (!empty($prop['list']))
			return self::listLevel($prop, $response);

		if(is_array($prop['props']))
		{
			foreach($prop['props'] as $props_prop_key => $prop_name)
				$result[$props_prop_key] = self::firstLevel($prop_name, $response[$prop['alias']]);

			return $result;
		}

		return self::firstLevel($prop['props'], $response[$prop['alias']]);
	}
	
	public static function listLevel($prop, $response)
	{
		$result = [];

		if(is_array($prop['props']))
			return self::fourthLevel($prop, $response);

		foreach($response[$prop['alias']] as $r_prop)
		{
			$r_prop = self::checkPropFile($r_prop);

			if($prop['key'])
			{
				$arrayKey = $r_prop[$prop['key']];

				if (is_array($prop['list']))
					if (!in_array($arrayKey, $prop['list']))
						continue;

				if(ctype_upper(str_replace(['-','_'], ' ', $arrayKey)) || stristr($arrayKey, '_') || stristr($arrayKey, '-'))
				{
					$arrayKey = strtolower($arrayKey);
					$arrayKey = ucwords(str_replace(['-','_'], ' ', $arrayKey));
					$arrayKey = lcfirst(str_replace(' ', '', $arrayKey));
				}

				$result[$arrayKey] = self::firstLevel($prop['props'], $r_prop);
			}
			else
				$result[] = self::firstLevel($prop['props'], $r_prop);
		}

		return $result;
	}

	public static function fourthLevel($prop, $response)
	{
		$result = [];

		foreach($prop['props'] as $props_prop_key => $prop_name)
		{
			$i = 0;
			foreach($response[$prop['alias']] as $r_prop)
			{
				$r_prop = self::checkPropFile($r_prop);

				if($prop['key'])
				{
					$arrayKey = $r_prop[$prop['key']];

					if (is_array($prop['list']))
						if (!in_array($arrayKey, $prop['list']))
							continue;

					if(ctype_upper(str_replace(['-','_'], ' ', $arrayKey)) || stristr($arrayKey, '_') || stristr($arrayKey, '-'))
					{
						$arrayKey = strtolower($arrayKey);
						$arrayKey = ucwords(str_replace(['-','_'], ' ', $arrayKey));
						$arrayKey = lcfirst(str_replace(' ', '', $arrayKey));
					}


					if ($props_prop_key == '#empty')
						$result[$arrayKey] = self::firstLevel($prop_name, $r_prop);
					else
						$result[$arrayKey][$props_prop_key] = self::firstLevel($prop_name, $r_prop);
				}
				else
				{
					if ($props_prop_key == '#empty')
						$result[$i] = self::firstLevel($prop_name, $r_prop);
					else
						$result[$i][$props_prop_key] = self::firstLevel($prop_name, $r_prop);
				}

				$i++;
			}
		}

		return $result;
	}


	public static function checkPropFile($r_prop)
	{
		if (array_key_exists('PROPERTY_TYPE', $r_prop))
			if ($r_prop['PROPERTY_TYPE'] == 'F')
			$r_prop['VALUE'] = self::getImagePath($r_prop['VALUE']);

		return $r_prop;
	}

	/**
	 * @param $prop | imagesId
	 */
	public static function getImagePath($prop ,$with = 0, $height = 0)
	{
		if(!is_array($prop))
		{
			if(empty($with))
				return \CFile::GetPath($prop);

			return \CFile::ResizeImageGet($prop, ['with' => $with, 'height' => $height])['src'];
		}

		if(empty($with))
		{
			foreach($prop as &$img)
				$img =  \CFile::GetPath($img);

			return $prop;
		}

		foreach($prop as &$img)
			$img =  \CFile::ResizeImageGet($img, ['with' => $with, 'height' => $height])['src'];

		return $prop;
	}

	public static function toCamelCase($array)
	{
		$result = [];

		foreach($array as $propName => $prop)
		{

			if(ctype_upper(str_replace(['-','_'], ' ', $propName)) || stristr($propName, '_') || stristr($propName, '-'))
			{
				$propName = strtolower($propName);
				$propName = ucwords(str_replace(['-','_'], ' ', $propName));
				$propName = lcfirst(str_replace(' ', '', $propName));
			}

			if(is_array($prop) || is_object($prop))
				$result[$propName] = self::toCamelCase($prop);
			else
				$result[$propName] = $prop;
		}

		return $result;
	}
}
