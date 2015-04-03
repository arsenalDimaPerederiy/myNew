<?php
/**
 * author: Dragan
 */

class Mementia_Ext_Model_Order_Observer
{

    /**
     * Creates new user if not exist or adds order to existing customer
     * @param $observer
     */

    public function save($observer)
    {
        $order = $observer->getOrder();

        if (!$order) {
            return $this;
        }
        if ($order->getState() == Mage_Sales_Model_Order::STATE_NEW) {

            $customer = $this->_createCustomer($order);
            if ($customer->getId()) {
                $order->setCustomer($customer);
                $order->setCustomerGroupId(Mage::getStoreConfig(Mage_Customer_Model_Group::XML_PATH_DEFAULT_ID));
                return $this;
            }
        }
    }

    /**
     * Generete new customer or return existing
     * @param $order
     * @return $customer
     */

    protected function _createCustomer($order)
    {
        $customer = Mage::getModel('customer/customer');
        $password = rand(100000, 100000000);
        $email = $order->getCustomerEmail();
        $customer->setWebsiteId(Mage::app()->getWebsite()->getId());
        $customer->loadByEmail($email);
        //Build billing and shipping address for customer, for checkout
        $address = $order->getBillingAddress();

        if (!$customer->getId()) {
            $customer->setEmail($email);
            $customer->setFirstname($order->getCustomerFirstname());
            $customer->setLastname($order->getCustomerLastname());
            $customer->setPassword($password);
            try {
                $customer->save();
                $customer->setConfirmation(null);
                $customer->save();
            } catch (Exception $e) {
                Mage::logException($e);
            }


        }
        else {
            $customer->setFirstname($address->getFirstname());
            $customer->setLastname($address->getLastname());
            try {
                $customer->save();
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
        $customerBillingAddressId = $customer->getDefaultBilling();
        if ($customerBillingAddressId) {
            $customAddress = Mage::getModel('customer/address')->load($customerBillingAddressId);
        }
        else {
            $customAddress = Mage::getModel('customer/address');
            $customAddress
                ->setCustomerId($customer->getId())
                ->setIsDefaultBilling('1')
                ->setIsDefaultShipping('1')
                ->setSaveInAddressBook('1');
        }



        $customAddress->setFirstname($address->getFirstname());
        $customAddress->setLastname($address->getLastname());
        $customAddress->setStreet($address->getStreet());
        $customAddress->setCity($address->getCity());
        $customAddress->setRegionId($address->getRegionId());
        $customAddress->setRegion($address->getRegion());
        $customAddress->setPostcode($address->getPostcode());
        $customAddress->setCountryId($address->getCountryId());
        $customAddress->setTelephone($address->getTelephone());


        try {
            $customAddress->save();
        } catch (Exception $e) {
            Mage::logException($e);
        }

        return $customer;
    }
}