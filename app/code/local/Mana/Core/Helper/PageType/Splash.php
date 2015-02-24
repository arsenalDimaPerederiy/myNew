<?php
/** 
 * @category    Mana
 * @package     Mana_Core
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Core_Helper_PageType_Splash extends Mana_Core_Helper_PageType  {
    public function getCurrentSuffix() {
        $categoryHelper = Mage::helper('catalog/category');

        return $categoryHelper->getCategoryUrlSuffix();
    }

    public function getRoutePath() {
        return 'splash/page/view';
    }
    /**
     * @return bool|string
     */
    public function getConditionLabel() {
        return $this->__('Splash Page');
    }

    public function getPageContent() {
        $result = array();
        return "1111++++++++++";
    }

    public function getPageTypeId() {
        if ($splash = Mage::registry('splash_page')) {
            return 'splash:' . $splash->getPageId();
        }
        else {
            return '';
        }
    }	
}