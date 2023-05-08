<?php

namespace Akwaaba\Barcode\Model\Config\Source;

class BarcodeTypeField implements \Magento\Framework\Data\OptionSourceInterface
{
	 public function toOptionArray()
	 {
	  return [
		  ['value' => 'UPCA', 'label' => __('UPC-A')],
		  ['value' => 'UPCE',  'label' => __('UPC-E')],
		  ['value' => 'EAN13', 'label' => __('EAN-13')],
		  ['value' => 'EAN8', 'label' => __('EAN-8')],
		  ['value' => 'C39', 'label' => __('Code 39')],
		  ['value' => 'C128', 'label' => __('Code 128')],
		  ['value' => 'CODABAR', 'label' => __('Codabar')],
		  ['value' => 'I25', 'label' => __('Interleaved 2 of 5')],
		  ['value' => 'POSTNET', 'label' => __('PostNet')]
		 ];
 	 }
}
