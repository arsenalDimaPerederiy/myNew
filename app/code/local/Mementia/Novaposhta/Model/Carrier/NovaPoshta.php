<?php

class Mementia_Novaposhta_Model_Carrier_NovaPoshta
    extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{

    protected $_code = 'novaposhta';

    /**
     * @param Mage_Shipping_Model_Rate_Request $request
     * @internal param \Mage_Shipping_Model_Rate_Request $data
     * @return Mage_Shipping_Model_Rate_Result
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        /** @var $result Mage_Shipping_Model_Rate_Result */
        $result = Mage::getModel('shipping/rate_result');
        $packageValue = $request->getPackageValue();

        $shippingPrice = 0;

        $paymentMethod = Mage::getSingleton('checkout/session')->getQuote()->getPayment()->getMethod();

        $warehouseId = Mage::app()->getRequest()->getPost('warehouse_id', ""); //  warehouse ID
        $warehouse = Mage::getModel('novaposhta/warehouse')->load($warehouseId);
        $warehouseName = $warehouse->getAddressRu(); // warehouse name

        if (($freeShippingSubtotal = intval($this->getConfigData('free_shipping_subtotal')))
            && ($packageValue >= $freeShippingSubtotal)
        ) {
            $shippingPrice = 0;
        } elseif ($methodPrice = $this->_getDeliveryPriceByWeight($request->getPackageWeight(), $paymentMethod)) {
            $shippingPrice = $methodPrice;
        }

        /** @var $method Mage_Shipping_Model_Rate_Result_Method */
        $method = Mage::getModel('shipping/rate_result_method');
        $method->setCarrier($this->_code)
            ->setCarrierTitle($this->getConfigData('title'))
            ->setMethod('warehouse_'
//            . $warehouseId
            )
            ->setMethodTitle($warehouseName)
            ->setPrice($shippingPrice)
            ->setCost($shippingPrice);

        $result->append($method);

        return $result;
    }

    public function getAllowedMethods()
    {
        return array($this->_code => $this->getConfigData('name'));
    }

    /**
     * Check if carrier has shipping tracking option available
     *
     * @return boolean
     */
    public function isTrackingAvailable()
    {
        return true;
    }

    /**
     * @param $paymentMethod
     *
     * @return array
     */
    protected function _getWeightPriceMap($paymentMethod)
    {
        $weightPriceMap = $this->getConfigData($paymentMethod . '_price');
        if (empty($weightPriceMap)) {
            return array();
        }

        return unserialize($weightPriceMap);
    }

    /**
     * @param $packageWeight
     * @param $paymentMethod
     *
     * @return float
     */
    protected function _getDeliveryPriceByWeight($packageWeight, $paymentMethod)
    {
        $weightPriceMap = $this->_getWeightPriceMap($paymentMethod);

        if (empty($weightPriceMap)) {
            return $resultingPrice;
        }

        $resultingPrice = 0.00;
        $minimumWeight = 1000000000;
        foreach ($weightPriceMap as $weightPrice) {
            if ($packageWeight <= $weightPrice['weight'] && $weightPrice['weight'] <= $minimumWeight) {
                $minimumWeight = $weightPrice['weight'];
                $resultingPrice = $weightPrice['price'];
            }
        }

        return $resultingPrice;
    }
}

