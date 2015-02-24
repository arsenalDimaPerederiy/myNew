<?php

class CJM_ColorSelectorPlus_IndexController extends Mage_Core_Controller_Front_Action {

    public function indexAction() {
        
		$productId = Mage::app()->getRequest()->getParam('id');
		
		if(!$productId) 
		return false;
			
		$_product = Mage::getModel('catalog/product')->load($productId);
		$_images = Mage::helper('colorselectorplus')->decodeImagesForList($_product);
		
		$_colorImg = array();
		$_colorCheck = array();
		$i=0; $j=0;
		foreach($_images['morev'] as $key => $val){
		
			if(!in_array($val, $_colorCheck)) {
				$_colorCheck[] = $val;
				$_colorImg[$i]['color'] = $val;
				
				foreach($_images['morev'] as $keyIn => $valIn){
				
					if($_images['position'][$keyIn] < 3 && $valIn == $val){
					
						$_colorImg[$i]['image'.$_images['position'][$keyIn]] = $_images['image'][$keyIn];
					
					}
				
				}
				$i++;
			}
			
			
		
		}
		$this->getResponse()->setHeader('Content-type', 'application/json');
		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($_colorImg));
		
    }
	
    public function stockAction() {
        
		$productId = Mage::app()->getRequest()->getParam('id');
		
		if(!$productId) 
		return false;
			
		$_product = Mage::getModel('catalog/product')->load($productId);
		if ($_product->isAvailable()){
			$stock[0]['label'] = Mage::helper('catalog')->__('Available');
			$stock[0]['status'] = true;
		} else {
			$stock[0]['label'] = Mage::helper('catalog')->__('Not available');
			$stock[0]['status'] = false;
		}
		
		$this->getResponse()->setHeader('Content-type', 'application/json');
		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($stock));
		
    }
}