<?php
namespace Akwaaba\Barcode\Observer;

use Magento\Framework\Event\ObserverInterface;

class SetBarcodeAttribute implements ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quoteItem = $observer->getQuoteItem();
        $product = $observer->getProduct();
        $quoteItem->setBarcode($product->getData('barcode'));
        $quoteItem->setBarcodeType($product->getAttributeText('barcode_type'));
    }
}