<?php
namespace Akwaaba\Barcode\Component;
class Inventory extends \Magento\Ui\Component\AbstractComponent
{
    const NAME = 'akwaaba_inventory';
    public function getComponentName()
    {
        return static::NAME;
    }
 
    //added this method
    public function getEvenMoreData()
    {
        return 'Even More Data!';
    }

    public function getDataSourceData()
    {
        return ['data' => $this->getContext()->getDataProvider()->getData()];
    }
} 