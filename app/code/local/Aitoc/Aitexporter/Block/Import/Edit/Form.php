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
class Aitoc_Aitexporter_Block_Import_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id'     => 'edit_form', 
            'action' => $this->getUrl('*/*/save'), 
            'method' => 'post', 
            'enctype' => 'multipart/form-data',
            ));

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }    
}