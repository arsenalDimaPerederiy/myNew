<?php
/**
 * @category Webinse
 * @package Webinse_All
 * @author Dmitriy Perederiy <perederiy1993@yandex.ua>
 */
class Webinse_OAuth_Block_OauthData extends Mage_Core_Block_Abstract{

    public function getVkData(){
        if(Mage::getStoreConfig('OAuth/OAuth_group_vk/vk')==true){
            $params = array(
                'client_id'=>Mage::getStoreConfig('OAuth/OAuth_group_vk/vk_id_key'),
                'client_secret'=>Mage::getStoreConfig('OAuth/OAuth_group_vk/vk_secret')
            );
            return $params;
        }
    }
}