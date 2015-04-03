<?php

/**
 * Iksanika llc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.iksanika.com/products/IKS-LICENSE.txt
 *
 * @category   Iksanika
 * @package    Iksanika_Ordermanage
 * @copyright  Copyright (c) 2013 Iksanika llc. (http://www.iksanika.com)
 * @license    http://www.iksanika.com/products/IKS-LICENSE.txt
 */

class Iksanika_Ordermanage_Model_System_Config_Source_Columns_Country
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Select
{
     protected function _getOptions()
     {
        $options = Mage::getResourceModel('directory/country_collection')->load()->toOptionArray();
//        array_unshift($options, array('value'=>'', 'label'=>Mage::helper('customer')->__('All countries')));
        return $options;
    }
}