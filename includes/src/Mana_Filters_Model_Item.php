<?php
/**
 * @category    Mana
 * @package     Mana_Filters
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 * In-memory representation of a single option of a filter
 * @method bool getMSelected()
 * @method Mana_Filters_Model_Item setMSelected(bool $value)
 * @author Mana Team
 * Injected instead of standard catalog/layer_filter_item in Mana_Filters_Model_Filter_Attribute::_createItemEx()
 * method.
 */
class Mana_Filters_Model_Item extends Mage_Catalog_Model_Layer_Filter_Item {
    /**
     * Returns URL which should be loaded if person chooses to add this filter item into active filters
     * @return string
     * @see Mage_Catalog_Model_Layer_Filter_Item::getUrl()
     * This method is overridden by copying (method body was pasted from parent class and modified as needed). All
     * changes are marked with comments.
     */
    public function getUrl()
    {

    	// MANA BEGIN: add multivalue filter handling
    	$values = $this->getFilter()->getMSelectedValues(); // this could fail if called from some kind of standard filter
    	if (!$values) $values = array();
    	if (!in_array($this->getValue(), $values)) $values[] = $this->getValue();
    	// MANA END
        
    	$query = array(
        	// MANA BEGIN: save multiple values in URL as concatenated with '_'
            $this->getFilter()->getRequestVar()=>implode('_', $values),
            // MANA_END
            Mage::getBlockSingleton('page/html_pager')->getPageVarName() => null // exclude current page from urls
        );
        $params = array('_current'=>true, '_m_escape' => '', '_use_rewrite'=>true, '_query'=>$query, '_secure' => Mage::app()->getFrontController()->getRequest()->isSecure());
		
		
        return Mage::helper('mana_filters')->markLayeredNavigationUrl(Mage::getUrl('*/*/*', $params), '*/*/*', $params);
    }
	
