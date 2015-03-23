<?php
/**
 * @category Webinse
 * @package Webinse_All
 * @author Dmitriy Perederiy <perederiy1993@yandex.ua>
 */
require_once 'Mage/Customer/controllers/AccountController.php';


class Webinse_OAuth_AccountController extends Mage_Core_Controller_Front_Action {


    public function VkLoginAction(){
        $vk_code=Mage::app()->getRequest()->getParam('code');
        if(isset($vk_code)){
            $data = $this->getLayout()->getBlockSingleton('webinse_oauth/OauthData')->getVkData();
            $client = new Varien_Http_Client('https://api.vk.com/oauth/access_token/');
            $client ->setMethod(Varien_Http_Client::GET);
                    $client ->setParameterGet('client_id',$data['client_id']);
                    $client->setParameterGet('client_secret',$data['client_secret']);
                    $client ->setParameterGet('code',$vk_code);

            $response = Mage::helper('core')->jsonDecode($client->request()->getBody());

            print_r($response);

           /* if(isset($response['access_token'])&&isset($response['user_id'])){
                $client = new Varien_Http_Client(' https://api.vk.com/method/users.get/');
                $client ->setMethod(Varien_Http_Client::GET);
                $client ->setParameterGet('uids',$response['user_id']);
                $client->setParameterGet('fields','email,name,first');
                $client ->setParameterGet('access_token',$response['access_token']);
                $user = Mage::helper('core')->jsonDecode($client->request()->getBody());

                $customer = Mage::getModel("customer/customer");
                $customer->setWebsiteId(Mage::app()->getWebsite()->getId());*/

               /* if($customer->loadByEmail($user['email'])){

                }*/

        }

    }

    public function loginAction(){

        $block = $this->loadLayout()->getLayout()->getBlockSingleton('customer/form_login')->setTemplate('WebinseOauth/login.phtml');
        $this->getResponse()->setBody($block->toHtml());
    }


}