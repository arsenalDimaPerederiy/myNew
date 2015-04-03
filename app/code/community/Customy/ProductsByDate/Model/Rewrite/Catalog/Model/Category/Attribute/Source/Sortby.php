<?php
/**
 * Customy
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Customy EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.customy.com/LICENSE-1.0.html
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@customy.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.customy.com/ for more information
 * or send an email to sales@customy.com
 *
 * @copyright  Copyright (c) 2011 Triple Dev Studio (http://www.customy.com/)
 * @license    http://www.customy.com/LICENSE-1.0.html
 */
 class Customy_ProductsByDate_Model_Rewrite_Catalog_Model_Category_Attribute_Source_Sortby extends Mage_Catalog_Model_Category_Attribute_Source_Sortby
 
{

    /**
     * Retrieve All options
     *
     * @return array
     */
    public function getAllOptions()
    {
        if (!Mage::getStoreConfig("productsbydate/options/active")){
            return parent::getAllOptions();
        }
        
        if (is_null($this->_options)) {
            $this->_options = array(array(
                'label' => Mage::helper('productsbydate')->__('Position'),
                'value' => 'position'
            )
            
            );
            foreach ($this->_getCatalogConfig()->getAttributesUsedForSortBy() as $attribute) {
                $this->_options[] = array(
                    'label' => Mage::helper('catalog')->__($attribute['frontend_label']),
                    'value' => $attribute['attribute_code']
                );
            }
        }
        return $this->_options;
    }
}
