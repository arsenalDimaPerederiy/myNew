<?php

/**
 * Iksanika llc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.iksanika.com/products/IKS-LICENSE.txt
 *
 * @category   Iksanika
 * @package    Iksanika_Ordermanage
 * @copyright  Copyright (c) 2013 Iksanika llc. (http://www.iksanika.com)
 * @license    http://www.iksanika.com/products/IKS-LICENSE.txt
 */

class Iksanika_Ordermanage_Model_System_Config_Source_Columns_CarrierCode
{

    public function toOptionArray()
    {
        //$shippmentMethods = Mage::getSingleton('shipping/config')->getActiveCarriers();
        
        $carriers = array();
        $carrierInstances = Mage::getSingleton('shipping/config')->getAllCarriers();
        $carriers['custom'] = Mage::helper('sales')->__('Custom Value');
        foreach ($carrierInstances as $code => $carrier) 
        {
            if ($carrier->isTrackingAvailable()) 
            {
                $carriers[] = array('value' => $code, 'label' => $carrier->getConfigData('title'));
            }
        }
        
        /*
        $methods = Mage::getSingleton('shipping/config')->getActiveCarriers();
        $shipping = array();
        foreach($methods as $_ccode => $_carrier) {
            if($_methods = $_carrier->getAllowedMethods())  {
                if(!$_title = Mage::getStoreConfig("carriers/$_ccode/title"))
                    $_title = $_ccode;
                foreach($_methods as $_mcode => $_method)   {
                    $_code = $_ccode . '_' . $_mcode;
                    $shipping[$_code]=array('title' => $_method,'carrier' => $_title);
                }
            }
        }
        echo "\n";print_r($shipping);
         */
        /*
        $columns    = array();
        foreach($shippmentMethods as $shippmentItemCode => $shippmentItem)
        {
            //var_dump($shippmentItem->getData());
            $columns[] = array('value' => $shippmentItemValue, 'label' => $shippmentItemLabel);
        }
        var_dump($shippmentMethods);die();
         */
        return $carriers;
    }
}