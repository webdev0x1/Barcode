# Mage2 Module Akwaaba Barcode

    ``akwaaba/module-barcode``

 - [Main Functionalities](#markdown-header-main-functionalities)
 - [Installation](#markdown-header-installation)
 - [Configuration](#markdown-header-configuration)
 - [Specifications](#markdown-header-specifications)
 - [Attributes](#markdown-header-attributes)


## Main Functionalities
Bardcode inventory

## Installation

### Type 1: Zip file

 - Unzip the zip file in `app/code/Akwaaba`
 - Enable the module by running `php bin/magento module:enable Akwaaba_Barcode`
 - Apply database updates by running `php bin/magento setup:upgrade --keep-generated`
 - Flush the cache by running `php bin/magento cache:flush`

### Type 2: Composer

 - Make the module available in a composer repository for example:
    - private repository `repo.magento.com`
    - public repository `packagist.org`
    - public github repository as vcs
 - Add the composer repository to the configuration by running `composer config repositories.repo.magento.com composer https://repo.magento.com/`
 - Install the module composer by running `composer require akwaaba/module-barcode`
 - enable the module by running `php bin/magento module:enable Akwaaba_Barcode`
 - apply database updates by running `php bin/magento setup:upgrade --keep-generated`
 - Flush the cache by running `php bin/magento cache:flush`


## Configuration




## Specifications




## Attributes

 - Product - Barcode (barcode)

 - Product - Barcode Type (barcode_type)

 - Sales - Barcode  (barcode_)

 - Sales - Barcode (barcode)

 - Sales - Barcode Type (barcode_type)

