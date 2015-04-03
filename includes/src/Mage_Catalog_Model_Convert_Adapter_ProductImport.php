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


class Mage_Catalog_Model_Convert_Adapter_ProductImport
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
		
			//delete all product images
			if($importData['type'] == 'configurable') {
				$_product = Mage::getModel('catalog/product')->load($productId); 
				foreach ($_product->getMediaGalleryImages() as $_usedImages) {
					if(file_exists($_usedImages->getPath())){
						unlink($_usedImages->getPath());
					}
				}
				
				$mediaApi = Mage::getModel("catalog/product_attribute_media_api");
				$items = $mediaApi->items($productId);
				foreach($items as $item)
				$mediaApi->remove($productId, $item['file']);
			}


            $product->load($productId);
        } else {

            /**
             * Check product define type
             */
            if (empty($importData['type'])) {
                $value = isset($importData['type']) ? $importData['type'] : '';
                $message = Mage::helper('catalog')->__(
                    'Skip import row, is not valid value "%s" for field "%s"',
                    $value,
                    'type'
                );
                Mage::throwException($message);
            }
            $product->setTypeId($importData['type']);

            /**
             * Check product define attribute set
             */
            if (empty($importData['attribute_set'])) {
                if (!is_null($this->getBatchParams('attribute_set')) || !isset($importData['attribute_set'])) {
                    $importData['attribute_set']=$this->getBatchParams('attribute_set');
                } else {
                    $value = isset($importData['attribute_set']) ? $importData['attribute_set'] : '';
                    $message = Mage::helper('catalog')->__(
                        'Skip import row, the value "%s" is invalid for field "%s"',
                        $value,
                        'attribute_set'
                    );
                    Mage::throwException($message);
                }
            }
            $product->setAttributeSetId($importData['attribute_set']);

            if (empty($importData['description']) || !isset($importData['description'])) {
                $value = isset($importData['description']) ? $importData['description'] : '';
                $message = Mage::helper('catalog')->__(
                    'Skip import row, is not valid value "%s" for field "%s"',
                    $value,
                    'description'
                );
                Mage::throwException($message);
            }
            $product->setDescription($importData['description']);
			

			
			
			if (!empty($importData['sku']) && isset($importData['sku'])) {
			    $product->setUrlKey($importData['sku']);
			}

            if (empty($importData['short_description']) || !isset($importData['short_description'])) {
                $value = isset($importData['short_description']) ? $importData['short_description'] : '';
                $message = Mage::helper('catalog')->__(
                    'Skip import row, is not valid value "%s" for field "%s"',
                    $value,
                    'short description'
                );
                Mage::throwException($message);
            }
            $product->setShortDescription($importData['short_description']);

            if (!isset($importData['tax_class_id'])) {
                $value = isset($importData['tax_class_id']) ? $importData['tax_class_id'] : '';
                $message = Mage::helper('catalog')->__(
                    'Skip import row, is not valid value "%s" for field "%s"',
                    $value,
                    'tax_class_id'
                );
                Mage::throwException($message);
            }
            $product->setTaxClassId($importData['tax_class_id']);

            if (!isset($importData['websites'])) {
                $value = isset($importData['websites']) ? $importData['websites'] : '';
                $message = Mage::helper('catalog')->__(
                    'Skip import row, is not valid value "%s" for field "%s"',
                    $value,
                    'websites'
                );
                Mage::throwException($message);
            }
            $product->setWebsiteIds($importData['websites']);

            if (empty($importData['status']) || !isset($importData['status'])) {
                $value = isset($importData['status']) ? $importData['status'] : '';
                $message = Mage::helper('catalog')->__(
                    'Skip import row, is not valid value "%s" for field "%s"',
                    $value,
                    'status'
                );
                Mage::throwException($message);
            }
            $product->setStatus($importData['status']);

            if (empty($importData['visibility']) || !isset($importData['visibility'])) {
                $value = isset($importData['visibility']) ? $importData['visibility'] : '';
                $message = Mage::helper('catalog')->__(
                    'Skip import row, is not valid value "%s" for field "%s"',
                    $value,
                    'visibility'
                );
                Mage::throwException($message);
            }
            $product->setVisibility($importData['visibility']);

            if (empty($importData['name']) || !isset($importData['name'])) {
                $value = isset($importData['name']) ? $importData['name'] : '';
                $message = Mage::helper('catalog')->__(
                    'Skip import row, is not valid value "%s" for field "%s"',
                    $value,
                    'name'
                );
                Mage::throwException($message);
            }
            $product->setName($importData['name']);

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

            if (empty($importData['weight']) || !isset($importData['weight'])) {
                $value = isset($importData['weight']) ? $importData['weight'] : '';
                $message = Mage::helper('catalog')->__(
                    'Skip import row, is not valid value "%s" for field "%s"',
                    $value,
                    'weight'
                );
                Mage::throwException($message);
            }
            $product->setWeight($importData['weight']);

 

                $this->_usedFields = array(
                        "1" => 'type',
                        "2" => 'attribute_set',
                        "3" => 'description',
                        "4" => 'short_description',
                        "5" => 'tax_class_id',
                        "6" => 'websites',
                        "7" => 'status',
                        "8" => 'visibility',
                        "9" => 'name',
                        "10" => 'price',
                        "10" => 'weight'
                    );

           

        }

        $this->setProductTypeInstance($product);


        if (isset($importData['meta_title'])) {
            $product->setMetaTitle($importData['meta_title']);
        }
        if (isset($importData['meta_description'])) {
            $product->setMetaDescription($importData['meta_description']);
        }
		
        if (isset($importData['category_ids'])) {
            $product->setCategoryIds($importData['category_ids']);
        }

        foreach ($importData as $field => $value) {

            if (is_null($value)) {
                continue;
            }
            if (in_array($field, $this->_usedFields)) {
                continue;
            }
            if (in_array($field, $this->_systemFields)) {
                continue;
            }


            $attribute = $this->getAttribute($field);
            if (!$attribute) {
                continue;
            }


            $isArray = false;
            $setValue = $value;

            if ($attribute->getFrontendInput() == 'multiselect') {
                $value = explode(self::MULTI_DELIMITER, $value);
                $isArray = true;
                $setValue = array();
            }

            if ($value && $attribute->getBackendType() == 'decimal') {
                $setValue = $this->getNumber($value);
            }

            if ($attribute->usesSource()) {
                $options = $attribute->getSource()->getAllOptions(false);

                if ($isArray) {
                    foreach ($options as $item) {
                        if (in_array($item['label'], $value)) {
                            $setValue[] = $item['value'];
                        }
                    }
                } else {
                    $setValue = false;
                    foreach ($options as $item) {
                        if (is_array($item['value'])) {
                            foreach ($item['value'] as $subValue) {
                                if (isset($subValue['value']) && $subValue['value'] == $value) {
                                    $setValue = $value;
                                }
                            }
                        } else if ($item['label'] == $value) {
                            $setValue = $item['value'];
                        }
                    }
                }
            }

            $product->setData($field, $setValue);
        }

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



        //add super attributes

        if (!empty($importData['simple_products'])) {

            $product->setCanSaveConfigurableAttributes(true);
            $product->setCanSaveCustomOptions(true);
             
            $productTypeInstance = $product->getTypeInstance();
            // This array is is an array of attribute ID's which the configurable product swings around (i.e; where you say when you
            // create a configurable product in the admin area what attributes to use as options)
            // $_attributeIds is an array which maps the attribute(s) used for configuration so their numerical counterparts.
            // (there's probably a better way of doing this, but i was lazy, and it saved extra db calls);
            // $_attributeIds = array("size" => 999, "color", => 1000, "material" => 1001); // etc..
            
			$_usedIds = $productTypeInstance->getUsedProductAttributeIds();
            $_attributeIds = array();
            foreach ($importData['simple_products'] as $tempArray) {    
                foreach ($tempArray as $key => $_newattributes) {
                    if (!in_array($_newattributes['attr_id'],$_usedIds)) {
						$_attributeIds[$key] = $_newattributes['attr_id'];
					}
                    
                } 
            }
            
			
            $productTypeInstance->setUsedProductAttributeIds($_attributeIds);
             
            // Now we need to get the information back in Magento's own format, and add bits of data to what it gives us..
            $attributes_array = $productTypeInstance->getConfigurableAttributesAsArray();

            foreach($attributes_array as $key => $attribute_array) {
                $attributes_array[$key]['use_default'] = 1;
                $attributes_array[$key]['position'] = 0;
             
                if (isset($attribute_array['frontend_label'])) {
                    $attributes_array[$key]['label'] = $attribute_array['frontend_label'];
                }
                else {
                    $attributes_array[$key]['label'] = $attribute_array['attribute_code'];
                }
            }

             
            // Add it back to the configurable product..
            $product->setConfigurableAttributesData($attributes_array);
             
            // Remember that $simpleProducts array we created earlier? Now we need that data..
            $dataArray = array();
            foreach ($importData['simple_products'] as $simpleArray) {
                $simpleArray['0']['id'] =  Mage::getModel('catalog/product')->getIdBySku($simpleArray['0']['sku']);

                $dataArray[$simpleArray['0']['id']] = array(); $key=0;
                foreach ($attributes_array as $attrArray) {

                        $dataArray[$simpleArray['0']['id']][$key]=
                        array(
                            "attribute_id" => $simpleArray[$key]['attr_id'],
                            "label" => $simpleArray[$key]['label'],
                            "is_percent" => false,
                            "value_index" => $simpleArray[$key]['value'],
                            "pricing_value" => $simpleArray[$key]['price']
                        );
                        $key++;
                }
            }

             
            // This tells Magento to associate the given simple products to this configurable product..
            $product->setConfigurableProductsData($dataArray);
     
            // Set stock data. Yes, it needs stock data. No qty, but we need to tell it to manage stock, and that it's actually
            // in stock, else we'll end up with problems later..
            $product->setStockData(array(
                'use_config_manage_stock' => 1,
                'is_in_stock' => 1,
                'is_salable' => 1
            ));
        }
        

        //end add super attributes

	
			/*$mediaGalleryAttribute = Mage::getModel('catalog/resource_eav_attribute')->loadByCode(4, 'media_gallery');
			$gallery = $product->getMediaGalleryImages();
			if(count($gallery)>0)
			{
				foreach ($gallery as $image)
				$mediaGalleryAttribute->getBackend()->removeImage($product, $image->getFile());
			
				$product->save();
			}*/


            $mediaGalleryBackendModel = $this->getAttribute('media_gallery')->getBackend();
    
            $arrayToMassAdd = array();
			
            foreach ($product->getMediaAttributes() as $mediaAttributeCode => $mediaAttribute) {
    
                if (isset($importData[$mediaAttributeCode])) {
                    $file = trim($importData[$mediaAttributeCode]);
                    if (!empty($file) && !$mediaGalleryBackendModel->getImage($product, $file)) {
                        $arrayToMassAdd[] = array('file' => trim($file), 'mediaAttribute' => $mediaAttributeCode);
                    }
                }
            }


			

			
			
            $addedFilesCorrespondence = $mediaGalleryBackendModel->addImagesWithDifferentMediaAttributes(
                $product,
                $arrayToMassAdd, Mage::getBaseDir('media') . DS . 'import',
                false,
                false
            );
    
            foreach ($product->getMediaAttributes() as $mediaAttributeCode => $mediaAttribute) {
                $addedFile = '';
                    $fileLabel = 1;
                    if (isset($importData[$mediaAttributeCode])) {
    
                        $keyInAddedFile = array_search($importData[$mediaAttributeCode],
                            $addedFilesCorrespondence['alreadyAddedFiles']);
                        if ($keyInAddedFile !== false) {
                            $addedFile = $addedFilesCorrespondence['alreadyAddedFilesNames'][$keyInAddedFile];
                        }
                    }
    
                    if (!$addedFile) {
                        $addedFile = $product->getData($mediaAttributeCode);
                    }

                    if ($fileLabel && $addedFile) {
                        if(isset($importData['image_color'])) {
                            $mediaGalleryBackendModel->updateImage($product, $addedFile, array('position' => $fileLabel, 'label' => $importData['image_color']));
                        } else {
                            $mediaGalleryBackendModel->updateImage($product, $addedFile, array('position' => $fileLabel));
                        }
                    }
            }
        

        if (!empty($importData['gallery'])) {
 
            $arrayToMassAdd2 = array();
  
            $_column = 'gallery'; //Mage::getStoreConfig('catalog/catalog/multiple_image_column_name');
            if ($importData[$_column]) {
                $additionalImages = explode(';',$importData[$_column]);
                foreach ($additionalImages as $image) {
                    if (!empty($image) && !$mediaGalleryBackendModel->getImage($product, $image)) {
                        $arrayToMassAdd2[] = array('file' => trim($image));
                    }
                }
            }
     
			
            $addedFilesCorrespondenceGallery = $mediaGalleryBackendModel->addImagesWithDifferentMediaAttributes(
                $product,
                $arrayToMassAdd2, Mage::getBaseDir('media') . DS . 'import',
                false,
                false
            );
    
            $_columnPosition = 'gallery_position';
            $_columnColor = 'gallery_color';
            if ($importData[$_columnPosition]) {
                $additionalImagesPos = explode(';',$importData[$_columnPosition]);

                if ($importData[$_columnColor]) {
                    $additionalImagesColor = explode(';',$importData[$_columnColor]);
                }
                $pos=0;
    
                foreach ($addedFilesCorrespondenceGallery['alreadyAddedFilesNames'] as $addedFile) {
                    
                    if(isset($importData['gallery_color'])) {
                        $mediaGalleryBackendModel->updateImage($product, $addedFile, array('position' => $additionalImagesPos[$pos],'label' => $additionalImagesColor[$pos]));
                    } else {
                        $mediaGalleryBackendModel->updateImage($product, $addedFile, array('position' => $additionalImagesPos[$pos]));
                    }
                    $pos++;
                }
            } 
		
        } 



        //$product->setIsMassupdate(true);
        $product->setExcludeUrlRewrite(true);

        $product->save();







        if($importData['type'] == 'configurable') {
            $updateProduct = $this->getProductModel()
                ->reset();
    
            if (empty($importData['store'])) {
                if (!is_null($this->getBatchParams('store'))) {
                    $store = $updateProduct->setStoreId($this->getBatchParams('store'));
                } else {
                    $message = Mage::helper('catalog')->__(
                        'Skipping import row, required field "%s" is not defined.',
                        'store'
                    );
                    Mage::throwException($message);
                }
            } else {
                $store = $updateProduct->setStoreId($importData['store']);
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
            $productId = $updateProduct->getIdBySku($importData['sku']);
    
            if ($productId) {
                $updateProduct->load($productId);
            }
    
    
            $this->setProductTypeInstance($updateProduct);
            
    
            $imageSwithCode = 'a:'.count($product->load($productId)->getMediaGalleryImages()).':{';
            $imageMoreCode = 'a:'.count($product->load($productId)->getMediaGalleryImages()).':{';
            foreach ($product->load($productId)->getMediaGalleryImages() as $productImage) {
            
                if($productImage->getData('position') == 1) {
                    $imageSwithCode .= 'i:'.$productImage->getData('value_id').';s:'.$this->bitsCount($productImage->getData('label')).':"'.$productImage->getData('label').'";';
                } else {
                    $imageSwithCode .= 'i:'.$productImage->getData('value_id').';s:0:"";';
                }
                $imageMoreCode .= 'i:'.$productImage->getData('value_id').';s:'.$this->bitsCount($productImage->getData('label')).':"'.$productImage->getData('label').'";';
            
            }
            $imageSwithCode .= '}';
            $imageMoreCode .= '}';
    
            $updateProduct->setData('cjm_imageswitcher',$imageSwithCode);
            $updateProduct->setData('cjm_moreviews',$imageMoreCode);
            $updateProduct->setData('cjm_useimages',1);


            $gallery = $updateProduct->getData('media_gallery');
            foreach($gallery['images'] as $image) {

                foreach ($importData['simple_products'] as $tempArray) {    
                    foreach ($tempArray as $key => $_newattributes) {
                        
                        if($_newattributes['attr_id'] == 85 && $_newattributes['value'] == $image['label'])
                        $image['label'] = $_newattributes['frontend_label'];
                        
                    } 
                }

            array_push($gallery['images'], $image);
            }
            $updateProduct->setData('media_gallery', $gallery);
    
            //$updateProduct->setIsMassupdate(true);
            $updateProduct->setExcludeUrlRewrite(true);
    
            $updateProduct->save();
    
        }

        // Store affected products ids
        $this->_addAffectedEntityIds($product->getId());

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
			$collection = Mage::getModel('catalog/product')
                        ->getCollection()
                        ->addAttributeToSelect('*');

			foreach ($collection as $product) {

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

		$this->_rebuildIndexes();
		$this->_rebuildCache();
		return true;
	}
	
  
}
