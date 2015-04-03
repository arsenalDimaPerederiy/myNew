<?php
class Mementia_Pickup_Block_Adminhtml_Cities extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'store_pickup';
        $this->_controller = 'adminhtml_cities';
        $this->_headerText = $this->__('Manage Cities');

        parent::__construct();

    }
}
