<?php
class Mementia_Expressdelivery_Block_Adminhtml_Cities extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'expressdelivery';
        $this->_controller = 'adminhtml_cities';
        $this->_headerText = $this->__('Manage Cities');

        parent::__construct();

//        $this->_removeButton('add');
//        $this->_addButton('synchronize', array(
//            'label'     => $this->__('Synchronize with API'),
//            'onclick'   => 'setLocation(\'' . $this->getUrl('*/*/synchronize') .'\')'
//        ));
    }
}
