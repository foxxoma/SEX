<?php
namespace Module;

use Module\Format;
use Module\DefaultFormats;
use Module\BaseModel;
use Module\ParentProductModel;

class ProductModel extends BaseModel
{
	protected $format = [];
	protected $filter = ['IBLOCK_ID' => PRODUCT_OFFER_IBLOCK_ID];

	protected $IBLOCK_ID = PRODUCT_OFFER_IBLOCK_ID;

	public function __construct($arguments = [])
	{
		$this->format = DefaultFormats::product();
		$this->newElement($arguments);
	}

	public function getAction()
	{
		$this->setElementList();

		if (empty($this->elementList))
			return false;

		$result = [];

		while($item = $this->elementList->GetNextElement())
		{
			$product = $item->GetFields();
			$product['PROPERTIES'] = $item->getProperties();
			$product['PRICE'] = \CPrice::GetBasePrice((int)$product['ID']);

			$result[] = Format::item($this->format, $product);
		}

		return $result;
	}

	public function parentAction()
	{
		return $this->belong(new ParentProductModel(), 'CML2_LINK');
	}
}
