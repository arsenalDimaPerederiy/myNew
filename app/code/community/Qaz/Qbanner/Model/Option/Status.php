<?php

class Qaz_Qbanner_Model_Option_Status extends Varien_Object {
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 2;

    static public function getOptionArray() {
        return array(
            self::STATUS_ENABLED => Mage::helper('qbanner')->__('Active'),
            self::STATUS_DISABLED => Mage::helper('qbanner')->__('Non Active')
        );
    }

    static public function getOptionArrayEdit() {
        return array(
            array('value' => self::STATUS_DISABLED, 'label' => Mage::helper('qbanner')->__('Non Active')),
            array('value' => self::STATUS_ENABLED, 'label' => Mage::helper('qbanner')->__('Active')),
        );
    }

}