    public function getSplashRemoveUrl()
    {

	if(Mage::registry('current_category')) {
		$_currentSeovalue = $this->getSeoValue();
		$_currentOptionId = $this->getValueString();
		$_attributeId = $this->getFilter()->getAttributeModel()->getAttributeId();
		$_attributeCode = $this->getFilter()->getAttributeModel()->getAttributeCode();
		$_currentCategoryId = Mage::registry('current_category')->getId();


		$_activeFilters = Mage::getSingleton('Mage_Catalog_Block_Layer_State')->getActiveFilters();
		
		$_filtersInLink = array();
		$_currentFiltersCount = 0;
		foreach($_activeFilters as $_activeFilter) {
		
			$_filterOptionId = $_activeFilter->getValueString();
			$_activeAttributeCode = $_activeFilter->getFilter()->getAttributeModel()->getAttributeCode();
			if(($_activeAttributeCode == $_attributeCode) && ($_filterOptionId == $_currentOptionId)) {
			} else {
				$_filtersInLink[$_activeAttributeCode][$_filterOptionId] = $_filterOptionId;
				$_currentFiltersCount++;
			}
		}
		$_countRemovefilters = $_currentFiltersCount;
		//$_filtersInLink[$_attributeCode][$_currentOptionId] = $_currentOptionId;
		//print_r($_filtersInLink);

		$read = Mage::getSingleton('core/resource')->getConnection('core_read');

		
		$_optionFilterArr = array();
		foreach($_filtersInLink as $attrCode => $attrValue) {
			foreach($attrValue as $optionIdValue) {
				$_optionFilterArr[] = '\'%'.'s:'.$this->getStrOrder($attrCode).':"'.$attrCode.'";a:2:{s:5:"value";a:1:{i:0;s:'.$this->getStrOrder($optionIdValue).':"'.$optionIdValue.'";}s:8:"operator";s:2:"OR";}'.'%\'';
			}
		}
		$_optionFilterArr[] = "'a:".$_countRemovefilters."%'";
		
		$_optionFilter = ''; 
		foreach($_optionFilterArr as $optionStr) {
			$_optionFilter .= '`option_filters` like '.$optionStr.' and ';
		}
		
		$_categoryFilter = 'a:2:{s:3:"ids";a:1:{i:0;s:'.$this->getStrOrder($_currentCategoryId).':"'.$_currentCategoryId.'";}s:8:"operator";s:3:"AND";}';
		
		$query = "select * from `splash_page` where ".$_optionFilter." `category_filters` = '".$_categoryFilter."'";
		//echo $query;
		$_getSplashPages = $read->fetchAll($query);
		
		if(count($_getSplashPages) > 0) {
		
			$_splashPageLoaded = Mage::getModel('splash/page')->load($_getSplashPages[0]['page_id']);
			return $_splashPageLoaded->getUrl();
		
		}
	}
		
		
		/*foreach($_filtersInLink as $attrCode => $attrValue) {
			foreach($attrValue as $optionIdValue) {
				$_optionFilter .= 's:'.$this->getStrOrder($attrCode).':"'.$attrCode.'";a:2:{s:5:"value";a:1:{i:0;s:'.$this->getStrOrder($optionIdValue).':"'.$optionIdValue.'";}s:8:"operator";s:2:"OR";}';
			}
		}
		$_optionFilter .= '}';
		
		$_categoryFilter = 'a:2:{s:3:"ids";a:1:{i:0;s:'.$this->getStrOrder($_currentCategoryId).':"'.$_currentCategoryId.'";}s:8:"operator";s:3:"AND";}';
		
		$query = "select * from `splash_page` where `option_filters` = '".$_optionFilter."' and `category_filters` = '".$_categoryFilter."'";
		$_getSplashPages = $read->fetchAll($query);
		
		if(count($_getSplashPages) > 0) {
		
			$_splashPageLoaded = Mage::getModel('splash/page')->load($_getSplashPages[0]['page_id']);
			return $_splashPageLoaded->getUrl();
		
		}*/
		
		/*
		$_splashPages = Mage::getResourceModel('splash/page_collection')->addFilter('status', array('eq' => 1))->load();
		
		foreach($_splashPages as $_splashPage){
		    
			$_noSplashPage = false;
			$_splashPageLoaded = Mage::getModel('splash/page')->load($_splashPage->getId());
			$_splashCategoryIds = $_splashPageLoaded->getCategoryIds();
			if($_splashCategoryIds[0] != Mage::registry('current_category')->getId()){
				$_noSplashPage = true;
			} else {
			//print_r($_splashPageLoaded->getOptionFilters());
			    $_splashFilterCount = 0;
				foreach($_splashPageLoaded->getOptionFilters() as $_splashCode => $_splashFilter){
					
					foreach($_splashFilter['value'] as $_splashOptionId){
						if($_filtersInLink[$_splashCode][$_splashOptionId] != $_splashOptionId){
							//echo $_splashCode."-".$_splashOptionId."-".$_splashPageLoaded->getId()."<br/>";
							$_noSplashPage = true;
							//break;
						} else {
							//echo $_splashCode."+".$_splashOptionId."-".$_splashPageLoaded->getId()."<br/>";
						}
						$_splashFilterCount++;
					}
					//if(!$_noSplashPage) break;
				}
			}
			//echo $_currentFiltersCount."==".$_splashFilterCount;
			if(!$_noSplashPage && ($_currentFiltersCount == $_splashFilterCount))
			return $_splashPageLoaded->getUrl();
		
		}*/


			
		
		if(Mage::registry('splash_page')){
			
			$_requestParams = array();
			

			
			
    		foreach(Mage::registry('splash_page')->getOptionFilters() as $_code => $_filter){
				
				
				$_request = $_code.'/';
				$_counter = 0;
				$_added = 0;
				foreach($_filter['value'] as $_optionId){
					
					$attribute = Mage::getSingleton('eav/config')->getAttribute('catalog_product', $_code);
					if ($attribute->usesSource()) {
						$options = $attribute->getSource()->getAllOptions(false); 

						foreach($options as $option) {
						   //echo '-'.$key."=".$_optionId.'<br/>';
							if(($option['value'] == $_optionId)){
								//if($_attributeCode == $_code && $_optionId == $this->getValueString()){
								//echo $_code."=".$_attributeCode."=".$_optionId."=".$_currentOptionId."<br/>";
								if(($_code == $_attributeCode) && ($_optionId == $_currentOptionId)) {
									$_added = 0;
								} else {
									$_optionValue = $option['label'];
									$_added = 1;
									$_counter++;
								}
								//}
							}
						
						}
					}
					
					if ($_added) {
						$_optionSeoValue = str_replace(' ','-',mb_convert_case($_optionValue, MB_CASE_LOWER, "UTF-8"));

						$_request .= ($_counter > 1) ? '_' : '';

						$_request .= $_optionSeoValue;
						//$_request .= $_optionSeoValue;
						
					}
					//echo count($_filter['value'])."=".$_counter."<br/>";

					
				}
				if($_counter > 0) {
					$_requestParams[$_code] = $_request;
				}
				//Mage::app()->getRequest()->setParam($_code, $_request);

			}
			//print_r($_requestParams);
			/*if(isset($_requestParams[$_attributeCode])) {
				$_requestParams[$_attributeCode] .= '_'.$_currentSeovalue;
			} else {
				$_requestParams[$_attributeCode] = $_attributeCode.'/'.$_currentSeovalue;
			}*/
            $_catUrl = Mage::registry('current_category')->getUrl();
			$_where = (count($_requestParams) > 0) ? '/where/' : '';
			$_temp = str_replace('.html',$_where, $_catUrl);
			$_filterUrl = $_temp.implode('/',$_requestParams).".html";
			
			return $_filterUrl;
			
		}

		


        return $this->getRemoveUrl();
    }
	
