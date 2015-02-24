<?php
/**
 * author: Dragan
 */

class Mementia_Pickup_Block_Adminhtml_Cities_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'store_pickup';
        $this->_controller = 'adminhtml_cities';
        $this->_mode = 'edit';

        $this->_addButton('save_and_continue', array(
            'label' => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick' => 'saveAndContinueEdit()',
            'class' => 'save',
        ), -100);
        $this->_updateButton('save', 'label', Mage::helper('store_pickup')->__('Save City'));

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('form_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'edit_form');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'edit_form');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if (Mage::registry('store_pickup_city') && Mage::registry('store_pickup_city')->getId())
        {
            return Mage::helper('store_pickup')->__('Edit City "%s"', $this->escapeHtml(Mage::registry('store_pickup_city')->getNameRu()));
        } else {
            return Mage::helper('store_pickup')->__('New City');
        }
    }
}