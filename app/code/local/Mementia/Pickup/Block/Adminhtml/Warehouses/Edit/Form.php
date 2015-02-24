<?php
/**
 * author: Dragan
 * To change this template use File | Settings | File Templates.
 */

class Mementia_Pickup_Block_Adminhtml_Warehouses_Edit_Form extends Mage_Adminhtml_Block_Widget_Form {
    protected function _prepareForm()
    {
        if (Mage::getSingleton('adminhtml/session')->getStorePickupWarehouse())
        {
            $data = Mage::getSingleton('adminhtml/session')->getStorePickupWarehouse();
            Mage::getSingleton('adminhtml/session')->getStorePickupWarehouse(null);
        }
        elseif (Mage::registry('store_pickup_warehouse'))
        {
            $data = Mage::registry('store_pickup_warehouse')->getData();
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

        $fieldset = $form->addFieldset('warehouse_form', array(
            'legend' =>Mage::helper('store_pickup')->__('Office Information')
        ));

        $fieldset->addField('city_id', 'select', array(
            'name' => 'city_id',
            'label' => $this->__('City'),
            'title' => $this->__('City'),
            'values' => Mage::getModel('store_pickup/city')->getOptionArray(),
            'note' => $this->__('Select office city'),
        ));

        $fieldset->addField('name_ru', 'text', array(
            'label'     => Mage::helper('store_pickup')->__('Name'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'name_ru',
            'note'     => Mage::helper('store_pickup')->__('The name of the office.'),
        ));


        $form->setValues($data);

        return parent::_prepareForm();
    }
}