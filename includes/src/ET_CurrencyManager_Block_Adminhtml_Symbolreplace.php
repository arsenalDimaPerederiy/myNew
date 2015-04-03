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

class ET_CurrencyManager_Block_Adminhtml_Symbolreplace extends Mage_Adminhtml_Block_System_Config_Form_Field
{
	protected $_addRowButtonHtml = array();
	protected $_removeRowButtonHtml = array();

	protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
	{
		$this->setElement($element);

		$html = '<div id="symbolreplace_template" style="display:none">';
		$html .= $this->_getRowTemplateHtml();
		$html .= '</div>';

		$html .= '<ul id="symbolreplace_container">';
		if ($this->_getValue('currency')) {
			foreach ($this->_getValue('currency') as $i=>$f)
			{
				if ($i)
				{
					$html .= $this->_getRowTemplateHtml($i);
				}
			}
		}
		$html .= '</ul>';
		$html .= $this->_getAddRowButtonHtml('symbolreplace_container',
			'symbolreplace_template', $this->__('Add currency specific options'));

		return $html;
	}

	protected function _getRowTemplateHtml($i=0)
	{

		$html = '<li><fieldset>';
		$html .= '<label>'.$this->__('Select currency:').' </label> ';

		$html .= '<select name="'.$this->getElement()->getName().'[currency][]" '.$this->_getDisabled().'>';

		$html .= '<option value="">'.$this->__('* Select currency').'</option>';
		foreach ($this->getAllowedCurrencies() as $currencyCode=>$currency)
		{
			$html .= '<option value="'.$currencyCode.'" '.$this->_getSelected('currency/'.$i, $currencyCode).' style="background:white;">'.$currency. " - " .$currencyCode.'</option>';
		}
		$html .= '</select>';

		$html .= '<label>'.$this->__('Precision:').' </label> ';
		$html .= '<input class="input-text" name="'.$this->getElement()->getName().'[precision][]" value="'.$this->_getValue('precision/'.$i).'" '.$this->_getDisabled().'/> ';
		$html .= '<p class="nm"><small>' . $this->__('Leave empty for global value use') . '</small></p>';

		$html .= '<label>'.$this->__('Cut Zero Decimals:').' </label> ';
		$html .= '<select class="input-text" name="'.$this->getElement()->getName().'[cutzerodecimal][]">';
		foreach(Mage::getModel("adminhtml/system_config_source_yesno")->toOptionArray() as $labelValue)
		{
			$html .= '<option value="'.$labelValue["value"].'" '.($this->_getValue('cutzerodecimal/'.$i)==$labelValue["value"]?'selected="selected"':'').'>'.$labelValue["label"]."</option>";
		}
		$html .= '</select>';


		$html .= '<label>'.$this->__('Suffix:').' </label> ';
		$html .= '<input class="input-text" name="'.$this->getElement()->getName().'[cutzerodecimal_suffix][]" value="'.$this->_getValue('cutzerodecimal_suffix/'.$i).'" '.$this->_getDisabled().'/> ';
		$html .= '<p class="nm"><small>' . $this->__('Leave empty for global value use') . '</small></p>';


		$html .= '<label>'.$this->__('Symbol position:').' </label> ';
		$html .= '<select class="input-text" name="'.$this->getElement()->getName().'[position][]">';
		foreach(Mage::getModel("currencymanager/typeposition")->toOptionArray() as $labelValue)
		{
			$html .= '<option value="'.$labelValue["value"].'" '.($this->_getValue('position/'.$i)==$labelValue["value"]?'selected="selected"':'').'>'.$labelValue["label"]."</option>";
		}
		$html .= '</select>';

		$html .= '<label>'.$this->__('Currency symbol use:').' </label> ';
		$html .= '<select class="input-text" name="'.$this->getElement()->getName().'[display][]">';
		foreach(Mage::getModel("currencymanager/typesymboluse")->toOptionArray() as $labelValue)
		{
			$html .= '<option value="'.$labelValue["value"].'" '.($this->_getValue('display/'.$i)==$labelValue["value"]?'selected="selected"':'').'>'.$labelValue["label"]."</option>";
		}
		$html .= '</select>';

		$html .= '<label>'.$this->__('Replace symbol to:').' </label> ';
		$html .= '<input class="input-text" name="'.$this->getElement()->getName().'[symbol][]" value="'.$this->_getValue('symbol/'.$i).'" '.$this->_getDisabled().'/> ';
		$html .= '<p class="nm"><small>' . $this->__('Leave empty for disable replace') . '</small></p>';

		$html .= '<label>'.$this->__('Replace Zero Price to:').' </label> ';
		$html .= '<input class="input-text" name="'.$this->getElement()->getName().'[zerotext][]" value="'.$this->_getValue('zerotext/'.$i).'" '.$this->_getDisabled().'/> ';
		$html .= '<p class="nm"><small>' . $this->__('Leave empty for disable replace') . '</small></p>';

		$html .= '<br /> <br />';
		$html .= $this->_getRemoveRowButtonHtml();
		//$html .= '</div>';
		$html .= '</fieldset></li>';

		return $html;
	}

	protected function getAllowedCurrencies()
	{

		if (!$this->hasData('allowed_currencies'))
		{
			$currencies = Mage::app()->getLocale()->getOptionCurrencies();
			$allowed_currency_codes = Mage::getSingleton('directory/currency')->getConfigAllowCurrencies();

			$formatet_currencies = array();
			foreach ($currencies as $k=>$currency)
				$formatet_currencies[$currency['value']]['label'] = $currency['label'];

			$allowed_currencies = array();
			foreach ($allowed_currency_codes as $k=>$currencyCode)
				$allowed_currencies[$currencyCode] = $formatet_currencies[$currencyCode]['label'];

			$this->setData('allowed_currencies', $allowed_currencies);
		}
		return $this->getData('allowed_currencies');
	}

	protected function _getDisabled()
	{
		return $this->getElement()->getDisabled() ? ' disabled' : '';
	}

	protected function _getValue($key)
	{
		return $this->getElement()->getData('value/'.$key);
	}

	protected function _getSelected($key, $value)
	{
		return $this->getElement()->getData('value/'.$key)==$value ? 'selected="selected"' : '';
	}

	protected function _getAddRowButtonHtml($container, $template, $title='Add')
	{
		if (!isset($this->_addRowButtonHtml[$container]))
		{
			$this->_addRowButtonHtml[$container] = $this->getLayout()->createBlock('adminhtml/widget_button')
				->setType('button')
				->setClass('add '.$this->_getDisabled())
				->setLabel($this->__($title))
				//$this->__('Add')
				->setOnClick("Element.insert($('".$container."'), {bottom: $('".$template."').innerHTML})")
				->setDisabled($this->_getDisabled())
				->toHtml();
		}
		return $this->_addRowButtonHtml[$container];
	}

	protected function _getRemoveRowButtonHtml($selector='li', $title='Remove')
	{
		if (!$this->_removeRowButtonHtml)
		{
			$this->_removeRowButtonHtml = $this->getLayout()->createBlock('adminhtml/widget_button')
				->setType('button')
				->setClass('delete v-middle '.$this->_getDisabled())
				->setLabel($this->__($title))
				//$this->__('Remove')
				->setOnClick("Element.remove($(this).up('".$selector."'))")
				->setDisabled($this->_getDisabled())
				->toHtml();
		}
		return $this->_removeRowButtonHtml;
	}
}