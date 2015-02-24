<?php
class Mementia_Pickup_Block_Adminhtml_Warehouses_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setDefaultSort('city_id');
        $this->setId('warehousesGrid');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        /** @var $collection Mementia_Pickup_Model_Resource_Warehouse_Collection */
        $collection = Mage::getModel('store_pickup/warehouse')
            ->getCollection();

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('warehouse_id',
            array(
                'header' => $this->__('ID'),
                'align' =>'right',
                'width' => '50px',
                'index' => 'warehouse_id'
            )
        );



        $this->addColumn('name_ru',
            array(
                 'header' => $this->__('Office'),
                 'index' => 'name_ru',
            )
        );
        $this->addColumn('city_id',
            array(
                'header' => $this->__('City'),
                'index' => 'city_id',
                'type'  => 'options',
                'options' => Mage::getModel('store_pickup/city')->getOptionArray(),
                'width'     => '250px'
            )
        );
        $this->addColumn('action',
            array(
                'header'    => Mage::helper('catalog')->__('Action'),
                'width'     => '100px',
                'type'      => 'action',
                'getter'     => 'getId',
                'actions'   => array(
                    array(
                        'caption' => Mage::helper('catalog')->__('Edit'),
                        'url'     => array(
                            'base'=>'*/*/edit',
                            'params'=>array('store'=>$this->getRequest()->getParam('store'))
                        ),
                        'field'   => 'id'
                    ),array(
                        'url'     => array(
                            'base'=>'*/*/delete',
                            'params'=>array('store'=>$this->getRequest()->getParam('store'))
                        ),
                        'field'   => 'id',
                        'caption'   => Mage::helper('adminhtml')->__('Delete'),
                        'confirm'   => Mage::helper('adminhtml')->__('Are you sure you want to do this?')
                    ),
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
            ));



        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

}
