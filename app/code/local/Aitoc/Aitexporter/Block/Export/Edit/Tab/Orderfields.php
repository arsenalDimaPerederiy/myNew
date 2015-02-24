<?php
/**
 * Orders Export and Import
 *
 * @category:    Aitoc
 * @package:     Aitoc_Aitexporter
 * @version      1.2.8
 * @license:     ou1zlIlUK4jGhUJZLohhJ5b8jdvumX7FXHqMPgZHkF
 * @copyright:   Copyright (c) 2014 AITOC, Inc. (http://www.aitoc.com)
 */
class Aitoc_Aitexporter_Block_Export_Edit_Tab_Orderfields extends Aitoc_Aitexporter_Block_Export_Edit_Tab_Abstract
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        
        $this->setForm($form);
        
        $this->setTemplate('aitexporter/orderfields.phtml');
        
        return parent::_prepareForm();
    }
    
    public function getOrderFields()
    {
        return Mage::getModel('aitexporter/export_type_order')->getOrderFields();
    }
    
    public function getAttributeCodeEavFlat($field)
    {
        return Mage::getModel('aitexporter/export_type_order')->getAttributeCodeEavFlat($field);
    }
}