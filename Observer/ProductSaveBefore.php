<?php 

namespace Akwaaba\Barcode\Observer;

use Magento\Framework\Event\ObserverInterface;
use \Milon\Barcode\DNS1D;

class ProductSaveBefore implements ObserverInterface
{
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $fileSystem = $objectManager->create('\Magento\Framework\Filesystem');
        $mediaPath = $fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath();
        $resultFactory = $objectManager->create('\Magento\Framework\Controller\ResultFactory');
        $messageManager = $objectManager->create('\Magento\Framework\Message\ManagerInterface');
        $fileDriver = $objectManager->create('\Magento\Framework\Filesystem\Driver\File');
	      $product = $observer->getProduct();
        $is_active = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('barcode_config/configuration/is_active');
        $default_bc_txt = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('barcode_config/configuration/default_barcode_text');
        $default_bc_type = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('barcode_config/configuration/default_barcode_type');
        if($product->getTypeId() != 'simple') {
          return;
        }
        

        $barcodeTxt = $product->getBarcode();
        $barcodeType = $product->getBarcodeType();
        $typeId = '';

        if(empty($barcodeTxt)) {
          $rn = $this->randomNumber(12);
          while(!$this->generate_upc_checkdigit($rn) || !$this->is_barcode_exist($rn, $product, $objectManager)) {
            $rn = $this->randomNumber(12);
            $barcodeTxt = $rn;
          }
        }
        if($is_active) {
    		    if(!empty($product->getBarcodeType())) {
              $barcodeType = $product->getBarcodeType();
              $optionId = $product->getBarcodeType();
              $attribute = $product->getResource()->getAttribute('barcode_type');
              if ($attribute->usesSource()) {
                $typeId = $optionId;
                $optionText = $attribute->getSource()->getOptionText($optionId);
                $barcodeType = $optionText;
              }
            } else {
               
                $barcodeType = $default_bc_type;
                if(empty($barcodeType)) {
                  $barcodeType = "UPCA";
                }
                
                $attribute = $product->getResource()->getAttribute('barcode_type');
                if ($attribute && $attribute->usesSource()) {
                    $typeId = $attribute->getSource()->getOptionId($barcodeType);
                }
                // echo $optionId;die();
                //$product->setBarcodeType($optionId);
            }
            if(empty($barcodeType)) {
              if(empty($default_bc_type)) {
                $barcodeType = "UPCA";
              }
              $isAttributeExist = $product->getResource()->getAttribute('barcode_type');
              $optionId = '';
              if ($isAttributeExist && $isAttributeExist->usesSource()) {
                  $optionId = $isAttributeExist->getSource()->getOptionId($barcodeType);
              }
              $product->setBarcodeType($optionId);
              $barcodeType = $default_bc_type;
            } 
               $isExist = $this->checkBarcodeExist($barcodeTxt, $objectManager);
               if($storeManager->getStore()->getStoreId() != 0) {
                return;
               }
               if($isExist && $barcodeTxt != $product->getBarcode()) {
                  $resultRedirect = $resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
                  $messageManager->addWarningMessage(__('Barcode already exist.'));
                  return $resultRedirect->setPath('*/*/');
               }
               //echo "\n".$barcodeTxt."---\n";
               //echo  $product->getBarcode()."&&";
              //  echo $barcodeTxt;
              //  echo $default_bc_type;
               $this->createBarcodeImage($product, $barcodeTxt, $barcodeType, $mediaPath);
        }
        
