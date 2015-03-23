<?php
/**
 * @category Webinse
 * @package Webinse_OAuth
 * @author Dmitriy Perederiy <perederiy1993@yandex.ua>
 */

$var_array=array(
    array(
        'social_name'=>'vk'
    ),
    array(
        'social_name'=>'facebook'
    ),
    array(
        'social_name'=>'gp'
    ),
);

foreach($var_array as $var){
    Mage::getModel('webinse_oauth/Oauth')
        ->setData($var)
        ->save();
}