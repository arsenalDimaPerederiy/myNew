<?php
/**
 * author: Dragan
 * To change this template use File | Settings | File Templates.
 */

class Mementia_Pickup_Block_Adminhtml_Cities_Edit_Form extends Mage_Adminhtml_Block_Widget_Form {
    protected function _prepareForm()
    {
        if (Mage::getSingleton('adminhtml/session')->getStorePickupCity())
        {
            $data = Mage::getSingleton('adminhtml/session')->getStorePickupCity();
            Mage::getSingleton('adminhtml/session')->getStorePickupCity(null);
        }
        elseif (Mage::registry('store_pickup_city'))
        {
            $data = Mage::registry('store_pickup_city')->getData();
        }
        else
        {
            $data = array();
        }

        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
            'method' => 'post',
        ));

        $form->setUseContainer(true);

        $this->setForm($form);

        $fieldset = $form->addFieldset('city_form', array(
            'legend' =>Mage::helper('store_pickup')->__('City Information')
        ));

        $fieldset->addField('name_ru', 'text', array(
            'label'     => Mage::helper('store_pickup')->__('Name'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'name_ru',
            'note'     => Mage::helper('store_pickup')->__('The name of the city.'),
        ));


        $form->setValues($data);

        return parent::_prepareForm();
    }
}