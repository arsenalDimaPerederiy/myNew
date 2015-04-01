<?php

/**
 * @category Webinse
 * @package Webinse_All
 * @author Dmitriy Perederiy <perederiy1993@yandex.ua>
 */
class Webinse_OAuth_Block_OauthData extends Mage_Core_Block_Abstract
{

    const VK_ICON_CLASS = 'fa fa-vk fa-3x';
    const F_ICON_CLASS = 'fa fa-facebook fa-3x';
    const G_ICON_CLASS = 'fa fa-google-plus fa-3x';
    public $arraySocialNetwork;

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

    public function getArrayCollection()
    {
        $this->createArray();
        $out = '';
        $i=0;
        if (count($this->arraySocialNetwork) > 0) {
            foreach ($this->arraySocialNetwork as $soc => $val) {
                if($i==0){
                    $out = '<a class='.'"'.'first'.'"'.' href=' . '"' . $val . '"' . '>';
                }
                else{
                    if($i==count($this->arraySocialNetwork)){
                        $out .= '<a class='.'"'.'last'.'"'. 'href=' . '"' . $val . '"' . '>';
                    }
                    else{
                        $out .= '<a href=' . '"' . $val . '"' . '>';
                    }

                }
                switch($soc){
                    case 'vk':$out .='<i class='.'"'.self::VK_ICON_CLASS.'">'.'</i>'; break;
                    case 'f':$out .='<i class='.'"'.self::F_ICON_CLASS.'">'.'</i>'; break;
                    case 'g':$out .='<i class='.'"'.self::G_ICON_CLASS.'">'.'</i>'; break;
                }
                $out.='</a>';
                $i++;
            }
        }
        return $out;
    }
    public function _toHtml()
    {
        $this->setTemplate('Webinse/social.phtml');

        return parent::_toHtml();
    }

}

