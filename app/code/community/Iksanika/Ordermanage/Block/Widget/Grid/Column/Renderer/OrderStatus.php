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

class Iksanika_Ordermanage_Block_Widget_Grid_Column_Renderer_OrderStatus
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Options
{
    
    public function getStatusesByState($filter = '')
    {
        // status, label, state, is_default
        $collection = Mage::getModel('sales/order_status')->getCollection()->joinStates();
        $statusList = $collection->load();
        if($statusList && !empty($statusList))
        {
            $returnStatusList = array();
            foreach($statusList as $statusItem)
            {
                if($statusItem['state'] == $filter)
                {
                    $returnStatusList[$statusItem['status']] = $statusItem['label'];
                }
            }
            return $returnStatusList;
        }else
            return array();
    }    

    public function render(Varien_Object $row)
    {
//        echo get_class($this);
//        $options = $this->getColumn()->getOptions();
//        var_dump($options);
//        die();
        $statusList = $this->getStatusesByState($row->getData('state'));
        if (!empty($statusList) && is_array($statusList)) 
        {
            $value = $row->getData($this->getColumn()->getIndex());
            $out = '<select name="'.$this->getColumn()->getIndex().'">';
            foreach($statusList as $itemId => $item)
            {
                $out .= '<option value="'.$itemId.'" '.($value == $itemId ? 'selected':'').'>'.$this->escapeHtml($item).'</option>'; 
            }
            $out .= '</select>';
            return $out;
        }
        return '[SELECT is empty]';
    }
}
