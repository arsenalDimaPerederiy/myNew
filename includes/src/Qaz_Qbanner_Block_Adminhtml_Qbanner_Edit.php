<?php

class Qaz_Qbanner_Block_Adminhtml_Qbanner_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'qbanner';
        $this->_controller = 'adminhtml_qbanner';
        
        $this->_updateButton('save', 'label', Mage::helper('qbanner')->__('Save Banner'));
        $this->_updateButton('delete', 'label', Mage::helper('qbanner')->__('Delete Banner'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('qbanner_data') && Mage::registry('qbanner_data')->getId() ) {
            return Mage::helper('qbanner')->__("Edit Banner '%s'", $this->htmlEscape(Mage::registry('qbanner_data')->getTitle()));
        } else {
            return Mage::helper('qbanner')->__('Add New Banner');
        }
    }
}