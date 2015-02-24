<?php
/**
 * author: Dragan
 * To change this template use File | Settings | File Templates.
 */

class Mementia_Expressdelivery_Block_Adminhtml_Cities_Edit_Form extends Mage_Adminhtml_Block_Widget_Form {
    protected function _prepareForm()
    {
        if (Mage::getSingleton('adminhtml/session')->getExpressdeliveryCity())
        {
            $data = Mage::getSingleton('adminhtml/session')->getExpressdeliveryCity();
            Mage::getSingleton('adminhtml/session')->getExpressdeliveryCity(null);
        }
        elseif (Mage::registry('expressdelivery_city'))
        {
            $data = Mage::registry('expressdelivery_city')->getData();
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
            'legend' =>Mage::helper('expressdelivery')->__('City Information')
        ));

        $fieldset->addField('name_ru', 'text', array(
            'label'     => Mage::helper('expressdelivery')->__('Name'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'name_ru',
            'note'     => Mage::helper('expressdelivery')->__('The name of the city.'),
        ));


        $form->setValues($data);

        return parent::_prepareForm();
    }
}