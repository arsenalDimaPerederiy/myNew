<?php
class Mementia_Expressdelivery_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_logFile = 'expressdelivery.log';

    /**
     * @param $string
     *
     * @return Mementia_Expressdelivery_Helper_Data
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
        return Mage::getStoreConfig("carriers/expressdelivery/$key", $storeId);
    }
    
    
    public function getCities() {
    	$apiKey = Mage::helper('expressdelivery')->getStoreConfig('api_key');
    	$apiUrl = Mage::helper('expressdelivery')->getStoreConfig('api_url');
    }
}