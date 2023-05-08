<?php
namespace Akwaaba\Barcode\Controller\Marketplace\BarcodesAjax;
use Magento\Framework\Controller\Result\JsonFactory;
 
class Index extends \Magento\Backend\App\Action
{
    protected $resultPageFactory;
    protected $productCollection;
    protected $storeManager;
    private $resultJsonFactory;
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        JsonFactory $resultJsonFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory)
    {
        $this->resultPageFactory = $resultPageFactory;    
        $this->productCollection = $productCollection;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->storeManager = $storeManager;
        parent::__construct($context);    
    }
    
    public function execute()
    {
        $postData = $this->getRequest()->getPost();
        $draw = $postData['draw'];
        $row = $postData['start'];
        $rowperpage = $postData['length']; // Rows display per page
        $columnIndex = $postData['order'][0]['column']; // Column index
        $columnName = $postData['columns'][$columnIndex]['data']; // Column name
        $columnSortOrder = $postData['order'][0]['dir']; // asc or desc
        $searchValue = $postData['search']['value']; // Search value


        $resultJson = $this->resultJsonFactory->create();
        $data = [];
        $currentStore = $this->storeManager->getStore();
        $mediaUrl = $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $collection = $this->sellerProductCollection();//->setCurPage(2)->setPageSize($rowperpage);
        $collectionCount = $collection->getSize();
        if(!empty($searchValue)) {
            $collection = $collection->addAttributeToFilter('name', array('like' => '%'.$searchValue.'%'));
            $collectionCount = $collection->getSize();
        }

        $collection = $collection->setOrder('created_at','desc');
        if(!empty($columnSortOrder)) {
            if($columnName == 'product_name')
                $collection = $collection->setOrder('name',$columnSortOrder);
            if($columnName == 'sku')
                $collection = $collection->setOrder('sku',$columnSortOrder);
            if($columnName == 'barcode')
                $collection = $collection->setOrder('barcode',$columnSortOrder);
            if($columnName == 'created_at')
                $collection = $collection->setOrder('created_at',$columnSortOrder);
        }
        
        if(!empty($row)) {
            $collection = $collection->setCurPage($row);
        }

        if(!empty($rowperpage)) {
            $collection = $collection->setPageSize($rowperpage);
        }
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $i = 1;
        foreach( $collection->getData() as $sellerProduct) {
            $product = $objectManager->get('Magento\Catalog\Model\Product')->load($sellerProduct['entity_id']);

            if($product->getBarcode()) {
               
                $data[] = array('product_name' => $product->getName(),
                                'sku' => $product->getSku(),
                                'barcode' => "<img width='220' src='".$mediaUrl.'barcodes/'.$product->getBarcode().".png' />",
                                'created_at' => $product->getCreatedAt());
                }
        }


        return $resultJson->setData(["draw" => intval($draw),"iTotalRecords" => $collectionCount,"iTotalDisplayRecords" => $collectionCount,'aaData' => $data]);
    }    
    protected function _isAllowed()
    {
        return true;
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
           ->addAttributeToFilter('barcode', array('neq' => ''))
           ->load();

       $collection->getSelect()->join(

               ['lof_marketplace_seller'],
              'lof_marketplace_seller.seller_id = '.$seller_id,
               []
       );
       return $collection;
   }
        
}