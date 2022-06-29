<?php
namespace Odva\Module\Model;

use Odva\Module\Format;
use Odva\Module\DefaultFormats;

class BaseElementModelTest
{
	protected $iblockId = false;

	protected $sort = [];
	protected $filter = [];
	protected $select = [];
	protected $groupBy = false;

	protected $count = 0;
	protected $pagination = false;

	protected $elementModel = false;

	public function __construct($elementModel, $iblockId)
	{
		$this->elementModel = $elementModel;

		$this->iblockId = $iblockId;
		$this->filter = ['IBLOCK_ID' => $iblockId];
	}

	public function __call($name, $arguments)
	{
		$name = $name.'Action';
		if (method_exists($this, $name))
			return $this->$name(...$arguments);
	}

	public function createAction($arguments)
	{
		$element = new $this->elementModel();
		return $element->setFields($arguments);
	}

	public function filterAction(array $filter)
	{
		foreach($filter as $prop => $value)
			$this->whereAction($prop, '', $value);

		return $this;
	}

	public function whereAction($property, $operator, $value)
	{
		if (!is_array($value))
			$value = [$value];

		if (!empty($this->filter[$operator.$property]))
		{
			$value = array_intersect($this->filter[$operator.$property], $value);
		}
		elseif(!empty($this->filter['='.$property]) && $operator == '')
		{
			$operator = '=';
			$value = array_intersect($this->filter[$operator.$property], $value);
			unset($this->filter[$operator.$property]);
		}

		if (empty($value))
			$value = 'undefined';

		$this->filter[$operator.$property] = $value;

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

	public function pagenateArrAction(array $arr)
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

	public function groupBy($data)
	{
		$this->groupBy = $data;

		return $this;
	}

	public function firstAction($id = null)
	{
		if ($id)
			$this->whereAction('ID', '=', $id);

		$this->setCElementGetList();

		if (empty($this->CGetList))
			return false;

		return new $this->elementModel($this->CGetList->GetNextElement());
	}

	public function fetchAction()
	{
		$this->setCElementGetList();

		if (empty($this->CGetList))
			return false;

		$result = [];

		while($item = $this->CGetList->Fetch())
			$result[] = $item;

		return $result;
	}

	public function getCountAction()
	{
		return $this->count;
	}

	public function getAction()
	{
		$this->setCElementGetList();

		if (empty($this->CGetList))
			return false;

		$result = [];

		$this->count = $this->CGetList->NavRecordCount;

		while($item = $this->CGetList->GetNextElement())
			$result[] = new $this->elementModel($item);

		return $result;
	}

	public function deleteAction($id)
	{
		$CElement = new \CIBlockElement;
		$CElement->delete($id);

		return true;
	}

	public function setCElementGetList()
	{
		$this->CGetList = \CIBlockElement::GetList(
			$this->sort,
			$this->filter,
			$this->groupBy,
			$this->pagination,
			$this->select
		);
	}
}
