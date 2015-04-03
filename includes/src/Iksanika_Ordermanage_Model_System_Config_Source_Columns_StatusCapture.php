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

class Iksanika_Ordermanage_Model_System_Config_Source_Columns_StatusCapture
{

    public function toOptionArray()
    {
        $statusList = Mage::helper('ordermanage')->getStatusesByState('capture');
        
        $columns    = array(array('value' => 'default', 'label' => 'Magento Default'));
        foreach($statusList as $statusItemValue => $statusItemLabel)
        {
            $columns[] = array('value' => $statusItemValue, 'label' => $statusItemLabel);
        }
        return $columns;
    }
}