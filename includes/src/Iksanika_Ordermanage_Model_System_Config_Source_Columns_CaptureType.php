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

class Iksanika_Ordermanage_Model_System_Config_Source_Columns_CaptureType
{

    public function toOptionArray()
    {
        $captures = array();
        $captures[] = array('value' => Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE, 'label' => 'Capture Online');
        $captures[] = array('value' => Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE, 'label' => 'Capture Offline');
        $captures[] = array('value' => Mage_Sales_Model_Order_Invoice::NOT_CAPTURE, 'label' => 'Not Capture');
        return $captures;
    }
}