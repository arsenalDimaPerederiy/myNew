<?php

class Qaz_Qbanner_Block_Adminhtml_Qbanner_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

    public function __construct() {
        parent::__construct();
        $this->setId('qbanner_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('qbanner')->__('Banner Information'));
    }

    protected function _beforeToHtml() {
        $this->addTab('form_section', array(
            'label' => Mage::helper('qbanner')->__('Banner Information'),
            'title' => Mage::helper('qbanner')->__('Banner Information'),
            'content' => $this->getLayout()->createBlock('qbanner/adminhtml_qbanner_edit_tab_form')->toHtml(),
        ));
        $this->addTab('image_section', array(
            'label' => Mage::helper('qbanner')->__('Banner Images'),
            'title' => Mage::helper('qbanner')->__('Banner Images'),
            'content' => $this->getLayout()->createBlock('qbanner/adminhtml_qbanner_edit_tab_image')->toHtml(),
        ));
        $this->addTab('page_section', array(
            'label' => Mage::helper('qbanner')->__('Display on Pages'),
            'title' => Mage::helper('qbanner')->__('Display on Pages'),
            'content' => $this->getLayout()->createBlock('qbanner/adminhtml_qbanner_edit_tab_pages')->toHtml(),
        ));
        $this->addTab('category_section', array(
            'label' => Mage::helper('qbanner')->__('Display on Categories'),
            'title' => Mage::helper('qbanner')->__('Display on Categories'),
            'content' => $this->getLayout()->createBlock('qbanner/adminhtml_qbanner_edit_tab_category')->toHtml(),
        ));
        return parent::_beforeToHtml();
    }

}