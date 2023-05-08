<?php
namespace Akwaaba\Barcode\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Form\Fieldset;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Element\DataType\Media\Image;
use Akwaaba\Barcode\Model\FileInfo;
use Magento\Ui\Component\Container;

/**
 * Data provider for "Custom Attribute" field of product page
 */
class Barcode extends AbstractModifier
{
    private $locator;
    private $arrayManager;
    protected $fileInfo;

    public function __construct(
        LocatorInterface $locator,
        FileInfo $fileInfo,
        \Magento\Framework\Stdlib\ArrayManager $arrayManager
    ) {
        $this->locator = $locator;
        $this->arrayManager = $arrayManager;
        $this->fileInfo = $fileInfo;
    }
    public function modifyData(array $data)
    {
        // $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        // $storeManager = $objectManager->get('Magento\Store\Model\StoreManagerInterface'); 
        // $currentStore = $storeManager->getStore();
        // $mediaUrl = $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        // $fileSystem = $objectManager->create('\Magento\Framework\Filesystem');
        // $mediaPath = $fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath();
        // $fileName = basename('test.png');
        // $img = [];
        // if($this->fileInfo->isExist($fileName) && $fileName !== '') {
        //     $stat = $this->fileInfo->getStat($fileName);
        //     $img[0]['url'] = $this->fileInfo->getAbsolutePath($fileName);
        //     $img[0]['name'] = basename($fileName);
        //     $img[0]['size'] = isset($stat) ? $stat['size'] : 0;
        //     $img[0]['type'] = $this->fileInfo->getMimeType($fileName);
        // }

        // $product   = $this->locator->getProduct();
        // $productId = $product->getId();
        // $data = array_replace_recursive(
        //     $data, [
        //         $productId => [
        //             'product' => [
        //                 'custom_field'=> $this->fileInfo->getAbsolutePath($fileName)
        //             ]
        //         ]
        //     ]);
        return $data;
    }
    public function getBarcode()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('Magento\Store\Model\StoreManagerInterface'); 
        $currentStore = $storeManager->getStore();
        $mediaUrl = $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $product   = $this->locator->getProduct();
        $fileSystem = $objectManager->create('\Magento\Framework\Filesystem');
        $mediaPath = $fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath();
        $fileName = basename($product->getBarcode().'.png');
        $image = '';
        $imageName = '';
        $img = [];
        $fileExist = true;
        if($this->fileInfo->isExist($fileName) && $fileName !== '') {
            $stat = $this->fileInfo->getStat($fileName);
            $image = $this->fileInfo->getAbsolutePath($fileName);
            $imageName = basename($fileName);
            $img[0]['url'] = $this->fileInfo->getAbsolutePath($fileName);
            $img[0]['name'] = basename($fileName);
            $img[0]['size'] = isset($stat) ? $stat['size'] : 0;
            $img[0]['type'] = $this->fileInfo->getMimeType($fileName);
            $fileExist = true;
        } else {
            $fileExist = false;
        }

        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => null,
                        'formElement' => Container::NAME,
                        'componentType' => Container::NAME,
                        'component' => 'Magento_Ui/js/form/components/html',
                        'template' => 'ui/form/components/complex',
                        'sortOrder' => 12,
                        'content' => ($fileExist) ? '<img id="barcode" src="'.$this->fileInfo->getAbsolutePath($fileName).'" /><br><a onclick="window.open().document.write(\'<img src='.$this->fileInfo->getAbsolutePath($fileName).' />\').print().close()" href="javascript:void(0)" class="print-barcode">Print Barcode</a>': 'You need to generate barcode!',
                    ],
                ],
            ],
        ];
    }
    public function modifyMeta(array $meta)
    {
        $meta = array_replace_recursive(
            $meta,
            [
                'custom_fieldset' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label' => __('Barcode Image'),
                                'collapsible' => true,
                                'componentType' => Fieldset::NAME,
                                'dataScope' => 'data.custom_field',
                                'sortOrder' => 10
                            ],
                        ],
                    ],
                    'children' => [
                        'custom_field' => $this->getBarcode()
                        ],
                ]
            ]
        );
        return $meta;


    //     $fieldCode = 'custom_field';
    //     $elementPath = $this->arrayManager->findPath($fieldCode, $meta, null, 'children');
    //     $containerPath = $this->arrayManager->findPath('product-details', $meta, null, 'children');
    //     if (!$elementPath) {
    //         return $meta;
    //     }

    //     $meta = $this->arrayManager->merge(
    //         $containerPath,
    //         $meta,
    //         [
    //             'children'  => [
    //                 $fieldCode => [
    //                     'arguments' => [
    //                         'data' => [
    //                             'config' => [
    //                                 'label' => __('Custom Field'),
    //                         'componentType' => 'imageUploader',
    //                         'formElement' => 'imageUploader',
    //                         'dataType' => Image::NAME,
    //                         'sortOrder' => 10
    //                                 'required'  => false,
    //                                 'default'  => 'img.png'
    //                             ],
    //                         ],
    //                     ],
    //                 ]
    //             ]
    //         ]
    //     );
    // return $meta;
    }
}