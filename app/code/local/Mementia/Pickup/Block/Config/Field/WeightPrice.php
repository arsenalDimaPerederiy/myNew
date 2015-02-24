<?php
class Mementia_Pickup_Block_Config_Field_WeightPrice extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    public function __construct()
    {
        $this->addColumn('weight', array(
            'label' => Mage::helper('store_pickup')->__('Weight upper limit'),
            'style' => 'width:120px',
        ));
        $this->addColumn('price', array(
            'label' => Mage::helper('store_pickup')->__('Price'),
            'style' => 'width:120px',
        ));
        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('store_pickup')->__('Add rate');
        parent::__construct();
    }
}
