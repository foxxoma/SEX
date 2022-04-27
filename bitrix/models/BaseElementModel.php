<?php
namespace SF\Model;

use SF\Helper\Format;
use SF\Helper\DefaultFormats;

class BaseElementModel
{
	protected $element = [];
	protected $elementFields = [];
	protected $elementId = null;
	protected $elementList= [];
	protected $format = [];

	protected $sort = [];
	protected $filter = [];
	protected $select = [];
	protected $groupBy = false;

	protected $elementsCount = 0;

	protected $pagination = false;

	protected $IBLOCK_ID = false;

	public function __construct($arguments = [])
	{
		$this->format = DefaultFormats::base();
		$this->newElement($arguments);
	}

	public static function __callStatic($name, $arguments)
	{
		$class = new static();
		$name = $name.'Action';

		if (method_exists($class, $name))
			return $class->$name(...$arguments);
	}

	public function __call($name, $arguments)
	{
		$name = $name.'Action';
		if (method_exists($this, $name))
			return $this->$name(...$arguments);
	}

	public function newElement(array $arguments)
	{
		$this->elementFields = $arguments;
		$this->element = new \CIBlockElement();
	}

	public function countAction()
	{
		return $this->elementsCount;
	}

	public function whereAction($property, $operator, $value)
	{
		$this->filter[$operator.$property] = $value;

		return $this;
	}

	public function filterAction(array $filter)
	{
		$this->filter = array_merge($filter, $this->filter);

		return $this;
	}

	public function orderByAction($property, $sort = "asc")
	{
		if (!is_array($property))
			$this->sort[$property] = $sort;
		else
			$this->sort = $property;

		return $this;
	}

	public function selectAction(array $props)
	{
		$this->select = $props;

		return $this;
	}

	public function formatAction(array $format)
	{
		$this->format = $format;

		return $this;
	}

	public function takeAction($count)
	{
		$pagination = ['checkOutOfRange' => true, 'iNumPage'=> 1, 'nPageSize' => $count];
		$this->pagination = $pagination;

		return $this;
	}

	public function pagenateCustomAction(array $arr)
	{
		$this->pagination = $arr;

		return $this;
	}

	public function pagenateAction($page, $count)
	{
		$pagination = ['checkOutOfRange' => true, 'iNumPage'=> $page, 'nPageSize' => $count];
		$this->pagination = $pagination;

		return $this;
	}

	public function firstAction($id = null)
	{
		if ($id)
			$this->where('ID', '=', $id);

		$this->select(['ID']);

		$this->setElementList();

		if (empty($this->elementList))
			return false;

		$this->elementId = $this->elementList->Fetch()['ID'];

		$this->select([]);
		$this->setElementList();

		return $this;
	}

	public function fetchAction()
	{
		$this->setElementList();

		if (empty($this->elementList))
			return false;

		$result = [];

		while($item = $this->elementList->Fetch())
		{
			$result[] = $item;
		}

		return $result;
	}

	public function getAction()
	{
		$this->setElementList();

		if (empty($this->elementList))
			return false;

		$result = [];

		$this->elementsCount = $this->elementList->NavRecordCount;

		while($item = $this->elementList->GetNextElement())
		{
			$element = $item->GetFields();
			$element['PROPERTIES'] = $item->getProperties();
			$result[] = Format::item($this->format, $element);
		}

		return $result;
	}

	public function toArray()
	{
		$rElement = $this->elementList->GetNextElement();

		if (is_bool($rElement))
		{
			$this->setElementList();
			$rElement = $this->elementList->GetNextElement();

			if (empty($rElement))
				return [];
		}

		$element = $rElement->GetFields();
		$element['PROPERTIES'] = $rElement->getProperties();

		return Format::item($this->format, $element);
	}

	public function setFields(array $arguments)
	{
		$this->elementFields = array_merge($arguments, $this->elementFields);
		return $this;
	}

	public function saveAction()
	{
		if (!empty($this->elementId))
			return $this->update();

		$this->elementFields['FIELDS']['IBLOCK_ID'] = $this->IBLOCK_ID;

		if($id = $this->element->Add($this->elementFields['FIELDS']));
		{
			$this->where('ID', '=', $id);
			$this->elementId = $id;

			$this->savePropertiesAction();
			$this->setElementList();

			return true;
		}

		return $this->element->LAST_ERROR;
	}

	public function update()
	{
		$this->element->Update($this->elementId, $this->elementFields['FIELDS']);
		$this->savePropertiesAction();
		$this->setElementList();

		return true;
	}

	public function deleteAction($id = null)
	{
		if ($id != null)
			$this->elementId = $id;

		if (empty($this->elementId))
			return false;

		$this->element = new \CIBlockElement;
		$this->element->delete($this->elementId);

		return true;
	}

	public function groupBy($data)
	{
		$this->groupBy = $data;

		return $this;
	}

	public function savePropertiesAction()
	{
		if (empty($this->elementFields['PROPERTY_VALUES']))
			return;

		\CIBlockElement::SetPropertyValuesEx(
			$this->elementId,
			$this->IBLOCK_ID,
			$this->elementFields['PROPERTY_VALUES']
		);
	}

	public function belong(BaseElementModel $model, string $foreignKey, string $internalKey = 'ID')
	{
		if (!$this->elementId)
			return false;

		$properties = $this->elementList->GetNextElement()->getProperties();
		if (!empty($properties[$foreignKey]['VALUE']))
			return $model->where($internalKey, '=', $properties[$foreignKey]['VALUE']);

		return false;
	}

	public function has(BaseElementModel $model, string $foreignKey, string $internalKey = 'ID')
	{
		if (!$this->elementId)
			return false;

		$value = $this->elementId;

		if ($internalKey != 'ID')
		{
			$properties = $this->elementList->GetNextElement()->getProperties();
			if (empty($properties[$internalKey]['VALUE']))
				return false;

			$value = $properties[$internalKey]['VALUE'];
		}

		return $model->where('PROPERTY_' . $foreignKey , '=', $value);
	}

	public function setElementList()
	{
		$this->elementList = \CIBlockElement::GetList(
			$this->sort,
			$this->filter,
			$this->groupBy,
			$this->pagination,
			$this->select
		);
	}
}
