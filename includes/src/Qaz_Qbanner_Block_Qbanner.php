<?php

class Qaz_Qbanner_Block_Qbanner extends Mage_Core_Block_Template {


    public function _prepareLayout() {
        if ($headBlock = $this->getLayout()->getBlock('head')) {
            //$headBlock->addJs('qaz/qbanner/jquery.1.5.1.js');
            $headBlock->addJs('qaz/qbanner/jqueryNoconfig.js');
            $headBlock->addJs('qaz/qbanner/jquery.slides.min.js');
            
            $headBlock->addCss('qaz/qbanner/css/global.css');
        }
        return parent::_prepareLayout();
    }

    public function getQbanner($position = null) {
        $storeId = Mage::app()->getStore()->getId();
        
        $collection = Mage::getModel('qbanner/qbanner')->getCollection()
                ->addFieldToFilter('status', 1)
        ;
        if (!Mage::app()->isSingleStoreMode()) {
            $collection->addStoreFilter($storeId);
        }
        if (Mage::registry('current_category')) {
            $_categoryId = Mage::registry('current_category')->getId();
            $collection->addCategoryFilter($_categoryId);
        } 
        if (Mage::app()->getFrontController()->getRequest()->getRouteName() == 'cms') {
            $_pageId = Mage::getBlockSingleton('cms/page')->getPage()->getPageId();
            $collection->addPageFilter($_pageId);
        }
        if ($position) {
            $collection->addFieldToFilter('position', $position);
        }
        
        return $collection;
    }
    /**
     *  Add Filter show in Module Name (Catalog , Customer , Checkout , ....)
     * @param type object $banner 
     */
    public function addShowInFilter($banner){
         $moduleName = Mage::app()->getRequest()->getModuleName();
         $showIn = $banner->getShowIn();
         if(strlen($showIn) > 0 and $moduleName != 'cms'){
             if(strpos("Show In ".$showIn, $moduleName) > 0)
                     return true;
             return false;
         }else{
             return true;
         }
    }
}
