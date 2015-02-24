<?php

class Qaz_Qbanner_Model_Option_Position extends Varien_Object {
    const POSITION_CONTENT_TOP = 1;
    const POSITION_CONTENT_BOTTOM = 2;

    static public function getOptionArray() {
        return array(
            self::POSITION_CONTENT_TOP => Mage::helper('qbanner')->__('Content Top'),
            self::POSITION_CONTENT_BOTTOM => Mage::helper('qbanner')->__('After Header')
        );
    }

    static public function getOptionArrayEdit() {
        return array(
            array('value' => self::POSITION_CONTENT_TOP, 'label' => Mage::helper('qbanner')->__('Content Top')),
            array('value' => self::POSITION_CONTENT_BOTTOM, 'label' => Mage::helper('qbanner')->__('After Header'))
        );
    }
}