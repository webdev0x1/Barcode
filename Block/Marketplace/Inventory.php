<?php
namespace Akwaaba\Barcode\Block\Marketplace;
class Inventory extends \Magento\Framework\View\Element\Template
{
    function _prepareLayout(){}

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;
    public function __construct(\Magento\Framework\View\Element\Template\Context $context, \Magento\Framework\Registry $registry) {
        $this->_coreRegistry  = $registry;
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
}