<?php

class Magestore_Megamenu_Model_Megamenu extends Mage_Core_Model_Abstract
{
    const CONTENT_ONLY = 1;
    const PRODUCT_LISTING = 2;
    const CATEGORY_LISTING = 3;
    const CONTACT_FORM = 4;
    const GROUP_CATEGORY_LISTING = 5;
    const ANCHOR_TEXT = 6;
    
    protected $_eventPrefix = 'megamenu_item';
    protected $_eventObject = 'megamenu_item';
    
    protected $_parentCategories;
    protected $_categoryCollection;


    protected $_html;


    public function _construct(){
		parent::_construct();
		$this->_init('megamenu/megamenu');
	}
    
    /**
     * get menu html from database
     * @return string
     */
    public function getMenuHtml(){
        if($this->_html)
            return $this->_html;
        $html = '';
        if($this->getId()){
            $mode = $this->getStyleShow();
            $html = $this->getContentOnlyHtml();
        }
        return $html;
    }
    
    public function getContentOnlyHtml(){
        $template = $this->getCodeTemplate();
        return $template;
    }
    
    public function getFeaturedProductIds(){
        $productIds = array();
        if($this->getId()){
            $productIds = explode(',', $this->getFeaturedProducts());
        }
        return $productIds;
    }
    
    public function getTemplateFilename(){
        $filename = '';
        if($this->getId()){
            $templateId = $this->getTemplateId();
            $template = Mage::getModel('megamenu/itemtemplate')->load($templateId);
            if($template->getMenuType() == self::CONTENT_ONLY){
                $filename = 'content_only/'.$template->getFilename();
            }elseif($template->getMenuType() == self::PRODUCT_LISTING){
                $filename = 'product_listing/'.$template->getFilename();
            }elseif($template->getMenuType() == self::CATEGORY_LISTING){
                $filename = 'category_listing/'.$template->getFilename();
            }elseif($template->getMenuType() == self::CONTACT_FORM){
                $filename = 'contact_form/'.$template->getFilename();
            }elseif ($template->getMenuType() == self::GROUP_CATEGORY_LISTING) {
                $filename = 'group_category_listing/'.$template->getFilename();
            }elseif ($template->getMenuType() == self::ANCHOR_TEXT) {
                $filename = 'anchor_text/'.$template->getFilename();
            }
        }
        return $filename;
    }
    
    public function getCategoryCollection($store = null){
         if(is_null($this->_categoryCollection)){
            $data = $this->getData('menu_item');
            $catIds = array(0);
            if($this->getId()){
                $catIds = explode(', ', $this->getCategories());
            }
            
            $collection = Mage::getResourceModel('catalog/category_collection')
                ->addAttributeToSelect('*')
                ->addFieldToFilter('entity_id', array('in' => $catIds))
                ->addFieldToFilter('is_active', 1);
            if(!is_null($store))
                $collection->setStore($store);
            $this->_categoryCollection = $collection;
        }
        return $this->_categoryCollection;
    }
    
    public function getParentCategories($store = null){
        if(is_null($this->_parentCategories)){
            $parentIds = array();
            $categories = $this->getCategoryCollection();
            $categoryIds = $categories->getAllIds();
            foreach($categories as $category){
                $parents = $category->getParentIds();
                if(count(array_intersect($parents, $categoryIds))== 0)
                        $parentIds[] = $category->getId();
            }
            $collection = Mage::getResourceModel('catalog/category_collection')
                ->addAttributeToSelect('*')
                ->addFieldToFilter('entity_id', array('in' => $parentIds))
                ->addFieldToFilter('is_active', 1);
            if(!is_null($store))
                $collection->setStore($store);
            $this->_parentCategories = $collection;
        }
        return $this->_parentCategories;
    }
    
    public function getCategoryIds(){
        $categoryIds = array();
        if($this->getId()){
            $stringIds = $this->getCategories();
            $categoryIds = explode(', ', $stringIds);
        }
        return $categoryIds;
    }
}