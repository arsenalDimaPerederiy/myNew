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
 * @copyright  Copyright (c) 2010 ET Web Solutions (http://etwebsolutions.com)
 * @contacts   support@etwebsolutions.com
 * @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class ET_CurrencyManager_Model_Typesymboluse extends Varien_Object
{
	/**
	 * Options getter
	 *
	 * @return array
	 */
	public function toOptionArray()
	{
		return array(
			array('value' => 1, 'label'=>Mage::helper('currencymanager')->__('Do not use')),
			array('value' => 2, 'label'=>Mage::helper('currencymanager')->__('Use symbol')),
			array('value' => 3, 'label'=>Mage::helper('currencymanager')->__('Use short name')),
			array('value' => 4, 'label'=>Mage::helper('currencymanager')->__('Use name'))
		);
	}
}
