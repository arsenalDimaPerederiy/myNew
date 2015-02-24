<?php

class Mementia_OrderView_Block_Form extends Mage_Core_Block_Template
{
    public function getRequestOrderId()
    {
        return $this->getRequest()->getParam('id', '');
    }

    public function getRequestEmail()
    {
        return $this->getRequest()->getParam('email', '');
    }

    public function getActionUrl()
    {
        return Mage::getUrl('orderview/status/process', array('_secure' => true));
    }
}