<?php

class Qaz_Qbanner_Model_Qbanner extends Mage_Core_Model_Abstract {

    public function _construct() {
        parent::_construct();
        $this->_init('qbanner/qbanner');
    }

    /*
     * Load image
     */

    public function getImageList() {
        if (!$this->hasData('image')) {
            $_object = $this->_getResource()->loadImage($this);
        }
        return $this->getData('image');
    }

}