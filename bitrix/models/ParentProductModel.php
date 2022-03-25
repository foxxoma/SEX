<?php
namespace SF\Model;

use SF\Model\Helper\Format;
use SF\Model\Helper\DefaultFormats;

use SF\Model\Model\BaseElementModel;
use SF\Model\Model\ProductModel;

class ParentProductModel extends BaseElementModel
{
	protected $format = [];
	protected $filter = ['IBLOCK_ID' => PRODUCT_IBLOCK_ID];

	protected $IBLOCK_ID = PRODUCT_IBLOCK_ID;

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

	public function childrenAction()
	{
		return $this->has(new ProductModel(), 'CML2_LINK');
	}
}
