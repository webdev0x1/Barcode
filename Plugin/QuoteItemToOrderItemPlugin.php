<?php

namespace Akwaaba\Barcode\Plugin;

use Magento\Framework\Serialize\SerializerInterface;


class QuoteItemToOrderItemPlugin
{
    public function aroundConvert(\Magento\Quote\Model\Quote\Item\ToOrderItem $subject, callable $proceed, $quoteItem, $data)
    {

        // get order item
        $orderItem = $proceed($quoteItem, $data);

        // get your barcode from quote_item . 
        $barcode = $quoteItem->getBarcode();
        // get your barcode from quote_item . 
        $barcodeType = $quoteItem->getBarcodeType();
        //set barcode to sales_order_item
        $orderItem->setBarcode($barcode);
        $orderItem->setBarcodeType($barcodeType);

        return $orderItem;
    }
}