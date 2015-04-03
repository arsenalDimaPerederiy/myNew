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

if (version_compare(Mage::getVersion(), '1.4', '<'))
{
	class ET_CurrencyManager_Block_Adminhtml_Heading extends Mage_Adminhtml_Block_Abstract implements Varien_Data_Form_Element_Renderer_Interface
	{
		public function render(Varien_Data_Form_Element_Abstract $element)
		{
			$useContainerId = $element->getData('use_container_id');
			return sprintf('<tr class="system-fieldset-sub-head" id="row_%s"><td colspan="5"><h4 id="%s">%s</h4></td></tr>',
				$element->getHtmlId(), $element->getHtmlId(), $element->getLabel()
			);
		}
	}
}
else
{
	class ET_CurrencyManager_Block_Adminhtml_Heading extends Mage_Adminhtml_Block_System_Config_Form_Field_Heading
	{
	}
}
