<?php
namespace SF\Model;

use SF\Helper\Format;
use SF\Helper\DefaultFormats;

class BaseSectionModel
{
	protected $section = [];
	protected $sectionFields = [];
	protected $sectionId = null;
	protected $sectionList = [];
	protected $format = [];

	protected $sort = [];
	protected $filter = [];
	protected $select = ['*', 'UF_*'];

	protected $pagination = false;

	protected $IBLOCK_ID = false;

	public function __construct($arguments = [])
	{
		$this->format = DefaultFormats::baseSection();
		$this->newSection($arguments);
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

	public function newSection(array $arguments)
	{
		$this->sectionFields = $arguments;
		$this->section = new \CIBlockSection();
	}

	public function whereAction($property, $operator, $value)
	{
		$value = $this->valueIntersection($property, $operator, $value);

		$this->filter[$operator.$property] = $value;

		return $this;
	}

	public function valueIntersection($property, $operator, $value)
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

		return $value;
	}

	public function filterAction(array $filter)
	{
		foreach($filter as $prop => $value)
			$this->where($prop, '', $value);

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

	public function paginateAction($page, $count)
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

		$this->setSectionList();

		if (empty($this->sectionList))
			return false;

		$this->sectionId = $this->sectionList->Fetch()['ID'];

		if (empty($this->sectionId))
			return false;

		$this->select(['*', 'UF_*']);
		$this->setSectionList();

		return $this;
	}

	public function getAction()
	{
		$this->setSectionList();

		if (empty($this->sectionList))
			return false;

		$result = [];

		while($item = $this->sectionList->GetNextElement())
		{
			$section = $item->GetFields();
			$result[] = Format::item($this->format, $section);
		}

		return $result;
	}

	public function toArray()
	{
		$rSection = $this->sectionList->GetNextElement();

		if (is_bool($rSection))
		{
			$this->setSectionList();
			$rSection = $this->sectionList->GetNextElement();

			if (empty($rSection))
				return [];
		}

		$section = $rSection->GetFields();

		return Format::item($this->format, $section);
	}

	public function treeAction()
	{
		$list = $this->get();
		return $this->buildTree($list);
	}

	public function buildTree(array $elements, $parentId = 0, $parent_key = 'parent', $idKey = 'id')
	{
		$branch = [];
		
		foreach ($elements as $element)
		{
			if ($element[$parent_key] == $parentId)
			{
				$child = $this->buildTree($elements, $element[$idKey], $parent_key, $idKey);

				if ($child)
					$element['children'] = $child;

				$branch[] = $element;
			}
		}
		
		return $branch;
	}

	public function setFields(array $arguments)
	{
		$this->sectionFields = array_merge($arguments, $this->sectionFields);
		return $this;
	}

	public function saveAction()
	{
		if (!empty($this->sectionId))
			return $this->update();

		$this->sectionFields['IBLOCK_ID'] = $this->IBLOCK_ID;

		if($id = $this->section->Add($this->sectionFields));
		{
			$this->where('ID', '=', $id);
			$this->sectionId = $id;

			$this->setSectionList();

			return true;
		}

		return false;
	}

	public function update()
	{
		$this->section->Update($this->sectionId, $this->sectionFields);
		$this->setSectionList();

		return true;
	}

	public function deleteAction($id = null)
	{
		if ($id != null)
			$this->sectionId = $id;

		if (empty($this->sectionId))
			return false;

		$this->section = new \CIBlockSection;
		$this->section->delete($this->sectionId);

		return true;
	}

	public function belong(BaseSectionModel $model, string $foreignKey = 'IBLOCK_SECTION_ID', string $internalKey = 'ID')
	{
		if (!$this->sectionId)
			return false;

		$fields = $this->sectionList->Fetch();
		if (!empty($fields[$foreignKey]))
			return $model->where($internalKey, '=', $fields[$foreignKey]);

		return false;
	}

	public function has(BaseSectionModel $model, string $foreignKey = 'SECTION_ID', string $internalKey = 'ID')
	{
		if (!$this->sectionId)
			return false;

		$value = $this->sectionId;

		if ($internalKey != 'ID')
		{
			$fields = $this->sectionList->Fetch();
			if (empty($fields[$internalKey]))
				return false;

			$value = $fields[$internalKey];
		}

		return $model->where($foreignKey , '=', $value);
	}

	public function setSectionList()
	{
		$this->sectionList = \CIBlockSection::GetList(
			$this->sort,
			$this->filter,
			false,
			$this->select,
			$this->pagination
		);
	}
}
