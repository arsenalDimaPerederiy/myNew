<?php
/**
 * @category Webinse
 * @package Webinse_All
 * @author Dmitriy Perederiy <perederiy1993@yandex.ua>
 */ 
class Webinse_OAuth_Helper_Customer_Data extends Mage_Customer_Helper_Data {

    const ROUTE_ACCOUNT_LOGIN_AJAX = 'customer/account/loginOauth';

public function getLoginType(){
   if(Mage::getStoreConfig('OAuth/Ajax_login_setup/loginAjax')){
       return '<input type='.'"'.'hidden'.'" id='.'"'.'ajaxLogin'.'" value='.'"'.'ajax'.'"'.'>';
   }
    else{
        return '<input type='.'"'.'hidden'.'" id='.'"'.'ajaxLogin'.'" value='.'"'.'login'.'"'.'>';
    }
}

}