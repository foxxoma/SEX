<?php
namespace Odva\Module\Model;

use Odva\Module\Format;
use Odva\Module\DefaultFormats;
use Odva\Module\Model\BaseElementModelTest;

class BaseElement implements \JsonSerializable
{
	protected $id = null;
	protected $iblockId = false;
	protected $fields = [];
	protected $format = [];
	protected $select = [];
	protected $BaseElementModelTest = false;

	public function __construct($arguments = [])
	{
		// $this->iblockId = PRODUCT_OFFER_IBLOCK_ID;
		//check if new
		$this->BaseElementModelTest = new BaseElementModelTest($this, $this->iblockId);

		if(!empty($arguments))
			$this->setCElementResult($arguments);
	}

	public function __set($prop, $value)
	{
		$this->fields[$prop] = $value;
	}

	public function __get($prop)
	{
		return $this->fields[$prop];
	}

	public function jsonSerialize()
	{
		return Format::toCamelCase($this->toArray());
	}

	public function __call($name, $arguments)
	{
		$name = $name.'Action';
		if (method_exists($this, $name))
			return $this->$name(...$arguments);
		elseif (method_exists($this->BaseElementModelTest, $name))
			return $this->BaseElementModelTest->$name(...$arguments);
	}

	public static function __callStatic($name, $arguments)
	{
		$class = new static();
		$name = $name.'Action';
		
		if (method_exists($class, $name))
			return $class->$name(...$arguments);
		elseif (method_exists($class->BaseElementModelTest, $name))
			return $class->BaseElementModelTest->$name(...$arguments);
	}

	public function toArray()
	{
		return $this->fields;
	}

	public function setFields(array $arguments)
	{
		$this->fields = array_merge($this->fields, $arguments);
		return $this;
	}

	public function update($arguments = [])
	{
		if (empty($this->id))
			$this->save();

		if (!empty($arguments))
			$this->setFields($arguments);

		$CElement = new \CIBlockElement();
		$CElement->Update($this->id, $this->fields);

		$this->savePropertiesAction();
		$this->setCElementGetList();

		return true;
	}

	public function savePropertiesAction()
	{
		if (empty($this->fields['PROPERTIES']))
			return;

		\CIBlockElement::SetPropertyValuesEx(
			$this->id,
			$this->iblockId,
			$this->fields['PROPERTIES']
		);
	}

	public function saveAction()
	{
		if (!empty($this->id))
			return $this->update();

		$this->fields['IBLOCK_ID'] = $this->iblockId;

		$CElement = new \CIBlockElement();
		$id = $CElement->Add($this->fields);

		if(!$id)
			return $CElement->LAST_ERROR;

		$this->id = $id;

		$this->savePropertiesAction();
		$this->setCElementGetList();

		return $id;
	}

	public function deleteAction()
	{
		if (empty($this->id))
			return false;

		$CElement = new \CIBlockElement;
		$CElement->delete($this->id);

		return true;
	}

	public function setCElementResult($cResult)
	{
		$result = [];

		$fields = $cResult->GetFields();
		$properties = $cResult->GetProperties();

		$this->id = $fields['ID']??null;

		foreach ($fields as $fieldKey => $field)
			if (strripos($fieldKey, '~') === false)
				$result[$fieldKey] = $field;

		$result['PROPERTIES'] = [];

		foreach ($properties as $property)
				$result['PROPERTIES'][$property['CODE']] = $property['VALUE'];

		$this->setFields($result);
	}

	public function belong(BaseElement $model, string $foreignKey, string $internalKey = 'ID')
	{
		if (!$this->id)
			return false;

		$properties = $this->fields['PROPERTIES'];
		if (!empty($properties[$foreignKey]))
			return $model->where($internalKey, '=', $properties[$foreignKey]);

		return false;
	}

	public function has(BaseElement $model, string $foreignKey, string $internalKey = 'ID')
	{
		if (!$this->id)
			return false;

		$value = $this->id;

		if ($internalKey != 'ID')
		{
			$properties = $this->fields['PROPERTIES'];
			if (empty($properties[$internalKey]))
				return false;

			$value = $properties[$internalKey];
		}

		return $model->where('PROPERTY_' . $foreignKey , '=', $value);
	}

	public function whereBelong($filter, BaseElement $model, string $foreignKey, string $internalKey = 'ID')
	{

		$internal = $model->filter($filter)->select(['ID', $internalKey])->Fetch();
		$value = [];

		foreach($internal as $item)
			$value[] = $item[$internalKey];

		if (empty($value))
			$value = 'undefined';

		return $this->where('PROPERTY_' . $foreignKey , '=', $value);
	}

	public function whereHas($filter, BaseElement $model, string $foreignKey, string $internalKey = 'ID')
	{
		$foreignKey = 'PROPERTY_'.$foreignKey;

		$internal = $model->filter($filter)->select(['ID', $foreignKey])->Fetch();
		$value = [];

		foreach($internal as $item)
			$value[] = $item[$foreignKey.'_VALUE'];

		if (empty($value))
			$value = 'undefined';

		return $this->where($internalKey, '=', $value);
	}

	public function setCElementGetList()
	{
		$CGetList = \CIBlockElement::GetList(
			[],
			[
				'IBLOCK_ID' => $this->iblockId,
				'ID' => $this->id
			],
			false,
			false,
		);

		$this->setCElementResult($CGetList->GetNextElement());
	}
}