    public function getStrOrder($var) 
    {
        $str = (string)$var;
        
        return strlen($str);
    }
	
    public function getSplashUrl()
    {

	if(Mage::registry('current_category')) {
		$_currentSeovalue = $this->getSeoValue();
		$_currentOptionId = $this->getValueString();
		$_attributeId = $this->getFilter()->getAttributeModel()->getAttributeId();
		$_attributeCode = $this->getFilter()->getAttributeModel()->getAttributeCode();
		$_currentCategoryId = Mage::registry('current_category')->getId();

		
		$_activeFilters = Mage::getSingleton('Mage_Catalog_Block_Layer_State')->getActiveFilters();
		
		$_filtersInLink = array();
		$_currentFiltersCount = 1;
		foreach($_activeFilters as $_activeFilter) {
		
			$_filterOptionId = $_activeFilter->getValueString();
			$_activeAttributeCode = $_activeFilter->getFilter()->getAttributeModel()->getAttributeCode();
			$_filtersInLink[$_activeAttributeCode][$_filterOptionId] = $_filterOptionId;
			$_currentFiltersCount++;
		}
		$_filtersInLink[$_attributeCode][$_currentOptionId] = $_currentOptionId;
		//print_r($_filtersInLink);
		
		$read = Mage::getSingleton('core/resource')->getConnection('core_read');

		
		$_optionFilterArr = array();
		foreach($_filtersInLink as $attrCode => $attrValue) {
			foreach($attrValue as $optionIdValue) {
				$_optionFilterArr[] = '\'%'.'s:'.$this->getStrOrder($attrCode).':"'.$attrCode.'";a:2:{s:5:"value";a:1:{i:0;s:'.$this->getStrOrder($optionIdValue).':"'.$optionIdValue.'";}s:8:"operator";s:2:"OR";}'.'%\'';
			}
		}
		$_optionFilterArr[] = "'a:".$_currentFiltersCount."%'";
		
		$_optionFilter = ''; 
		foreach($_optionFilterArr as $optionStr) {
			$_optionFilter .= '`option_filters` like '.$optionStr.' and ';
		}
		
		$_categoryFilter = 's:3:"ids";a:1:{i:0;s:'.$this->getStrOrder($_currentCategoryId).':"'.$_currentCategoryId.'";';
		
		$query = "select * from `splash_page` where ".$_optionFilter." `category_filters` like '%".$_categoryFilter."%'";
		//echo '">'.$query;
		$_getSplashPages = $read->fetchAll($query);
		
		if(count($_getSplashPages) > 0) {
		
			$_splashPageLoaded = Mage::getModel('splash/page')->load($_getSplashPages[0]['page_id']);
			return $_splashPageLoaded->getUrl();
		
		}
		
	}	
		/*
		$_splashPages = Mage::getResourceModel('splash/page_collection')->addFilter('status', array('eq' => 1))->load();
		
		foreach($_splashPages as $_splashPage){
		    
			$_noSplashPage = false;
			$_splashPageLoaded = Mage::getModel('splash/page')->load($_splashPage->getId());
			$_splashCategoryIds = $_splashPageLoaded->getCategoryIds();
			if($_splashCategoryIds[0] != Mage::registry('current_category')->getId()){
				$_noSplashPage = true;
			} else {
			//print_r($_splashPageLoaded->getOptionFilters());
			    $_splashFilterCount = 0;
				foreach($_splashPageLoaded->getOptionFilters() as $_splashCode => $_splashFilter){
					
					foreach($_splashFilter['value'] as $_splashOptionId){
						if($_filtersInLink[$_splashCode][$_splashOptionId] != $_splashOptionId){
							//echo $_splashCode."-".$_splashOptionId."-".$_splashPageLoaded->getId()."<br/>";
							$_noSplashPage = true;
							break;

						} else {
							//echo $_splashCode."+".$_splashOptionId."-".$_splashPageLoaded->getId()."<br/>";
						}
						$_splashFilterCount++;
					}
					if($_noSplashPage) break;
				}
			}
			//echo $_currentFiltersCount."==".$_splashFilterCount;
			if(!$_noSplashPage && ($_currentFiltersCount == $_splashFilterCount))
			return $_splashPageLoaded->getUrl();
		
		
		}
		*/


			
		
		if(Mage::registry('splash_page')){
			
			$_requestParams = array();
			

			
			//$_deleted = 0;
    		foreach(Mage::registry('splash_page')->getOptionFilters() as $_code => $_filter){
			
				$_request = $_code.'/';
				$_counter = 1;
				foreach($_filter['value'] as $_optionId){
				    $_request .= ($_counter == 1) ? '' : '_';
					
					$attribute = Mage::getSingleton('eav/config')->getAttribute('catalog_product', $_code);
					if ($attribute->usesSource()) {
						$options = $attribute->getSource()->getAllOptions(true,true); 

						foreach($options as $option) {
						   //echo '-'.$key."=".$_optionId.'<br/>';
							if(($option['value'] == $_optionId)){
								//if($_attributeCode == $_code && $_optionId == $this->getValueString()){
									$_optionValue = $option['label'];
									//$_deleted = 1;
								//}
							}
						
						}
					}
					
					$_optionSeoValue = str_replace(' ','-',mb_convert_case($_optionValue, MB_CASE_LOWER, "UTF-8"));
					$_request .= $_optionSeoValue;
					//$_request .= $_optionSeoValue;
					$_counter++;
				}
				$_requestParams[$_code] = $_request;
				//Mage::app()->getRequest()->setParam($_code, $_request);

			}
			//print_r($_requestParams);
			if(isset($_requestParams[$_attributeCode])) {
				$_requestParams[$_attributeCode] .= '_'.$_currentSeovalue;
			} else {
				$_requestParams[$_attributeCode] = $_attributeCode.'/'.$_currentSeovalue;
			}
            $_catUrl = Mage::registry('current_category')->getUrl();
			$_temp = str_replace('.html','/where/', $_catUrl);
			$_filterUrl = $_temp.implode('/',$_requestParams).".html";
			
			return $_filterUrl;
			
		}

			


        return $this->getUrl();
    }
    
