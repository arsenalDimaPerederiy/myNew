<?php
class Mementia_Pickup_Model_Resource_City extends Mage_Core_Model_Resource_Db_Abstract
{
    public function _construct()
    {
        $this->_init('store_pickup/city', 'city_id');
    }


}
