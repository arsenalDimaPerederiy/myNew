<?php
/**
 * Magesty
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magestyapps.com for more information or
 * send an email to support@magestyapps.com .
 *
 * @category    Magesty
 * @package     Magesty_Crumbs
 * @copyright   Copyright (c) 2013 Magesty (http://www.magestyapps.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Magesty_Crumbs_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_ENABLED = 'crumbs/general/enabled';
    const XML_PATH_ONLY_ONE_PATH = 'crumbs/general/one_path';
    const XML_PATH_HIDE_DUPLICATES = 'crumbs/general/hide_duplicates';
    const XML_PATH_DEFAULT_CATEGORY = 'crumbs/general/default_category';

    const PAGE_TYPE_DIRECT_PRODUCT = 'direct_product';
    const PAGE_TYPE_CATEGORY_PRODUCT = 'category_product';
    const PAGE_TYPE_CATEGORY = 'category';

    /**
     * Get configuration "Enabled"
     *
     * @return bool
     */
    public function isEnabled()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_ENABLED);
    }

    /**
     * Get configuration "Show only one path"
     *
     * @return bool
     */
    public function showOnlyOnePath()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_ONLY_ONE_PATH);
    }

    /**
     * Get configuration "Hide Duplicated Categories"
     *
     * @return bool
     */
    public function hideDuplicates()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_HIDE_DUPLICATES);
    }

    /**
     * Get configuration "Default category"
     *
     * @return string
     */
    public function getDefaultCategory()
    {
        return Mage::getStoreConfig(self::XML_PATH_DEFAULT_CATEGORY);
    }

    /**
     * get type of current page
     *
     * @return bool|string
     */
    public function getPageType()
    {
        $type = false;
        $refererUrl = Mage::app()->getRequest()->getServer('HTTP_REFERER');

        if (Mage::registry('current_category') && Mage::registry('current_product')) {
            $type = self::PAGE_TYPE_CATEGORY_PRODUCT;
        } elseif (!Mage::registry('current_category') && Mage::registry('current_product')) {
            $type =  self::PAGE_TYPE_DIRECT_PRODUCT;
        } elseif (Mage::registry('current_category') && !Mage::registry('current_product')) {
            $type = self::PAGE_TYPE_CATEGORY;
        }

        return $type;
    }

}