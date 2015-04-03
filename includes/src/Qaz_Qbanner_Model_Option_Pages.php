<?php

class Qaz_Qbanner_Model_Option_Pages extends Varien_Object {

    public function getOptionArray() {
        $_collection = Mage::getSingleton('cms/page')->getCollection()
                ->addFieldToFilter('is_active', 1);

        $_result = array();
        foreach ($_collection as $item) {
            $data = array(
                'value' => $item->getData('page_id'),
                'label' => $item->getData('title'));
            $_result[] = $data;
        }
        return $_result;
    }

}