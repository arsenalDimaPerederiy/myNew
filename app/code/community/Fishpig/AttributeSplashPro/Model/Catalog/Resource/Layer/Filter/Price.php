<?php
/**
 * Catalog Price Slider
 *
 * @category   Magehouse
 * @class    Magehouse_Slider_Block_Catalog_Layer_Filter_Price
 * @author     Mrugesh Mistry <core@magentocommerce.com>
 */
class Fishpig_AttributeSplashPro_Model_Catalog_Resource_Layer_Filter_Price extends Mage_Catalog_Model_Resource_Layer_Filter_Price
{


    public function applyFilterToCollection($filter, $range, $index)
    {
        $collection = $filter->getLayer()->getProductCollection();
        $collection->addPriceData($filter->getCustomerGroupId(), $filter->getWebsiteId());

        $select     = $collection->getSelect();
        $response   = $this->_dispatchPreparePriceEvent($filter, $select);

        $table      = $this->_getIndexTableAlias();
        $additional = join('', $response->getAdditionalCalculations());
        $rate       = $filter->getCurrencyRate();
        $priceExpr  = new Zend_Db_Expr("(({$table}.min_price {$additional}) * {$rate})");

        //$select
            //->where($priceExpr . ' >= ?', ($range * ($index - 1)))
            //->where($priceExpr . ' < ?', ($range * $index));
 
            $select
                ->where($priceExpr . ' >= ?', (int)$index)
                ->where($priceExpr . ' <= ?', (int)$range); //apply custom range                       

        return $this;
    }
}
