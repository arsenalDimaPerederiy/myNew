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

class ET_CurrencyManager_Block_Adminhtml_Formprice  extends Mage_Adminhtml_Block_Catalog_Product_Helper_Form_Price
{
	public function getEscapedValue($index=null)
	{
		$options=Mage::helper('currencymanager')->getOptions(array());
		$value = $this->getValue();

		if (!is_numeric($value))
		{
			return null;
		}

		if(isset($options["input_admin"]) && isset($options['precision']))
			return number_format($value, $options['precision'], null, '');

		return parent::getEscapedValue($index);
	}

}
