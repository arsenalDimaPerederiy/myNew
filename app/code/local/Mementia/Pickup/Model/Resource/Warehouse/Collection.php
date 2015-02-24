<?php
class Mementia_Pickup_Model_Resource_Warehouse_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    public function _construct()
    {
        $this->_init('store_pickup/warehouse');
    }


}
