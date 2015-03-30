<?php
/**
 * @category Webinse
 * @package Webinse_All
 * @author Dmitriy Perederiy <perederiy1993@yandex.ua>
 */

class Webinse_OAuth_Model_OauthLib_Oauth_Oauth{

    public $websiteId;
    public $store;
    public $customer_id;

    protected $code;
    protected $userId;
    protected $token;
    public  $email;
    public  $userInfoArray;

    public function __construct(){
        $this->websiteId = Mage::app()->getWebsite()->getId();
        $this->store = Mage::app()->getStore();
    }
    public function GeneratePassword(){
        return Mage::helper('core')->getRandomString($length = 7);
    }

    public function CheckCustomer(){
        $customer = Mage::getModel("customer/customer");
        $customer->setWebsiteId(Mage::app()->getWebsite()->getId());
        $customer->loadByEmail($this->email);
        $this->customer_id = $customer->getId();
        return $this->customer_id;
    }
    public function setNewCustomer(){
        $customer = Mage::getModel("customer/customer");
        $customer   ->setWebsiteId($this->websiteId)
            ->setStore($this->store)
            ->setFirstname($this->userInfoArray['first_name'])
            ->setLastname($this->userInfoArray['last_name'])
            ->setEmail($this->email)
            ->setPassword($this->GeneratePassword());

        $customer->save();

        $this->customer_id=$customer->getId();

    }
    public function createUserEmail(){
        $this->email=$this->userInfoArray['first_name'].$this->userInfoArray['last_name'].'@test.loc';
    }

    public function changeEmailCustomer(){
        $socialRecord = $this->GetUserBySocialIdSocId();
        $CustomerId=$socialRecord->getCustomerId();
        $customer = Mage::getModel("customer/customer")->load($CustomerId);
        $customer->setEmail($this->email);
        $customer->save();
    }
}