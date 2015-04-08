<?php

/**
 * @category Webinse
 * @package Webinse_All
 * @author Dmitriy Perederiy <perederiy1993@yandex.ua>
 */
class Webinse_OAuth_Block_OauthData extends Mage_Core_Block_Template
{

    const VK_ICON_CLASS = 'fa fa-vk fa-3x';
    const F_ICON_CLASS = 'fa fa-facebook fa-3x';
    const G_ICON_CLASS = 'fa fa-google-plus fa-3x';
    private  $arraySocialNetwork;


    public function createArray()
    {
        if (Mage::getStoreConfig('OAuth/OAuth_group_vk/vk')) {
            $vk = new Webinse_OAuth_Model_OauthLib_OauthVk(Mage::getUrl('customer/account/loginOauthVk'));
            $this->arraySocialNetwork['vk'] = $vk->getCode();
        }
        if (Mage::getStoreConfig('OAuth/OAuth_group_f/f')) {
            $vk = new Webinse_OAuth_Model_OauthLib_OauthF(Mage::getUrl('customer/account/loginOauthF'));
            $this->arraySocialNetwork['f'] = $vk->getCode();
        }
        if (Mage::getStoreConfig('OAuth/OAuth_group_g/g')) {
            $vk = new Webinse_OAuth_Model_OauthLib_OauthG(Mage::getUrl('customer/account/loginOauthG'));
            $this->arraySocialNetwork['g'] = $vk->getCode();
        }
    }

    public function getIconClass($id){
        switch($id){
            case 'vk': return self::VK_ICON_CLASS; break;
            case 'f': return self::F_ICON_CLASS; break;
            case 'g': return self::G_ICON_CLASS; break;
        }
    }

    public function getArraySocialNetwork(){
        return $this->arraySocialNetwork;
    }

    public function _toHtml()
    {
        $this->createArray();
        $this->setTemplate('Webinse/social.phtml');
        return parent::_toHtml();
    }

}

