<?php
namespace SF\Model;

use SF\Model\Model\BaseSectionModel;

class MenuModel extends BaseSectionModel
{
	protected $filter = ['IBLOCK_ID' => MENU_IBLOCK_ID];

	protected $IBLOCK_ID = MENU_IBLOCK_ID;

	public function parentAction()
	{
		return $this->belong(new static());
	}

	public function childrenAction()
	{
		return $this->has(new static());
	}
}
