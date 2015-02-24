<?php
/**
 * Catalog Price Slider
 *
 * @category   Magehouse
 * @class    Magehouse_Slider_Block_Catalog_Layer_Filter_Price
 * @author     Mrugesh Mistry <core@magentocommerce.com>
 */
class Fishpig_AttributeSplashPro_Model_Catalog_Layer_Filter_Price extends Mage_Catalog_Model_Layer_Filter_Price
{


    protected function _renderItemLabel($range, $value)
    {
        $store      = Mage::app()->getStore();
        //$fromPrice  = $store->formatPrice(($value-1)*$range);
        //$toPrice    = $store->formatPrice($value*$range);
        $fromPrice  = $store->formatPrice($value);
        $toPrice    = $store->formatPrice($range);
        return Mage::helper('catalog')->__('%s - %s', $fromPrice, $toPrice);
    }

}
