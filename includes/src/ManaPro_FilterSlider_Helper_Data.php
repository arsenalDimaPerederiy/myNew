<?php
/**
 * @category    Mana
 * @package     ManaPro_FilterSlider
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/* BASED ON SNIPPET: New Module/Helper/Data.php */
/**
 * Generic helper functions for ManaPro_FilterSlider module. This class is a must for any module even if empty.
 * @author Mana Team
 */
class ManaPro_FilterSlider_Helper_Data extends Mage_Core_Helper_Abstract {
	public function getUrl($name) {
		$query = array(
            $name=>'__0__,__1__',
            Mage::getBlockSingleton('page/html_pager')->getPageVarName() => null // exclude current page from urls
        );
        $params = array('_current' => true, '_m_escape' => '', '_use_rewrite' => true, '_query' => $query);
        return Mage::helper('mana_filters')->markLayeredNavigationUrl(Mage::getUrl('*/*/*', $params), '*/*/*', $params);
	}
	public function getClearUrl($name) {
		$query = array(
            $name=>null,
            Mage::getBlockSingleton('page/html_pager')->getPageVarName() => null // exclude current page from urls
        );
        $params = array('_current' => true, '_m_escape' => '', '_use_rewrite' => true, '_query' => $query);
        return Mage::helper('mana_filters')->markLayeredNavigationUrl(Mage::getUrl('*/*/*', $params), '*/*/*', $params);
	}
	
    public function getPriceUrl($name)
    {

			
		
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
			/*if(isset($_requestParams[$_attributeCode])) {
				$_requestParams[$_attributeCode] .= '_'.$_currentSeovalue;
			} else {
				$_requestParams[$_attributeCode] = $_attributeCode.'/'.$_currentSeovalue;
			}*/
            $_catUrl = Mage::registry('current_category')->getUrl();
			$_temp = str_replace('.html','/where/', $_catUrl);
			
			$_filterUrl = $_temp.implode('/',$_requestParams)."/price/__0__,__1__.html";
			
			return $_filterUrl;
			
		}



        return $this->getUrl($name);
    }
	
    public function getPriceClearUrl($name,$remove)
    {

			
		
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
			/*if(isset($_requestParams[$_attributeCode])) {
				$_requestParams[$_attributeCode] .= '_'.$_currentSeovalue;
			} else {
				$_requestParams[$_attributeCode] = $_attributeCode.'/'.$_currentSeovalue;
			}*/
            $_catUrl = Mage::registry('current_category')->getUrl();
			$_temp = str_replace('.html','/where/', $_catUrl);
			
			$_filterUrl = $_temp.implode('/',$_requestParams).".html";
			
			return $_filterUrl;
			
		}




        return $this->getClearUrl($name);
    }
	
}