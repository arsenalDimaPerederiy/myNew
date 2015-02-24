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

class Iksanika_Ordermanage_Block_Widget_Grid_Column_Renderer_Shippment
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Options
{
    /*
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
    
    
    */
    
    
    
    
    
    
    
    
    
    
    
    
    public function render(Varien_Object $row)
    {
        $html   =   '';
        $order  =   Mage::getModel('sales/order')->load($row->getId());
        
        if ($order->canShip()) 
        {
            
            $default    =  Mage::getStoreConfig('ordermanage/ship/carrier');
            
            $html       =  $this->__('Carrier:') . '<br/>';
            $html       .=  '<select class="ordermanage-carrier" rel="'.$row->getId().'"  name="'.$this->getColumn()->getIndex().'_carrier" style="width:90%">';
            foreach ($this->getCarriers($row->getIncrementId()) as $k => $v)
            {
                $selected = '';
                if ($default == $k)
                {
                    $selected = 'selected="selected"';
                }
                $html .= sprintf('<option value="%s" %s>%s</option>', $k, $selected, $v);
            }
            $html .= '</select><br/>';
/*
            if (Mage::getStoreConfig('ordermanage/ship/comment')) 
            {
                $html .= Mage::helper('sales')->__('Title:') . '<br />';
                $html .= '<input rel="'.$row->getId().'" class="input-text amasty-comment" value="'.Mage::getStoreConfig('amoaction/ship/title').'" /><br />';
            }
*/            
            $html .= $this->__('Tracking Number:') . '<br/>';
            $html .= '<input rel="'.$row->getId().'" class="input-text" name="'.$this->getColumn()->getIndex().'" value=""/>';
        }else 
        {
            
            $field = 'track_number';
            if (version_compare(Mage::getVersion(), '1.5.1.0') <= 0)
            {
                $field = 'number';
            }            
            
            $collection = Mage::getModel('sales/order_shipment_track')
                ->getCollection()
                ->addAttributeToSelect($field)
                ->addAttributeToSelect('title')
                ->setOrderFilter($row->getId());
                
            $numbers    =   array();
            $carriers   = array();
            foreach ($collection as $track) 
            {
                $numbers[]  = $track->getData($field);
                $carriers[] = $track->getTitle();
            }

            if($carriers)
            {
                $html =  $this->__('Carrier:').'<br />';
                $html .= '<strong>' . implode(', ', $carriers) . '</strong><br />';
                
                $html .= $this->__('Tracking Number:').'<br />';
                $html .= '<strong>' .implode(', ', $numbers). '</strong>';
            }
        }

        return $html;
    
    }
    
    
    
    
    

    private function getCarriers($code)
    {
        /*
        $hash           =   array();
        $hash['custom'] =   Mage::helper('sales')->__('Custom Value');
      
        $carrierInstances = Mage::getSingleton('shipping/config')->getActiveCarriers();
        foreach ($carrierInstances as $code => $carrier) 
        {
//            if ($carrier->isTrackingAvailable()) 
            {
                $hash[$code] = $carrier->getConfigData('title');
            }
        }
        
        // add custom carrier as dropdown option
        $title = Mage::getStoreConfig('ordermanage/ship/carrier_title');
        //if ($title && !Mage::getStoreConfig('ordermanage/ship/comment'))
        if ($title && !Mage::getStoreConfig('ordermanage/ship/carrier_title'))
        {
            $hash['custom'] = $title;
        } 

        return $hash;
        */
        $carriers = array();
        $carrierInstances = Mage::getSingleton('shipping/config')->getAllCarriers();
        $carriers['custom'] = Mage::helper('sales')->__('Custom Value');
        foreach ($carrierInstances as $code => $carrier) 
        {
            if ($carrier->isTrackingAvailable()) 
            {
//                $carriers[] = array('value' => $code, 'label' => $carrier->getConfigData('title'));
                $carriers[$code] = $carrier->getConfigData('title');
            }
        }
        return $carriers;
    }
    
    
}
