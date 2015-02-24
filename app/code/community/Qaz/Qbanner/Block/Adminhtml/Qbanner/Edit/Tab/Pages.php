<?php

/**
 * @author     kevin.magento@gmail.com
 */
class Qaz_Qbanner_Block_Adminhtml_QBanner_Edit_Tab_Pages extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $form = new Varien_Data_Form();
        $this->setForm($form);

        $fieldset = $form->addFieldset('qbanner_form', array('legend' => Mage::helper('qbanner')->__('Banner Pages')));
        $fieldset->addField('pages', 'multiselect', array(
            'label' => Mage::helper('qbanner')->__('Visible In'),
            'name' => 'pages[]',
            'values' => Mage::getSingleton('qbanner/option_pages')->getOptionArray(),
        ));
        $showIn = array(
            array('value'=>'catalog','label'=>'Category & Product Page'),
            array('value'=>'customer','label'=>'Customer Page'),
            array('value'=>'checkout','label'=>'Cart & Checkout Page'),
        );
        
        $fieldset->addField('show_in', 'multiselect', array(
            'label' => Mage::helper('qbanner')->__('Show In'),
            'name' => 'show_in[]',
            'values' => $showIn,
        ));
        if (Mage::getSingleton('adminhtml/session')->getQbannerData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getQbannerData());
            Mage::getSingleton('adminhtml/session')->setQbannerData(null);
        } elseif (Mage::registry('qbanner_data')) {
            $form->setValues(Mage::registry('qbanner_data')->getData());
        }

        return parent::_prepareForm();
    }

}
