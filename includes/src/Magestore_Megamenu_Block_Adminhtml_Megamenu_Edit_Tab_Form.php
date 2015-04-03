<?php

class Magestore_Megamenu_Block_Adminhtml_Megamenu_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm(){
        $form = new Varien_Data_Form();
        $this->setForm($form);

        if (Mage::getSingleton('adminhtml/session')->getMegamenuData()){
                $data = Mage::getSingleton('adminhtml/session')->getMegamenuData();
                Mage::getSingleton('adminhtml/session')->setMegamenuData(null);
        }elseif(Mage::registry('megamenu_data'))
                $data = Mage::registry('megamenu_data')->getData();
        
        $fieldset = $form->addFieldset('megamenu_form', array('legend'=>Mage::helper('megamenu')->__('General information')));

        $fieldset->addField('name_menu', 'text', array(
                'label'		=> Mage::helper('megamenu')->__('Name'),
                'class'		=> 'required-entry',
                'required'	=> true,
                'name'		=> 'name_menu',
        ));
        
        if (!Mage::app()->isSingleStoreMode()) {
            $fieldset->addField('stores', 'multiselect', array(
                'name'      => 'stores[]',
                'label'     => Mage::helper('megamenu')->__('Store View'),
                'title'     => Mage::helper('megamenu')->__('Store View'),
                'required'  => true,
                'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
            ));
        }
        else {
			$data['stores'] = Mage::app()->getStore(true)->getId();
            $fieldset->addField('stores', 'hidden', array(
                'name'      => 'stores[]',
            ));					
        }
         
        $fieldset->addField('link', 'text', array(
            'label'		=> Mage::helper('megamenu')->__('Link'),		
            'required'	=> false,
            'name'		=> 'link',
        ));
        
        $fieldset->addField('sort_order', 'text', array(
            'label'		=> Mage::helper('megamenu')->__('Sort Order'),
            'name'		=> 'sort_order'
        ));                 
        $fieldset->addField('status', 'select', array(
                'label'		=> Mage::helper('megamenu')->__('Status'),
                'name'		=> 'status',
                'values'	=> Mage::getSingleton('megamenu/status')->getOptionHash(),
        ));
        
        $form->setValues($data);
        return parent::_prepareForm();
    }
}