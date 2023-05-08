<?php
namespace Akwaaba\Barcode\Controller\Marketplace\Inventory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Pricing\Helper\Data;

class Product extends \Magento\Backend\App\Action
{   
    protected $request;
    protected $resultPageFactory;
    private $resultJsonFactory;
    

    /**
     * @var \Magento\Framework\Json\Helper\Data $helper
     */
    protected $helper;

    /**
     * @var Json
     */
    protected $json;

    protected $productCollection;
    protected $product;
    protected $_stockFilter;
    protected $imageHelper;
    protected $_storeManager;
    protected $stockRegistry;
    protected $priceHelper;

    public function __construct(
        Data $priceHelper,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Request\Http $request,
        JsonFactory $resultJsonFactory,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Framework\Json\Helper\Data $helper,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Catalog\Model\Product $product,
        \Magento\CatalogInventory\Helper\Stock $stockFilter,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection,
        Json $json)
    {
        $this->priceHelper = $priceHelper;
        $this->_storeManager = $storeManager;
        $this->_stockFilter = $stockFilter; 
        $this->imageHelper = $imageHelper;
        $this->product = $product;
        $this->stockRegistry = $stockRegistry;
        $this->resultPageFactory = $resultPageFactory; 
        $this->request = $request;    
        $this->resultJsonFactory = $resultJsonFactory; 
        $this->json = $json;  
        $this->helper = $helper;
        $this->productCollection = $productCollection;
        return parent::__construct($context);
    }
    
    public function execute()
    {
        $content = explode('&', $this->getRequest()->getContent());
        if(isset($content[0]) && !empty($content)) {
            $requestParams = $this->helper->jsonDecode($content[0]);
            $json = json_encode($requestParams);
            $json = json_decode($json);
            
            if(isset($json->seller_id))
                $seller_id = $json->seller_id;
            else 
                return $resultJson->setData(['status' => 'error', 'message' => 'Seller id is required! Please refresh the page and try again!']);

            if(isset($json->barcodes) && !empty($json->barcodes))
                $barcodes = $json->barcodes;
            else 
                return $resultJson->setData(['status' => 'error', 'message' => 'Barcode is required! Please refresh the page and try again!']);
            
            if(isset($json->inventory) && !empty($json->inventory))
                $inventory = $json->inventory;
            else
                return $resultJson->setData(['status' => 'error', 'message' => 'Stock is required! Please choose increase/decrease!']);
            
            $collection = $this->collection($seller_id, $barcodes, $inventory);
            if(empty($collection)) {
                return $resultJson->setData(['status' => 'error', 'message' => 'Product is not Available!']);
            }
            $currentStore = $this->_storeManager->getStore();
            $imageW = 200;
            $imageH = 200;
            $productDetails = [];
            foreach($collection->getData() as $product) {
                //if(isset($json->product_qty) && isset($json->product_qty[$product['entity_id']])) {
                    $_product = $this->product->load($product['entity_id']);
                    $stockItem = $_product->getExtensionAttributes()->getStockItem();
                    $qty = $stockItem->getQty();
                    $isFirstLoad = true;
                    foreach($json->product_qty as $product_qty) {
                        if($product_qty->id == $product['entity_id']) {
                            $qty = $product_qty->qty;
                        }
                    }
                    $productDetails[$_product->getId()]['product_image_url'] = $this->imageHelper->init($_product, 'product_thumbnail_image')->getUrl();
                    $productDetails[$_product->getId()]['barcode_image_url'] = $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)."barcodes/".$_product->getBarcode().".png";
                    $productDetails[$_product->getId()]['product_name'] = $_product->getName();
                    $productDetails[$_product->getId()]['product_price'] = $this->priceHelper->currency($_product->getFinalPrice(), true, false);
                    
                    if($json->inventory == 'inc')
                        $productDetails[$_product->getId()]['product_qty'] = $qty+1;
                    
                    if($json->inventory == 'dec') {
                        if($qty != 0)
                            $productDetails[$_product->getId()]['product_qty'] = $qty-1;
                    }

                    // $stockItem = $this->stockRegistry->getStockItemBySku($sku);
                    // $stockItem->setQty($_product->getQty()+1);
                    // $this->stockRegistry->updateStockItemBySku($sku, $stockItem);
                //}
            }
            //print_r($productDetails);
            $resultJson = $this->resultJsonFactory->create();
            return $resultJson->setData(['status' => 'success', 'product_details' => $productDetails]);
        } else {
            return $resultJson->setData(['status' => 'error']);
        }
    }    

    public function collection($seller_id, $barcodeText, $inventory) {
        $productCollection = $this->productCollection;
       $collection = $productCollection->create()
           ->addAttributeToSelect('*')
           ->addAttributeToFilter('barcode', array('in'=> $barcodeText ))
           ->addAttributeToFilter('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
           ->load();
        
       $this->_stockFilter->addInStockFilterToCollection($collection);

       $collection->getSelect()->join(

               ['lof_marketplace_seller'],
              'lof_marketplace_seller.seller_id = '.$seller_id,
               []
       );
       return $collection;
   }

    protected function _isAllowed()
    {
        return true;
    }            
        
}