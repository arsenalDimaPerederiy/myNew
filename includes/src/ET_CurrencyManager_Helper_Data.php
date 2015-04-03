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

class ET_CurrencyManager_Helper_Data extends Mage_Core_Helper_Abstract
{
	/**
	 * ZEND constants avalable in /lib/Zend/Currency.php
	 *
	 * NOTICE
	 *
	 * Magento ver 1.3.x - display - USE_SHORTNAME(3) by default
	 * Magento ver 1.4.x - display - USE_SYMBOL(2) by default
	 *
	 * position: 8 - standart; 16 - right; 32 - left
	 *
	 */

	protected $_options         = array();
	protected $_optionsadvanced = array();

	public function getOptions($options=array(), $old=false,$currency="default") // $old - for support Magento 1.3.x
	{
		$storeId=Mage::app()->getStore()->getStoreId();
		if ((!isset($this->_options[$storeId][$currency])) || (!isset($this->_optionsadvanced[$storeId][$currency])))
		{
			$this->setOptions($currency);
		}
		$newOptions         = $this->_options[$storeId][$currency];
		$newOptionsAdvanced = $this->_optionsadvanced[$storeId][$currency];

		if (!$old)
			$newOptions = $newOptions + $newOptionsAdvanced;

		// For JavaScript prices: Strange Symbol extracting in function getOutputFormat in file app/code/core/Mage/Directory/Model/Currency.php
		// For Configurable, Bundle and Simple with custom options
		// This causes problem if any currency has by default NO_SYMBOL
		// with this module can't change display value in this case
		if(isset($options["display"]))if($options["display"]==Zend_Currency::NO_SYMBOL)unset($newOptions["display"]);

		if (count($options) > 0)
			return $newOptions + $options;
		else
			return $newOptions;
	}


	public function isEnabled()
	{
		$config          = Mage::getStoreConfig('currencymanager/general');
		$storeId         = Mage::app()->getStore()->getStoreId();
		return ((($config['enabled'])&&($storeId>0))||(($config['enabledadm'])&&($storeId==0)));
	}


	public function setOptions($currency="default")
	{
		$config          = Mage::getStoreConfig('currencymanager/general');
		$moduleName      = Mage::app()->getRequest()->getModuleName();
		$options         = array();
		$optionsadvanced = array();
		$storeId         = Mage::app()->getStore()->getStoreId();
		if ($this->isEnabled())
		{
			if (!($config['excludecheckout'] & ($moduleName == 'checkout')))
				if (isset($config['precision'])) // precision must be in range -1 .. 30
					$options['precision'] = min(30, max(-1, (int)$config['precision']));

			if (isset($config['position']))$options['position'] = (int)$config['position'];
			if (isset($config['display']))$options['display']   = (int)$config['display'];
			if (isset($config['input_admin']))$optionsadvanced['input_admin']   = (int)$config['input_admin'];



			$optionsadvanced['excludecheckout'] = $config['excludecheckout'];
			$optionsadvanced['cutzerodecimal']  = $config['cutzerodecimal'];
			$optionsadvanced['cutzerodecimal_suffix']  = isset($config['cutzerodecimal_suffix'])?$config['cutzerodecimal_suffix']:"";


			// formating symbols from admin, preparing to use. Maybe can better :)
			// если в админке будут внесены несколько значений для одной валюты, то использоваться будет только одна
			if(isset($config['symbolreplace']))
			{
				$symbolreplace = unserialize($config['symbolreplace']);
				foreach($symbolreplace['currency'] as $symbolreplaceKey=>$symbolreplaceValue)
					if(strlen(trim($symbolreplace['currency'][$symbolreplaceKey]))==0)
					{
						unset($symbolreplace['currency'][$symbolreplaceKey]);
						unset($symbolreplace['precision'][$symbolreplaceKey]);
						unset($symbolreplace['cutzerodecimal'][$symbolreplaceKey]);
						unset($symbolreplace['cutzerodecimal_suffix'][$symbolreplaceKey]);
						unset($symbolreplace['position'][$symbolreplaceKey]);
						unset($symbolreplace['display'][$symbolreplaceKey]);
						unset($symbolreplace['symbol'][$symbolreplaceKey]);
						unset($symbolreplace['zerotext'][$symbolreplaceKey]);
					}


				if(count($symbolreplace['currency'])>0)
				{
					$displayCurrencyCode = $currency; //geting current display currency

					$configSubData = array_combine($symbolreplace['currency'], $symbolreplace['cutzerodecimal']);
					if (array_key_exists($displayCurrencyCode, $configSubData))
						$optionsadvanced['cutzerodecimal'] = (int)$configSubData[$displayCurrencyCode];



					if(isset($symbolreplace['cutzerodecimal_suffix'])){
						$configSubData = array_combine($symbolreplace['currency'], $symbolreplace['cutzerodecimal_suffix']);
						if (array_key_exists($displayCurrencyCode, $configSubData))
							if($configSubData[$displayCurrencyCode]!=""){
								$optionsadvanced["cutzerodecimal_suffix"] = $configSubData[$displayCurrencyCode];
							}
					}

					$configSubData = array_combine($symbolreplace['currency'], $symbolreplace['position']);
					if (array_key_exists($displayCurrencyCode, $configSubData))
						$options['position'] = (int)$configSubData[$displayCurrencyCode];

					$configSubData = array_combine($symbolreplace['currency'], $symbolreplace['display']);
					if (array_key_exists($displayCurrencyCode, $configSubData))
						$options['display'] = (int)$configSubData[$displayCurrencyCode];

					$configSubData = array_combine($symbolreplace['currency'], $symbolreplace['symbol']);
					if (array_key_exists($displayCurrencyCode, $configSubData))
						if($configSubData[$displayCurrencyCode]!="")
							$options['symbol'] = $configSubData[$displayCurrencyCode];

					if (!($config['excludecheckout'] & ($moduleName == 'checkout')))
					{
						$configSubData = array_combine($symbolreplace['currency'], $symbolreplace['zerotext']);
						if (array_key_exists($displayCurrencyCode, $configSubData))
							if($configSubData[$displayCurrencyCode]!="")
								$optionsadvanced['zerotext'] = $configSubData[$displayCurrencyCode];

						$configSubData = array_combine($symbolreplace['currency'], $symbolreplace['precision']);
						if (array_key_exists($displayCurrencyCode, $configSubData))
							if($configSubData[$displayCurrencyCode]!="")
								$options['precision'] = min(30, max(-1, (int)$configSubData[$displayCurrencyCode]));
					}

				}
			}
		} // end NOT ENABLED

		$this->_options[$storeId][$currency]         = $options;
		$this->_optionsadvanced[$storeId][$currency] = $optionsadvanced;
		if(!isset($this->_options[$storeId]["default"]))
		{
			$this->_options[$storeId]["default"]         = $options;
			$this->_optionsadvanced[$storeId]["default"] = $optionsadvanced;
		}

		return $this;
	}

}
