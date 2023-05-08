<?php
namespace Akwaaba\Barcode\Ui\DataProvider\Product\Form;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;

/**
 * DataProvider for product edit form
 *
 * @api
 * @since 101.0.0
 */
class ProductDataProvider extends \Magento\Catalog\Ui\DataProvider\Product\Form\ProductDataProvider
{
    /**
     * @var PoolInterface
     */
    private $pool;

    protected $request;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param PoolInterface $pool
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        PoolInterface $pool,
        \Magento\Framework\App\Request\Http $request,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $collectionFactory, $pool, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->request = $request;
        $this->pool = $pool;
    }
    
    /**
     * {@inheritdoc}
     * @since 101.0.0
     */
    public function getData()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('Magento\Store\Model\StoreManagerInterface'); 
        $currentStore = $storeManager->getStore();
        $mediaUrl = $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        /** @var ModifierInterface $modifier */
        foreach ($this->pool->getModifiersInstances() as $modifier) {
            $this->data = $modifier->modifyData($this->data);
        }
       
        //$this->data[$this->request->getParam('id', false)]['product']['custom_field'] = $img;
        //  print_r($this->data[$this->request->getParam('id', false)]['product']);
        //  die();
        return $this->data;
    }
}
