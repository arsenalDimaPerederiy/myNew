<?php
/** 
 * @category    Mana
 * @package     Mana_Seo
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Seo_Helper_PageType_Splash extends Mana_Seo_Helper_PageType  {
    public function getSuffixHistoryType() {
        return Mana_Seo_Model_UrlHistory::TYPE_SPLASH_SUFFIX;
    }

    protected $_urlKey;
	
   public function setPage($token) {
        parent::setPage($token);
        $token
            ->addParameter('id', $token->getPageUrl()->getSplashId());
        return true;
    }

    /**
     * @param Mana_Seo_Rewrite_Url $urlModel
     * @return string | bool
     */
    public function getUrlKey($urlModel) {
        /* @var $seo Mana_Seo_Helper_Data */
        $seo = Mage::helper('mana_seo');

        /* @var $logger Mana_Core_Helper_Logger */
        $logger = Mage::helper('mana_core/logger');

        if (($splashPageId = $urlModel->getSeoRouteParam('id')) === false) {
            $logger->logSeoUrl(sprintf('WARNING: while resolving %s, %s route parameter is required', 'Splash page URL key', 'id'));
        }
        if (!isset($this->_urlKeys[$splashPageId])) {
            $urlCollection = $seo->getUrlCollection($urlModel->getSchema(), Mana_Seo_Resource_Url_Collection::TYPE_PAGE);
            $urlCollection->addFieldToFilter('splash_id', $splashPageId);
            if (!($result = $urlModel->getUrlKey($urlCollection))) {
                $logger->logSeoUrl(sprintf('WARNING: %s not found by  %s %s', 'Splash page URL key', 'id', $splashPageId));
            }
            $this->_urlKeys[$splashPageId] = $result;
        }

        return $this->_urlKeys[$splashPageId]['final_url_key'];
    }
}