    /**
     * Returns URL which should be loaded if person chooses to add this filter item into active filters
     * @return string
     * @see Mage_Catalog_Model_Layer_Filter_Item::getUrl()
     * This method is overridden by copying (method body was pasted from parent class and modified as needed). All
     * changes are marked with comments.
     */
    public function getReplaceUrl()
    {
    	// MANA BEGIN: add multivalue filter handling
    	$values = array();
    	if (!in_array($this->getValue(), $values)) $values[] = $this->getValue();
    	// MANA END
        
    	$query = array(
        	// MANA BEGIN: save multiple values in URL as concatenated with '_'
            $this->getFilter()->getRequestVar()=>implode('_', $values),
            // MANA_END
            Mage::getBlockSingleton('page/html_pager')->getPageVarName() => null // exclude current page from urls
        );
        $params = array('_current'=>true, '_m_escape' => '', '_use_rewrite'=>true, '_query'=>$query, '_secure' => Mage::app()->getFrontController()->getRequest()->isSecure());
        return Mage::helper('mana_filters')->markLayeredNavigationUrl(Mage::getUrl('*/*/*', $params), '*/*/*', $params);
    }
    /** 
     * Returns URL which should be loaded if person chooses to remove this filter item from active filters
     * @return string
     * @see Mage_Catalog_Model_Layer_Filter_Item::getRemoveUrl()
     * This method is overridden by copying (method body was pasted from parent class and modified as needed). All
     * changes are marked with comments.
     */
    public function getRemoveUrl()
    {
    	// MANA BEGIN: add multivalue filter handling
    	if ($this->hasData('remove_url')) {
    	    return $this->getData('remove_url');
    	}

    	$values = $this->getFilter()->getMSelectedValues(); // this could fail if called from some kind of standard filter
    	if (!$values) $values = array();
    	unset($values[array_search($this->getValue(), $values)]);
    	if (count($values) > 0) {
	    	$query = array(
	            $this->getFilter()->getRequestVar()=>implode('_', $values),
	            Mage::getBlockSingleton('page/html_pager')->getPageVarName() => null // exclude current page from urls
	        );
    	}
    	else {
    		$query = array($this->getFilter()->getRequestVar()=>$this->getFilter()->getResetValue());
    	}
    	// MANA END
    	$params = array('_secure' => Mage::app()->getFrontController()->getRequest()->isSecure());
        $params['_current']     = true;
        $params['_use_rewrite'] = true;
        $params['_m_escape'] = '';
        $params['_query']       = $query;
        return Mage::helper('mana_filters')->markLayeredNavigationUrl(Mage::getUrl('*/*/*', $params), '*/*/*', $params);
    }
	public function getUniqueId($block) {
		/* @var $helper Mana_Filters_Helper_Data */ $helper = Mage::helper(strtolower('Mana_Filters'));
		return 'filter_'.$helper->getFilterName($block, $this->getFilter()).'_'.$this->getValue();
	}

	public function getSeoValue() {
	    $urlValue = $this->getValue();
	    /* @var $core Mana_Core_Helper_Data */ $core = Mage::helper(strtolower('Mana_Core'));
        if (Mage::app()->getRequest()->getParam('m-seo-enabled', true) &&
            ((string)Mage::getConfig()->getNode('modules/ManaPro_FilterSeoLinks/active')) == 'true' &&
            $this->getFilter()->getMode() != 'search'
        )
        {
            $url = Mage::getModel('manapro_filterseolinks/url');
            /* @var $url ManaPro_FilterSeoLinks_Model_Url */
            $urlValue = $url->encodeValue($this->getFilter()->getRequestVar(), $urlValue);
       }
       return $urlValue;
	}
}