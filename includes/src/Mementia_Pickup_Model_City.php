<?php
class Mementia_Pickup_Model_City extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        $this->_init('store_pickup/city');
    }

    public function getOptionArray()
    {
        $options = array();

        $collection = $this->getCollection();
        while ($city = $collection->fetchItem()) {
            $options[$city->getId()] = $city->getNameRu();
        }

        asort($options);

        return $options;
    }
}
