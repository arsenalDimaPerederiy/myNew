<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Mage_Catalog_Model_Convert_Adapter_ProductImportLight
    extends Mage_Catalog_Model_Convert_Adapter_Product
{
    protected $_usedFields;
	
	protected $_importId;


    protected $_systemFields = array(
            "1" => 'status',
            "2" => 'visibility',
            "3" => 'tax_class_id',
            "4" => 'image',
            "5" => 'thumbnail',
            "6" => 'small_image'
    );
	
	
    /**
     * Save product (import)
     *
     * @param  array $importData
     * @throws Mage_Core_Exception
     * @return bool
     */
    public function saveRow(array $importData)
    {
		if(!Mage::registry('last_import_id')) {
		
			$batch = Mage::getModel('dataflow/batch')->getCollection();
			$batch->getSelect()->order('batch_id desc')->limit(1);
			$batch->load();
			foreach($batch as $batchItem) {
				$batchId = $batchItem->getId();
			}
			Mage::register('last_import_id',$batchId);
			
		}
		
		
        $product = $this->getProductModel()
            ->reset();
//print_r($importData);
//echo "<br>";
        if (empty($importData['store'])) {
            if (!is_null($this->getBatchParams('store'))) {
                $store = $product->setStoreId($this->getBatchParams('store'));
            } else {
                $message = Mage::helper('catalog')->__(
                    'Skipping import row, required field "%s" is not defined.',
                    'store'
                );
                Mage::throwException($message);
            }
        } else {
            $store = $product->setStoreId($importData['store']);
        }

        if ($store === false) {
            $message = Mage::helper('catalog')->__(
                'Skipping import row, store "%s" field does not exist.',
                $importData['store']
            );
            Mage::throwException($message);
        }


        if (empty($importData['sku'])) {
            $message = Mage::helper('catalog')->__('Skipping import row, required field "%s" is not defined.', 'sku');
            Mage::throwException($message);
        }
        $productId = $product->getIdBySku($importData['sku']);

        if ($productId) {

            $product->load($productId);
			


			$this->setProductTypeInstance($product);

			$product->setData("lastimportid", Mage::registry('last_import_id'));


			$stockData = array();
			$inventoryFields = isset($this->_inventoryFieldsProductTypes[$product->getTypeId()])
				? $this->_inventoryFieldsProductTypes[$product->getTypeId()]
				: array();
			foreach ($inventoryFields as $field) {
				if (isset($importData[$field])) {
					if (in_array($field, $this->_toNumber)) {
						$stockData[$field] = $this->getNumber($importData[$field]);
					} else {
						$stockData[$field] = $importData[$field];
					}
				}
			}
			$product->setStockData($stockData);


			//if(Mage::registry('current_convert_profile')){
				//$product->setData('lastimportid', '10');
				//$product->setData('price', '10');
				//				$product->setPrice(10);
			//} else {

            if (empty($importData['price']) || !isset($importData['price'])) {
                $value = isset($importData['price']) ? $importData['price'] : '';
                $message = Mage::helper('catalog')->__(
                    'Skip import row, is not valid value "%s" for field "%s"',
                    $value,
                    'price'
                );
                Mage::throwException($message);
            }
            $product->setPrice($importData['price']);
			//}
			
			//$product->setIsMassupdate(true);
			$product->setExcludeUrlRewrite(true);

			$product->save();









			// Store affected products ids
			$this->_addAffectedEntityIds($product->getId());
			
		}

        return true;
    }

    public function bitsCount($number)
    {

        $counter = 0;
        while ($number >= 1) {
 
            $number = $number/10;
            $counter++; 

        }

        return $counter;
    }
	
	public function _rebuildIndexes(){

		$collection = Mage::getSingleton('index/indexer')->getProcessesCollection();
		foreach ($collection as $process) {
			try {
				$process->reindexAll();
				$this->addException($process->getIndexer()->getName() . " index was rebuilt successfully");
			} catch (Exception  $e) {
			    $this->addException($e->getMessage(), Mage_Dataflow_Model_Convert_Exception::FATAL);
			}
		}
	}
	
	public function _rebuildCache(){

		try {
			$allTypes = Mage::app()->useCache();
			foreach($allTypes as $type => $blah) {
				Mage::app()->getCacheInstance()->cleanType($type);
			}
			$this->addException("Cache was rebuilt successfully");
		} catch (Exception $e) {
			// do something
			$this->addException($e->getMessage(), Mage_Dataflow_Model_Convert_Exception::FATAL);
		}
		
	}
	
	public function setAllOutOfStock() 
    {
		try{
		
			$batch = Mage::getModel('dataflow/batch')->getCollection();
			$batch->getSelect()->order('batch_id desc')->limit(1);
			$batch->load();
			foreach($batch as $batchItem) {
				$batchId = $batchItem->getId();
			}
			
			$updatedProducts = Mage::getModel('catalog/product')
							->getCollection()
							->addAttributeToSelect('*')
							->addAttributeToFilter('lastimportid', $batchId);
							
			$productIds = array();
			foreach($updatedProducts as $product) {
				$productIds[] = $product->getId();
			}
			
			$productsToHide = Mage::getModel('catalog/product')->getCollection()->addAttributeToFilter('entity_id', array('nin' => $productIds));
		

			foreach ($productsToHide as $product) {

				$product = Mage::getModel('catalog/product')->load($product->getId());
				$stockData = $product->getStockItem();
				$stockData->setData('qty',0);
				$stockData->setData('is_in_stock',0);
				$stockData->setData('manage_stock',1);
				$stockData->setData('use_config_manage_stock',1);
				$stockData->save();

			} 

			
			$this->addException("Set all product out of stock");
		} catch (Exception $e) {
			// do something
			$this->addException($e->getMessage(), Mage_Dataflow_Model_Convert_Exception::FATAL);
		}
    }

	public function finish(){

		$this->setAllOutOfStock();
		$this->_rebuildIndexes();
		$this->_rebuildCache();
		
		return true;
	}


}
