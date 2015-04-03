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

class ET_CurrencyManager_Model_Currency extends Mage_Directory_Model_Currency
{

/*
public function format($price, $options=array(), $includeContainer = true, $addBrackets = false)
{
	$config = Mage::getStoreConfig('currencymanager/general');
	$store	= Mage::app()->getStore()->getStoreId();

	if (($config['enabledadm'])and($store=0))
		$options = Mage::helper('currencymanager')->getOptions($options);

	if (($config['enabled'])and($store>0))
		$options = Mage::helper('currencymanager')->getOptions($options);

	return parent::format($price, $options, $includeContainer, $addBrackets);
}
*/

	public function getOutputFormat()
	{
		$formated = $this->formatTxt(1);
		$number = $this->formatTxt(1, array('display'=>Zend_Currency::NO_SYMBOL));
		return str_replace($number, '%s', $formated);
	}

	public function formatTxt($price, $options=array())
	{

		$answer = parent::formatTxt($price, $options);
		if(Mage::helper('currencymanager')->isEnabled())
		{
			$store           = Mage::app()->getStore()->getStoreId();
			$moduleName      = Mage::app()->getRequest()->getModuleName();
			$optionsAdvanced = Mage::helper('currencymanager')->getOptions($options,false,$this->getCurrencyCode());
			$options         = Mage::helper('currencymanager')->getOptions($options,true,$this->getCurrencyCode());

			$answer = parent::formatTxt($price, $options);
//print "answer: " . $answer . "<br />";
			$suffix = isset($optionsAdvanced['cutzerodecimal_suffix'])?$optionsAdvanced['cutzerodecimal_suffix']:"";

			if (count($options)>0)
			{

				if (($moduleName == 'admin'))
				{
					$answer = parent::formatTxt($price, $options);
				}

				if (!(($moduleName == 'checkout') & $optionsAdvanced['excludecheckout']))
				{

					if($price==0)
						if(isset($optionsAdvanced['zerotext']))
							if($optionsAdvanced['zerotext']!="")return $optionsAdvanced['zerotext'];

					if ($optionsAdvanced['cutzerodecimal'] && (round($price,$optionsAdvanced['precision']) == round($price,0))) // cut decimal if it = 0
					{
						if ((isset($suffix)) && (strlen($suffix)>0)) // if need to add suffix
						{
							// searching for fully formatted currency without currency symbol
							$options['display']   = Zend_Currency::NO_SYMBOL;
							$answerBlank          = $this->_localizeNumber(parent::formatTxt($price, $options), $options);

//print "answerBlank: " . $answerBlank . "<br />";
							// searching for fully formatted currency without currency symbol and rounded to int
							$options['precision'] = 0;
							$answerRound          = $this->_localizeNumber(parent::formatTxt($price, $options), $options);
//print "answerRound: " . $answerRound . "<br />";

							// replace cutted decimals with suffix
							$answer=str_replace($answerBlank,$answerRound.$suffix,$answer);
						}
						else // esle only changing precision
						{
							$options['precision'] = 0;
							$answer = parent::formatTxt($price, $options);
						}
					}
				}
			}
		}

		return $answer;
	}

	protected function _localizeNumber($number, $options = array())
	{
		$options = Mage::helper('currencymanager')->getOptions($options,true,$this->getCurrencyCode());
		if ($options['display'] == Zend_Currency::NO_SYMBOL)
		{
			// in Zend_Currency toCurrency() function are stripped unbreakable spaces only for currency without Currency Symbol
			return $number;
		}
		else
		{
			$locale = Mage::app()->getLocale()->getLocaleCode();
			$format = Zend_Locale_Data::getContent($locale, 'decimalnumber');
			$number = Zend_Locale_Format::getNumber($number, array('locale'    => $locale,
															'number_format'    => $format,
															'precision'        => 0));
			return Zend_Locale_Format::toNumber($number, array('locale'        => $locale,
															'number_format'    => $format,
															'precision'        => 0));
		}
	}

}
