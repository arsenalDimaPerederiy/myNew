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

class Magesty_Crumbs_Block_Breadcrumbs extends Mage_Page_Block_Html_Breadcrumbs
{
    protected $_crumbs = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('magesty/crumbs/breadcrumbs.phtml');
    }

    /**
     * Get breadcrumbs for current product
     *
     * @return Magesty_Crumbs_Block_Breadcrumbs|null
     */
    protected function _getBreadcrumbs()
    {
        if (!$this->_crumbs) {
            $helper = Mage::helper('crumbs');
            $pageType = $helper->getPageType();
            $crumbs = array();

            if ($pageType == $helper::PAGE_TYPE_DIRECT_PRODUCT) {

                $regProd = Mage::registry('current_product');
                $product = Mage::getModel('catalog/product')->setStoreId(Mage::app()->getStore()->getId())->load($regProd->getId());
                $crumbs = Mage::getModel('crumbs/breadcrumbs')->getProductBreadcrumbs($product);
                $lastCrumbTitle = Mage::registry('current_product')->getName();

            } elseif ($pageType == $helper::PAGE_TYPE_CATEGORY_PRODUCT) {

                //$crumbs = Mage::getModel('crumbs/breadcrumbs')->getDirectBreadcrumbs();
                //$lastCrumbTitle = Mage::registry('current_product')->getName();
                $regProd = Mage::registry('current_product');
                $product = Mage::getModel('catalog/product')->setStoreId(Mage::app()->getStore()->getId())->load($regProd->getId());
                $crumbs = Mage::getModel('crumbs/breadcrumbs')->getProductBreadcrumbs($product);
                $lastCrumbTitle = Mage::registry('current_product')->getName();

            } elseif ($pageType == $helper::PAGE_TYPE_CATEGORY) {

                $crumbs = Mage::getModel('crumbs/breadcrumbs')->getDirectBreadcrumbs(true);
                $lastCrumbTitle = Mage::registry('current_category')->getName();

            }

            if (Mage::helper('crumbs')->showOnlyOnePath() || $pageType == $helper::PAGE_TYPE_CATEGORY) {
                $crumbs = $this->getLongestPath($crumbs); 
            } elseif (Mage::helper('crumbs')->hideDuplicates()) {
                $crumbs = $this->hideDubCategories($crumbs);
            }

            if (count($crumbs) == 1) {
                $crumbs = $this->addLastCrumb($crumbs, $lastCrumbTitle);
            }

            $this->_crumbs = $crumbs;
        }

        return $this->_crumbs;
    }

    /**
     * Add last unclickable crumb
     *
     * @param array $crumbs
     * @param $lastCrumbsTitle
     * @return array
     */
    public function addLastCrumb(array $crumbs, $lastCrumbsTitle)
    {
        $crumbs[0][] = array(
            'title' => $lastCrumbsTitle,
            'last' => true
        );

        return $crumbs;
    }

    /**
     * Get only one breadcrumbs path of all available paths
     *
     * @param array $crumbs
     * @return array
     */
    public function getLongestPath(array $crumbs)
    {
        $longestPath =false;

        foreach ($crumbs as $k => $path) {
            if (count($path) >= count($longestPath)) {
                $longestPath = $crumbs[$k];
            }
        }

        return array($longestPath);
    }

    /**
     * Mark dublicated categories as hidden
     *
     * @param array $crumbs
     * @return mixed
     */
    public function hideDubCategories(array $crumbs)
    {
        $existCat = array();
        foreach ($crumbs as $pathKey => $path) {
            foreach ($path as $crumbKey => $_crumb) {
                if (in_array($_crumb['category_id'], $existCat)) {
                    $crumbs[$pathKey][$crumbKey]['hidden'] = true;
                } else {
                    $existCat[] = $_crumb['category_id'];
                }
            }
        }
        return $crumbs;
    }

    /**
     * Get all filtered and formatted breadcrumbs
     *
     * @return Magesty_Crumbs_Block_Breadcrumbs|null
     */
    public function getAllBreadcrumbs()
    {
        $crumbs = $this->_getBreadcrumbs();
        return $crumbs;
    }
}