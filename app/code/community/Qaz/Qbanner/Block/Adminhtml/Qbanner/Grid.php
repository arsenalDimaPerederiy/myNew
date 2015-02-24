<?php

class Qaz_Qbanner_Block_Adminhtml_Qbanner_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('qbannerGrid');
        $this->setDefaultSort('qbanner_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel('qbanner/qbanner')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    
    protected function _getStore() {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    protected function _prepareColumns() {
        $this->addColumn('qbanner_id', array(
            'header' => Mage::helper('qbanner')->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'qbanner_id',
        ));

        $this->addColumn('title', array(
            'header' => Mage::helper('qbanner')->__('Title'),
            'align' => 'left',
            'index' => 'title',
        ));

        $this->addColumn('position', array(
            'header' => Mage::helper('qbanner')->__('Position'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'position',
            'type' => 'options',
            'options' => Mage::getSingleton('qbanner/option_position')->getOptionArray(),
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('qbanner')->__('Status'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'status',
            'type' => 'options',
            'options' => Mage::getSingleton('qbanner/option_status')->getOptionArray(),
        ));

        $this->addColumn('action', array(
            'header' => Mage::helper('qbanner')->__('Action'),
            'width' => '100',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => Mage::helper('qbanner')->__('Edit'),
                    'url' => array('base' => '*/*/edit'),
                    'field' => 'id'
                )
            ),
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
            'is_system' => true,
        ));
        return parent::_prepareColumns();
    }

    protected function _prepareMassaction() {
        $this->setMassactionIdField('qbanner_id');
        $this->getMassactionBlock()->setFormFieldName('qbanner');

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => Mage::helper('qbanner')->__('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => Mage::helper('qbanner')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('qbanner/option_status')->getOptionArray();

        array_unshift($statuses, array('label' => '', 'value' => ''));
        $this->getMassactionBlock()->addItem('status', array(
            'label' => Mage::helper('qbanner')->__('Change status'),
            'url' => $this->getUrl('*/*/massStatus', array('_current' => true)),
            'additional' => array(
                'visibility' => array(
                    'name' => 'status',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => Mage::helper('qbanner')->__('Status'),
                    'values' => $statuses
                )
            )
        ));
        return $this;
    }

    public function getRowUrl($row) {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

}