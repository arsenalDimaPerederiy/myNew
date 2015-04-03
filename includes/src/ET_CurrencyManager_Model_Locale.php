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

class ET_CurrencyManager_Model_Locale extends Mage_Core_Model_Locale
{
	public function currency($ccode)
	{
		$admcurrency = parent::currency($ccode);
		$options = Mage::helper('currencymanager')->getOptions(array(),true,$ccode);
		$admcurrency->setFormat($options, $ccode);

		return $admcurrency;
	}


	public function getJsPriceFormat()
	{
		// For JavaScript prices
		$parentFormat=parent::getJsPriceFormat();
		$options = Mage::helper('currencymanager')->getOptions(array());
		if (isset($options["precision"]))
		{
			$parentFormat["requiredPrecision"] = $options["precision"];
			$parentFormat["precision"] = $options["precision"];
		}

		return $parentFormat;
	}
}
