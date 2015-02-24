<?php
/**
 * Orders Export and Import
 *
 * @category:    Aitoc
 * @package:     Aitoc_Aitexporter
 * @version      1.2.8
 * @license:     ou1zlIlUK4jGhUJZLohhJ5b8jdvumX7FXHqMPgZHkF
 * @copyright:   Copyright (c) 2014 AITOC, Inc. (http://www.aitoc.com)
 */
class Aitoc_Aitexporter_Model_Export_Order extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();

        $this->_init('aitexporter/export_order');
    }

    public function isOrdersExported($profile_id = 0)
    {
        $exportedOrders = $this->getCollection()
            ->loadByProfileId($profile_id);

        return sizeof($exportedOrders) == 1;
    } 
    
    public function assignOrders(Aitoc_Aitexporter_Model_Export $export)
    {
        $this->getResource()->assignOrders($export);

        return $this;
    }
}