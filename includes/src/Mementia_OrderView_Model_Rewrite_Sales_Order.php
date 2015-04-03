<?php


class Mementia_OrderView_Model_Rewrite_Sales_Order extends Mage_Sales_Model_Order {
    /*
     * Method to get direct order view url for frontend
     *
     */
    public function getDirectViewUrl()
    {
        $email = '';

        if ($this->getCustomerEmail())
        {
            $email = $this->getCustomerEmail();
        }
        else if($this->getShippingAddress() && $this->getShippingAddress()->getEmail())
        {
            $email = $this->getShippingAddress()->getEmail();
        }
        else if ($this->getBillingAddress() && $this->getBillingAddress()->getEmail())
        {
            $email = $this->getBillingAddress()->getEmail();
        }

        return Mage::getUrl('orderview/status/form', array('_secure' => true, 'id' => $this->getIncrementId(), 'email' => rawurlencode($email)));
    }
}