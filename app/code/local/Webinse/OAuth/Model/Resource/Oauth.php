<?php
/**
 * @category Webinse
 * @package Webinse_All
 * @author Dmitriy Perederiy <perederiy1993@yandex.ua>
 */
class Webinse_OAuth_Model_Resource_Oauth extends Mage_Core_Model_Resource_Db_Abstract{

    public function _construct()
    {
        $this->_init('webinse_oauth/webinse_oauth', 'entity_id');
    }
}