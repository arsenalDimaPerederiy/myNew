<?php
class Mementia_Pickup_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_logFile = 'store_pickup.log';

    /**
     * @param $string
     *
     * @return Mementia_Pickup_Helper_Data
     */
    public function log($string)
    {
        if ($this->getStoreConfig('enable_log')) {
            Mage::log($string, null, $this->_logFile);
        }
        return $this;
    }

    /**
     * @param string $key
     * @param null $storeId
     *
     * @return mixed
     */
    public function getStoreConfig($key, $storeId = null)
    {
        return Mage::getStoreConfig("carriers/store_pickup/$key", $storeId);
    }

}