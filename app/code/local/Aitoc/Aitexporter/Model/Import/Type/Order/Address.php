<?php
/**
 * Orders Export and Import
 *
 * @category:    Aitoc
 * @package:     Aitoc_Aitexporter
 * @version      1.2.8
 * @license:     ou1zlIlUK4jGhUJZLohhJ5b8jdvumX7FXHqMPgZHkF
 * @copyright:   Copyright (c) 2014 AITOC, Inc. (http://www.aitoc.com)
 */
class Aitoc_Aitexporter_Model_Import_Type_Order_Address implements Aitoc_Aitexporter_Model_Import_Type_Interface
{
    /**
     * 
     * @param SimpleXMLElement $orderXml
     * @param string $itemXpath
     * @param array $config
     * @param string $orderIncrementId
     * @return boolean is valid 
     */
    public function validate(SimpleXMLElement $orderXml, $itemXpath, array $config, $orderIncrementId = '')
    {
        $isValid    = true;
        $addressXml = current($orderXml->xpath($itemXpath));

        // empty items from CSV 
        if (!trim($addressXml->asXML()))
        {
            return $isValid;
        }

        if (!isset($addressXml->address_type))
        {
            Mage::getModel('aitexporter/import_error')
                ->setOrderIncrementId($orderIncrementId)
                ->setError(Mage::helper('aitexporter')->__('address_type is not set for %s item', $itemXpath))
                ->save();
        }
        elseif (!in_array($addressXml->address_type, array('billing', 'shipping')))
        {
            Mage::getModel('aitexporter/import_error')
                ->setOrderIncrementId($orderIncrementId)
                ->setError(Mage::helper('aitexporter')->__('Unknown address_type for %s item', $itemXpath))
                ->save();
        }

        return $isValid;
    }

    /**
     * @param SimpleXMLElement $orderXml
     * @param string $itemXpath
     * @param array $importConfig
     * @param Mage_Sales_Model_Order $parentItem
     */
    public function import(SimpleXMLElement $orderXml, $itemXpath, array $config, Mage_Core_Model_Abstract $parentItem = null)
    {
        /* @var $parentItem Mage_Sales_Model_Order */
        $address    = Mage::getModel('sales/order_address');
        /* @var $address Mage_Sales_Model_Order_Address */
        $addressXml = current($orderXml->xpath($itemXpath));
        /* @var $addressXml SimpleXMLElement */

        // empty items from CSV 
        if (!trim($addressXml->asXML()))
        {
            return false;
        }

        // Do not import address without corresponding types
        if (!isset($addressXml->address_type) || !in_array($addressXml->address_type, array('billing', 'shipping')))
        {
            return false;
        }

        // Create customer by email if needed
        if (isset($addressXml->address_type) && 'billing' == $addressXml->address_type && !empty($orderXml->fields->customer_id))
        {
            $addressEmail = isset($addressXml->email) ? (string)$addressXml->email : '';

            if ($addressEmail)
            {
                $websiteId = $parentItem->getStore()->getWebsiteId();
                $storeId = $parentItem->getStore()->getId();
                $customer  = Mage::getModel('customer/customer')->setWebsiteId($websiteId)->loadByEmail($addressEmail);
                /* @var $customer Mage_Customer_Model_Customer */

                if (!$customer->getId() && isset($config['create_customers']) && 'billing' == $config['create_customers'])
                {
                    $customer
                        ->setEmail($addressEmail)
                        ->setStoreId($storeId)
                        ->setWebsiteId($websiteId)
                        ->setFirstname(isset($addressXml->firstname) ? (string)$addressXml->firstname : '')
                        ->setLastname(isset($addressXml->lastname) ? (string)$addressXml->lastname : '')
                        ->save();
                }

                $parentItem->setCustomerId($customer->getId()); 
            }
        }

        foreach ($addressXml->children() as $field)
        {
            switch ($field->getName())
            {
                case 'parent_id':
                case 'quote_address_id':
                case 'customer_address_id':
                    break;

                case 'entity_id':
                    $address->setData('old_'.$field->getName(), (string)$field);
                    break;

                case 'customer_id':
                    $address->setCustomerId($parentItem->getCustomerId());
                    break;

                default:
                    $address->setData($field->getName(), (string)$field);
                    break;
            }
        }

        $parentItem->addAddress($address);
    }

    public function getXpath()
    {
        return '/addresses/address';
    }

    /**
     * @see Aitoc_Aitexporter_Model_Import_Type_Interface::getErrorType()
     * return string
     */
    public function getErrorType()
    {
        return Aitoc_Aitexporter_Model_Import_Error::TYPE_WARNING;
    }

    public function postProcess(Mage_Sales_Model_Order $order)
    {
        foreach ($order->getAddressesCollection() as $address)
        {
            $address->setCustomerId($order->getCustomerId());
        }

        return $this;
    }
}