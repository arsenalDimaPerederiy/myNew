<?php
/**
 * @category Webinse
 * @package Webinse_All
 * @author Dmitriy Perederiy <perederiy1993@yandex.ua>
 */

class Webinse_OAuth_Model_OauthLib_Oauth_Oauth{

    const GET_TOKEN_ERROR = 'Error taking token';
    const GET_USER_DATA_ERROR='Error taking user data';


    public $websiteId;
    public $store;
    public $customer_id;

    protected $code;
    protected $userId;
    protected $token;
    public  $email;
    public  $userInfoArray;

    public $SocialNetworkModel;
    public $class_id;

    public function __construct(){
        $this->SocialNetworkModel=Mage::getModel('webinse_oauth/Oauth');
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
        try{
            $customer = Mage::getModel("customer/customer");
            $customer   ->setWebsiteId($this->websiteId)
                ->setStore($this->store)
                ->setFirstname($this->userInfoArray['first_name'])
                ->setLastname($this->userInfoArray['last_name'])
                ->setEmail($this->email)
                ->setPassword($this->GeneratePassword());
            $customer->save();
            if($customer->getId()){
                $this->customer_id=$customer->getId();
            }
            else{
                throw new Exception('new customer not create');
            }
        }
        catch(Exception $e){
            Mage::logException($e);
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());

            return false;
        }
        return true;
    }
    public function createUserEmail(){
        $this->email=$this->userInfoArray['first_name'].$this->userInfoArray['last_name'].md5('gsdfg'.$this->userId).'@test.loc';
    }

    public function changeEmailCustomer(){
        try{
            $socialRecord = $this->GetUserBySocialIdSocId();
            $CustomerId=$socialRecord->getCustomerId();
            $customer = Mage::getModel("customer/customer")->load($CustomerId);
            $customer->setEmail($this->email);
            $customer->save();
            if(!$customer->getId()){
                throw new Exception('new customer not create');
            }
        }
        catch(Exception $e){
            Mage::logException($e);
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());

            return false;
        }
        return true;
    }
    public function setCode($code){
        $this->code=$code;
    }

    public function setSocialNewRecord(){
        $socialData = array(
            'social'=>$this->class_id,
            'user_soc_id'=>$this->userId,
            'customer_id'=>$this->customer_id,
            'store_id'=>$this->store,
            'website_id'=>$this->websiteId
        );
        if($this->SocialNetworkModel->setSocialNetworkNewRecord($socialData)){
            return true;
        }
        else{
            return false;
        }

    }

    public function getCustomerEmail($model){
        $customer = $model->getFirstItem()->getCustomerId();
        $StoreId = $model->getFirstItem()->getStoreId();
        $websiteId = $model->getFirstItem()->getWebsiteId();

        $customerModel = Mage::getModel('customer/customer')
            ->setWebsiteId($websiteId)
            ->setStoreId($StoreId);
        $customerModel->load($customer);
        if($customerModel->getId()){
            $this->email=$customerModel->getEmail();
            return true;
        }
        else{
            return false;
        }
    }

    public function GetUserBySocialId(){
        return $this->SocialNetworkModel->getRecordsByUserId($this->userId);
    }

    public function GetUserBySocialIdSocId(){
        return $this->SocialNetworkModel->GetUserBySocialIdSocId($this->userId,$this->class_id);
    }
    public function setNewCustomerId($id){
        $this->SocialNetworkModel->setNewCustomerId($id,$this->customer_id);
    }
    public function deleteRecords(){
        $this->SocialNetworkModel->deleteRecords();
    }
}