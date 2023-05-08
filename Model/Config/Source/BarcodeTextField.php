<?php

namespace Akwaaba\Barcode\Model\Config\Source;

class BarcodeTextField implements \Magento\Framework\Data\OptionSourceInterface
{
	 public function toOptionArray()
	 {
	  return [
		  ['value' => 'name', 'label' => __('Product Name')],
		  ['value' => 'sku',  'label' => __('Product Sku')]
		 ];
 	 }
}