        $product->setBarcode($barcodeTxt);
        $product->setBarcodeType($typeId);
    }

    public function checkBarcodeExist($barcodeTxt, $objectManager) {
      $prodCollection = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
      $collection = $prodCollection->create()
          ->addAttributeToSelect('name')
          ->addAttributeToFilter('barcode',$barcodeTxt)
          ->load();
          
          if(empty($collection->getData())) {
            return false;
          } else {
            return true;
          }
    }
    

    public function createBarcodeImage($product, $barcodeTxt, $barcodeType, $mediaPath) {
      try {
        $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', true);
        $pdf->SetPrintHeader(false);
        $pdf->SetPrintFooter(false);
        $pdf->AddPage();
        $style = array(
            'position' => '0',
            'align' => 'left',
            'stretch' => true,
            'fitwidth' => true,
            'cellfitalign' => '',
            'border' => true,
            'hpadding' => 'auto',
            'vpadding' => 'auto',
            'fgcolor' => array(0,0,0),
            'bgcolor' => '',//array(255,255,128),
            'text' => true,
            'label' => '',
            'font' => 'helvetica',
            'fontsize' => 8,
            'stretchtext' => 4
        );
        
        $pdf->write1DBarcode($barcodeTxt, $barcodeType, '', '', 120, 25, 0.4, $style, 'N');
        
        // ---------------------------------------------------------
        
        $pdf->Output($mediaPath.'barcodes/'.$barcodeTxt.'.pdf', 'F');
        $pdflib = new \ImalH\PDFLib\PDFLib();
        $pdflib->setPdfPath($mediaPath.'barcodes/'.$barcodeTxt.'.pdf');
        $pdflib->setOutputPath($mediaPath.'barcodes/');
        $pdflib->setImageFormat(\ImalH\PDFLib\PDFLib::$IMAGE_FORMAT_PNG);
        $pdflib->setDPI(300);
        $pdflib->setPageRange(1,$pdflib->getNumberOfPages());
        $pdflib->convert();
        $img = imagecreatefrompng($mediaPath.'barcodes/page-1.png');
        $b_top = 0;
        $b_btm = 0;
        $b_lft = 0;
        $b_rt = 0;
        
        //top
        for(; $b_top < imagesy($img); ++$b_top) {
          for($x = 0; $x < imagesx($img); ++$x) {
            if(imagecolorat($img, $x, $b_top) != 0xFFFFFF) {
               break 2; //out of the 'top' loop
            }
          }
        }
        
        //bottom
        for(; $b_btm < imagesy($img); ++$b_btm) {
          for($x = 0; $x < imagesx($img); ++$x) {
            if(imagecolorat($img, $x, imagesy($img) - $b_btm-1) != 0xFFFFFF) {
               break 2; //out of the 'bottom' loop
            }
          }
        }
        
        //left
        for(; $b_lft < imagesx($img); ++$b_lft) {
          for($y = 0; $y < imagesy($img); ++$y) {
            if(imagecolorat($img, $b_lft, $y) != 0xFFFFFF) {
               break 2; //out of the 'left' loop
            }
          }
        }
        
        //right
        for(; $b_rt < imagesx($img); ++$b_rt) {
          for($y = 0; $y < imagesy($img); ++$y) {
            if(imagecolorat($img, imagesx($img) - $b_rt-1, $y) != 0xFFFFFF) {
               break 2; //out of the 'right' loop
            }
          }
        }
        
        $newimg = imagecreatetruecolor(
            imagesx($img)-($b_lft+$b_rt), imagesy($img)-($b_top+$b_btm));
        
        imagecopy($newimg, $img, 0, 0, $b_lft, $b_top, imagesx($newimg), imagesy($newimg));
        
        imagepng($newimg, $mediaPath.'barcodes/'.$barcodeTxt.'.png');
        return $mediaPath.'barcodes/'.$barcodeTxt.'.png';
      } catch (\Exception $e) {
        throw new \Exception($e->getMessage());
      }
    }


   public function randomNumber($length) {
      $result = '';
  
      for($i = 0; $i < $length; $i++) {
          $result .= mt_rand(0, 9);
      }
  
      return $result;
  }
  
  public function is_barcode_exist($barcode, $product, $objectManager) {
  
    $is_barcode_exist = true;
    $productCollectionFactoryN = $objectManager->get('\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
    $productcollectionN = $productCollectionFactoryN->create()->addAttributeToSelect('*');
  
    $collection = $productcollectionN->addAttributeToFilter('barcode', array('in'=> $barcode ));
          if(!empty($collection->getData())) {
            foreach($collection->getData() as $productC) {
              if($productC->getId() == $product->getId()) {
                echo 'barcode not exist';
                $is_barcode_exist = false;
              } else {
                echo 'barcode exist';
                $is_barcode_exist = true;
              }
            }
          }
          return $is_barcode_exist;
  }
  
  public function generate_upc_checkdigit($barcode)
  {
      // check to see if barcode is 12 digits long
  
    if(!preg_match("/^[0-9]{12}$/",$barcode)) {
  
      return false;
  
    }
  
    $digits = $barcode;
  
    // 1. sum each of the odd numbered digits
  
    $odd_sum = $digits[0] + $digits[2] + $digits[4] + $digits[6] + $digits[8] + $digits[10];  
  
    // 2. multiply result by three
  
    $odd_sum_three = $odd_sum * 3;
  
    // 3. add the result to the sum of each of the even numbered digits
  
    $even_sum = $digits[1] + $digits[3] + $digits[5] + $digits[7] + $digits[9];
  
    $total_sum = $odd_sum_three + $even_sum;
  
    // 4. subtract the result from the next highest power of 10
  
    $next_ten = (ceil($total_sum/10))*10;
  
    $check_digit = $next_ten - $total_sum;
  
  
  
  
    // if the check digit and the last digit of the barcode are OK return true;
  
    if($check_digit == $digits[11]) {
  
       return true;
  
    } 
  
    return false;
  }
}
