<?php

class Qaz_Qbanner_Block_Adminhtml_Qbanner_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('qbanner_form', array('legend' => Mage::helper('qbanner')->__('Banner  information')));

        $fieldset->addField('title', 'text', array(
            'label' => Mage::helper('qbanner')->__('Title'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'title',
        ));
        $fieldset->addField('width', 'text', array(
            'label' => Mage::helper('qbanner')->__('Width'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'width',
        ));
        $fieldset->addField('height', 'text', array(
            'label' => Mage::helper('qbanner')->__('Height'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'height',
        ));
        $fieldset->addField('duration', 'text', array(
            'label' => Mage::helper('qbanner')->__('Duration'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'duration',
        ));
        $fieldset->addField('position', 'select', array(
            'label' => Mage::helper('qbanner')->__('Position'),
            'name' => 'position',
            'values' => Mage::getSingleton('qbanner/option_position')->getOptionArrayEdit(),
        ));
        
        $fieldset->addField('effect', 'select', array(
            'label' => Mage::helper('qbanner')->__('Effects'),
            'name' => 'effect',
            'values' => array(
                array('value'=>'fade','label'=>'Fade'),
                array('value'=>'slide','label'=>'Slide'),
            ),
        ));
        
        $fieldset->addField('show_caption', 'select', array(
            'label' => Mage::helper('qbanner')->__('Show Caption'),
            'name' => 'show_caption',
            'values' => array(
                array('value'=>1,'label'=>'Yes'),
                array('value'=>0,'label'=>'No'),
            ),
        ));
        
        $fieldset->addField('show_pagination', 'select', array(
            'label' => Mage::helper('qbanner')->__('Show Pagination'),
            'name' => 'show_pagination',
            'values' => array(
                array('value'=>1,'label'=>'Yes'),
                array('value'=>0,'label'=>'No'),
            ),
        ));
         $fieldset->addField('show_next_prev', 'select', array(
            'label' => Mage::helper('qbanner')->__('Show Next & Prev'),
            'name' => 'show_next_prev',
            'values' => array(
                array('value'=>1,'label'=>'Yes'),
                array('value'=>0,'label'=>'No'),
            ),
        ));
        
        $fieldset->addField('auto_slide', 'select', array(
            'label' => Mage::helper('qbanner')->__('Auto Slide'),
            'name' => 'auto_slide',
            'values' => array(
                array('value'=>1,'label'=>'Yes'),
                array('value'=>0,'label'=>'No'),
            ),
        ));
        
        $fieldset->addField('mouseover_stop', 'select', array(
            'label' => Mage::helper('qbanner')->__('Mouseover Stop'),
            'name' => 'mouseover_stop',
            'values' => array(
                array('value'=>1,'label'=>'Yes'),
                array('value'=>0,'label'=>'No'),
            ),
        ));

        $fieldset->addField('status', 'select', array(
            'label' => Mage::helper('qbanner')->__('Status'),
            'name' => 'status',
            'values' => Mage::getSingleton('qbanner/option_status')->getOptionArrayEdit(),
        ));
        
//        if (!Mage::app()->isSingleStoreMode()) {
            $fieldset->addField('stores', 'multiselect', array(
                'label' => Mage::helper('qbanner')->__('Show In'),
                'required' => true,
                'name' => 'stores[]',
                'values' => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(),
            ));
//        } else {
//            $fieldset->addField('stores', 'hidden', array(
//                'name' => 'stores[]',
//                'value' => Mage::app()->getStore(true)->getId()
//            ));
//        }

        if (Mage::getSingleton('adminhtml/session')->getQbannerData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getQbannerData());
            Mage::getSingleton('adminhtml/session')->setQbannerData(null);
        } elseif (Mage::registry('qbanner_data')) {
            $form->setValues(Mage::registry('qbanner_data')->getData());
        }
        return parent::_prepareForm();
    }

}