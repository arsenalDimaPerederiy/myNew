<?php
/**
 * ET Web Solutions
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade to newer
 * versions in the future.
 *
 * @category   ET
 * @package    ET_CurrencyManager
 * @copyright  Copyright (c) 2011 ET Web Solutions (http://etwebsolutions.com)
 * @contacts   support@etwebsolutions.com
 * @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class ET_CurrencyManager_Block_Js  extends Mage_Core_Block_Template
{
	public function getJsonConfig()
	{
		if(method_exists(Mage::helper('core'),'jsonEncode')){ 
			return Mage::helper('core')->jsonEncode(Mage::helper('currencymanager')->getOptions(array(),false,Mage::app()->getBaseCurrencyCode()));
		}
		else {
			return Zend_Json::encode(Mage::helper('currencymanager')->getOptions(array(),false,Mage::app()->getBaseCurrencyCode()));
		}

		
		
	}
}

