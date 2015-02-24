<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_GoogleAnalytics
 * @copyright  Copyright (c) 2006-2014 X.commerce, Inc. (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * GoogleAnalitics Page Block
 *
 * @category   Webinse
 * @package    Webinse_All
 * @author     Dmitriy Perederiy <perederiy1993@yandex.ua>
 */
class Webinse_All_Block_Ga extends Mage_GoogleAnalytics_Block_Ga
{

    protected function _getOrdersTrackingCodeAnalytics()
    {
        $orderIds = $this->getOrderIds();
        if (empty($orderIds) || !is_array($orderIds)) {
            return;
        }
        $collection = Mage::getResourceModel('sales/order_collection')
            ->addFieldToFilter('entity_id', array('in' => $orderIds));
        $result = array();
        foreach ($collection as $order) {
            if ($order->getIsVirtual()) {
                $address = $order->getBillingAddress();
            } else {
                $address = $order->getShippingAddress();
            }
            $result[] = sprintf("_gaq.push(['_addTrans', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s']);",
                $order->getIncrementId(),
                $this->jsQuoteEscape(Mage::app()->getStore()->getFrontendName()),
                $order->getBaseSubtotal(),
                $order->getBaseTaxAmount(),
                $order->getBaseShippingAmount(),
                $this->jsQuoteEscape(Mage::helper('core')->escapeHtml($address->getCity())),
                $this->jsQuoteEscape(Mage::helper('core')->escapeHtml($address->getRegion())),
                $this->jsQuoteEscape(Mage::helper('core')->escapeHtml($address->getCountry()))
            );
            foreach ($order->getAllVisibleItems() as $item) {
                $result[] = sprintf("_gaq.push(['_addItem', '%s', '%s', '%s', '%s', '%s', '%s']);",
                    $order->getIncrementId(),
                    $this->jsQuoteEscape($item->getSku()), $this->jsQuoteEscape($item->getName()),
                    null, // there is no "category" defined for the order item
                    $item->getBasePrice(), $item->getQtyOrdered()
                );
            }
            $result[] = "_gaq.push(['_trackTrans']);";
        }

        return implode("\n", $result);
    }

    protected function _getOrdersTrackingCodeUniversal()
    {
        $orderIds = $this->getOrderIds();
        if (empty($orderIds) || !is_array($orderIds)) {
            return;
        }
        $collection = Mage::getResourceModel('sales/order_collection')
            ->addFieldToFilter('entity_id', array('in' => $orderIds));
        $result = array();
        $result[] = "ga('require', 'ecommerce')";
        foreach ($collection as $order) {
            $result[] = sprintf("ga('ecommerce:addTransaction', {
                'id': '%s',
                'affiliation': '%s',
                'revenue': '%s',
                'tax': '%s',
                'shipping': '%s'
                });",
                $order->getIncrementId(),
                $this->jsQuoteEscape(Mage::app()->getStore()->getFrontendName()),
                $order->getBaseSubtotal(),
                $order->getBaseTaxAmount(),
                $order->getBaseShippingAmount()
            );
            foreach ($order->getAllVisibleItems() as $item) {
                $result[] = sprintf("ga('ecommerce:addItem', {
                    'id': '%s',
                    'sku': '%s',
                    'name': '%s',
                    'category': '%s',
                    'price': '%s',
                    'quantity': '%s'
                    });",
                    $order->getIncrementId(),
                    $this->jsQuoteEscape($item->getSku()),
                    $this->jsQuoteEscape($item->getName()),
                    null, // there is no "category" defined for the order item
                    $item->getBasePrice(),
                    $item->getQtyOrdered()
                );
            }
            $result[] = "ga('ecommerce:send');";
        }
        return implode("\n", $result);
    }

}
