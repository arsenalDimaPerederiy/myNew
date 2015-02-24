<?php
class Qaz_Qbanner_Block_Adminhtml_Qbanner extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_qbanner';
    $this->_blockGroup = 'qbanner';
    $this->_headerText = Mage::helper('qbanner')->__('Qbanner Manager');
    $this->_addButtonLabel = Mage::helper('qbanner')->__('Add New Banner');
    parent::__construct();
  }
}