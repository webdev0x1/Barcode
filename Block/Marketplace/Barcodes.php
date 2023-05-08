<?php
namespace Akwaaba\Barcode\Block\Marketplace;
class Barcodes extends \Magento\Framework\View\Element\Template
{
    function _prepareLayout(){}

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;
    protected $productCollection;

    public function __construct(\Magento\Framework\View\Element\Template\Context $context, \Magento\Framework\Registry $registry, \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection) {
        $this->_coreRegistry  = $registry;
        $this->productCollection = $productCollection;
        parent::__construct($context);
    }

    public function getCurrentSeller()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerSession = $objectManager->create('Magento\Customer\Model\Session');
        if ($customerSession->isLoggedIn()) {
            $customerId = $customerSession->getId();
           	$sellerDatas = $objectManager->get ( 'Lof\MarketPlace\Model\Seller' )->load ( $customerId, 'customer_id' );
            $id = $sellerDatas ->getId();
            return $id;
        }
    }

    public function sellerProductCollection() {
        $sellerId = $this->getCurrentSeller();
        return $collection = $this->collection($sellerId);
    }

    public function collection($seller_id) {
        $productCollection = $this->productCollection;
       $collection = $productCollection->create()
           ->addAttributeToSelect('*')
           ->addAttributeToFilter('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
           ->load();

       $collection->getSelect()->join(

               ['lof_marketplace_seller'],
              'lof_marketplace_seller.seller_id = '.$seller_id,
               []
       );
       return $collection;
   }
}