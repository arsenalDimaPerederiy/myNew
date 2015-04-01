<?php
/**
 * @category Webinse
 * @package Webinse_All
 * @author Dmitriy Perederiy <perederiy1993@yandex.ua>
 */
class Webinse_OAuth_Model_OauthLib_OauthVk extends Webinse_OAuth_Model_OauthLib_Oauth_Oauth{

    const OAUTH_VK_URI_AUTHORIZATION = 'http://oauth.vk.com/authorize';
    const OAUTH_VK_URI_GET_TOKEN ='https://api.vk.com/oauth/access_token/';
    const OAUTH_VK_URI_GET_USER_INFO ='https://api.vk.com/method/users.get';


    protected $clientId;
    protected $client_secret;
    protected $redirect_uri;

    public  $SocialNetworkModel;
    public $class_id='vk';

    public function __construct($redirectUrl){

        $this->redirect_uri=$redirectUrl;
        $this->clientId=Mage::getStoreConfig('OAuth/OAuth_group_vk/vk_id_key');
        $this->client_secret=Mage::getStoreConfig('OAuth/OAuth_group_vk/vk_secret');
        $this->SocialNetworkModel=Mage::getModel('webinse_oauth/Oauth');
        parent::__construct();
    }

    public function getCode(){
        return self::OAUTH_VK_URI_AUTHORIZATION.'?'.'client_id='.$this->clientId.'&'.'redirect_uri='.$this->redirect_uri.'&'.'response_type=code'.'&'.'scope=email,offline';
    }

    public function getToken(){

        $client = new Varien_Http_Client(self::OAUTH_VK_URI_GET_TOKEN);
        $client->setMethod(Varien_Http_Client::GET);
        $client->setParameterGet('client_id',$this->clientId);
        $client->setParameterGet('client_secret',$this->client_secret);
        $client->setParameterGet('code',$this->code);
        $client->setParameterGet('redirect_uri',$this->redirect_uri);

        $userTokenArray=Mage::helper('core')->jsonDecode($client->request()->getBody());

        if(isset($userTokenArray['access_token'])){

            $this->token=$userTokenArray['access_token'];
            $this->userId=$userTokenArray['user_id'];

            if(isset($userTokenArray['email'])){
                $this->email=$userTokenArray['email'];
            }
            else{
                $this->email=null;
            }
            return true;
        }
        else{
            return false;
        }


    }


    public function getUserInfo(){

        $client_1 = new Varien_Http_Client(self::OAUTH_VK_URI_GET_USER_INFO);
        $client_1->setMethod(Varien_Http_Client::GET);
        $client_1->setParameterGet('uids',$this->userId);
        $client_1->setParameterGet('access_token',$this->token);
        $client_1->setParameterGet('fields','first_name');

        if($client_1->request()->isSuccessful()){
            $this->userInfoArray=Mage::helper('core')->jsonDecode($client_1->request()->getBody());
                if(isset($this->userInfoArray['response'])){
                    $this->userInfoArray=$this->userInfoArray['response']['0'];
                    return true;
                }
                else{
                    return false;
                }
        }
        else{
            return false;
        }

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
     $this->SocialNetworkModel->setSocialNetworkNewRecord($socialData);
    }
    public function GetUserBySocialId(){
        return $this->SocialNetworkModel->getRecordsByUserId($this->userId);
    }

    public function GetUserBySocialIdSocId(){
        return $this->SocialNetworkModel->GetUserBySocialIdSocId($this->userId,$this->class_id);
    }
    public function setNewCustomerId($id){
        return $this->SocialNetworkModel->setNewCustomerId($id,$this->customer_id);
    }

}