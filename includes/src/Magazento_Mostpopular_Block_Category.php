<?php
/*
 *  Created on Mar 16, 2011
 *  Author Ivan Proskuryakov - volgodark@gmail.com - Magazento.com
 *  Copyright Proskuryakov Ivan. Magazento.com © 2011. All Rights Reserved.
 *  Single Use, Limited Licence and Single Use No Resale Licence ["Single Use"]
 */
?>
<?php

class Magazento_Mostpopular_Block_Category extends Mage_Catalog_Block_Product_Abstract {


        
    protected function _beforeToHtml() {

            $storeId = Mage::app()->getStore()->getId();
            $collection = Mage::getResourceModel('reports/product_collection')
                            ->addAttributeToSelect('*')
                            ->setStoreId($storeId)
                            ->addStoreFilter($storeId)
                            ->addViewsCount()
                            ->setPageSize($this->getModel()->getCatProductsLimit())
                            ->setCurPage(1)
                            ->setOrder('views_count', 'desc');

            Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
            Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);                
            
            $c = Mage::registry("current_category");

            if(isset($c)) {
            $catId = $c->getData('entity_id');
            } else {

                $current_product = Mage::registry('current_product');
                 
                if ($current_product->getId()) {
                    $categoryIds = $current_product->getCategoryIds();
                    $catId = $categoryIds[0];
                }

            }
            if ($catId>0) {
                $category = $this->getModel()->getCategory($catId);
                $collection->addCategoryFilter($category); 
            }


            $this->setProductCollection($collection);
            return parent::_beforeToHtml();
    }        
        
        

    public function getModel() {
        return Mage::getModel('mostpopular/data');
    }

}

