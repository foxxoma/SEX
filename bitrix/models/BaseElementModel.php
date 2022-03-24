<?php
namespace Module;

use Module\Format;
use Module\DefaultFormats;

class BaseElementModel
{
	protected $element = [];
	protected $elementFields = [];
	protected $format = [];

	protected $sort = [];
	protected $filter = [];
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

	public function newElement($arguments)
	{
		$this->elementFields = $arguments;
		$this->element = new \CIBlockElement();
	}

	public function whereAction($property, $operator, $value)
	{
		$this->filter[$operator.$property] = $value;

		return $this;
	}

	public function filterAction($filter)
	{
		$this->filter = array_merge($filter, $this->filter);

		return $this;
	}

	public function orderByAction($property, $sort = "asc")
	{
		$this->sort[$property] = $sort;

		return $this;
	}

	public function takeAction($count)
	{
		$pagination = ['checkOutOfRange' => true, 'iNumPage'=> 1, 'nPageSize' => $count];
		$this->pagination = $pagination;

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

		$this->setElement();

		if (empty($this->element))
			return false;

		$this->elementFields['ID'] = $id;

		return $this;
	}

	public function getAction()
	{
		$this->setElement();

		$result = [];

		while($item = $this->element->GetNextElement())
		{
			$element = $item->GetFields();
			$element['PROPERTIES'] = $item->getProperties();

			$result[] = Format::item($this->format, $element);
		}

		return $result;
	}

	public function toArray()
	{
		$rElement = $this->element->GetNextElement();
		$element = $rElement->GetFields();
		$element['PROPERTIES'] = $rElement->getProperties();

		return Format::item($this->format, $element);
	}

	public function setFields($arguments)
	{
		$this->elementFields = array_merge($arguments, $this->elementFields);
		return $this;
	}

	public function saveAction()
	{
		if (!empty($this->elementFields['ID']))
			return $this->update();

		$this->elementFields['FIELDS']['IBLOCK_ID'] = $this->IBLOCK_ID;

		if($id = $this->element->Add($this->elementFields['FIELDS']));
		{
			$this->where('ID', '=', $id);
			$this->elementFields['ID'] = $id;
			$this->savePropertiesAction();

			return true;
		}	

		return false;
	}

	public function update()
	{
		$this->element = new \CIBlockElement;

		$this->element->Update($this->elementFields['ID'], $this->elementFields['FIELDS']);

		$this->savePropertiesAction();

		return true;
	}

	public function deleteAction($id = null)
	{
		if ($id != null)
			$this->elementFields['ID'] = $id;

		if (empty($this->elementFields['ID']))
			return false;

		$this->element = new \CIBlockElement;
		$this->element->delete($this->elementFields['ID']);

		return true;
	}

	public function savePropertiesAction()
	{
		if (empty($this->elementFields['PROPERTIES_VALUES']))
			return;

		\CIBlockElement::SetPropertyValuesEx(
			$this->elementFields['ID'],
			$this->IBLOCK_ID,
			$this->elementFields['PROPERTIES_VALUES']
		);
	}

	public function setElement()
	{
		$this->element = \CIBlockElement::GetList(
			$this->sort,
			$this->filter,
			false,
			$this->pagination
		);
	}
}
