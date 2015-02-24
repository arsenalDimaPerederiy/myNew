<?php

class Mementia_Ext_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getCarriers($isMultiSelect = false)
    {
        $methods = Mage::getSingleton('shipping/config')->getActiveCarriers();

        $options = array();

        foreach($methods as $_code => $_method)
        {

            if($_code != "googlecheckout"){
                if(!$_title = Mage::getStoreConfig("carriers/$_code/title"))
                    $_title = $_code;
                $options[] = array('code' => $_code, 'label' => $_title, 'sort_order' => $_method->getSortOrder());
            }
        }

        if($isMultiSelect)
        {
            array_unshift($options, array('value'=>'', 'label'=> Mage::helper('adminhtml')->__('--Please Select--')));
        }
        usort($options, array('Mementia_Ext_Helper_Data','cmp_by_sortOrder'));
        return $options;
    }

    private static function cmp_by_sortOrder($a, $b) {
        return $a["sort_order"] - $b["sort_order"];
    }
}
