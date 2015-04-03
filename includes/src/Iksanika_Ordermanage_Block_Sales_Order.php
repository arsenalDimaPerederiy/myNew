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

class Iksanika_Ordermanage_Block_Sales_Order 
    extends Mage_Adminhtml_Block_Sales_Order
{
    
    public function __construct()
    {
        parent::__construct();
        $this->_headerText = Mage::helper('ordermanage')->__('Orders Manager (Advanced)');
    }
    
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
/*        $this->_addButton('add_new', array(
            'label'   => Mage::helper('catalog')->__('Add Product'),
            'onclick' => "setLocation('{$this->getUrl('adminhtml/catalog_product/new')}')",
            'class'   => 'add'
        ));*/
//        $this->setTemplate('iksanika/ordermanage/sales/order.phtml');
        $this->setChild('grid', $this->getLayout()->createBlock('ordermanage/sales_order_grid', 'order.ordermanage'));
//        $this->setChild('store_switcher', $this->getLayout()->createBlock('adminhtml/store_switcher'));
    }
/*    
    public function getStoreSwitcherHtml()
    {
        if(!$this->isSingleStoreMode())
        {
            return $this->getChildHtml('store_switcher');
        }
    }
 */
}