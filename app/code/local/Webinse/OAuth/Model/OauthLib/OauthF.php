<?php
/**
 * @category Webinse
 * @package Webinse_All
 * @author Dmitriy Perederiy <perederiy1993@yandex.ua>
 */
class Webinse_OAuth_Model_OauthLib_OauthF extends Webinse_OAuth_Model_OauthLib_Oauth_Oauth{


    const OAUTH_F_URI_AUTHORIZATION = 'https://www.facebook.com/dialog/oauth';
    const OAUTH_F_URI_GET_TOKEN ='https://graph.facebook.com/oauth/access_token';
    const OAUTH_F_URI_GET_USER_INFO ='https://graph.facebook.com/me';


    protected $clientId;
    protected $client_secret;
    protected $redirect_uri;

    public $SocialNetworkModel;
    public $class_id='f';

    public function __construct($redirectUrl){

        $this->redirect_uri=$redirectUrl;
        $this->clientId=Mage::getStoreConfig('OAuth/OAuth_group_f/f_id_key');
        $this->client_secret=Mage::getStoreConfig('OAuth/OAuth_group_f/f_secret');
        $this->SocialNetworkModel=Mage::getModel('webinse_oauth/Oauth');
        parent::__construct();
    }

    public function getCode(){
        return self::OAUTH_F_URI_AUTHORIZATION.'?'.'client_id='.$this->clientId.'&'.'redirect_uri='.$this->redirect_uri.'&'.'response_type=code';
    }

    public function getToken(){

        $client = new Varien_Http_Client(self::OAUTH_F_URI_GET_TOKEN);
        $client->setMethod(Varien_Http_Client::GET);
        $client->setParameterGet('client_id',$this->clientId);
        $client->setParameterGet('code',$this->code);
        $client->setParameterGet('client_secret',$this->client_secret);
        $client->setParameterGet('redirect_uri',$this->redirect_uri);

        $userTokenArray=$client->request()->getBody();
        $keywords = explode("&", $userTokenArray);
        if(count($keywords)==2){
            $keywords=explode('=',$keywords[0]);
            $this->token=$keywords[1];
        }

    }

    public function getUserInfo(){

        $client_1 = new Varien_Http_Client(self::OAUTH_F_URI_GET_USER_INFO);
        $client_1->setMethod(Varien_Http_Client::GET);
        $client_1->setParameterGet('access_token',$this->token);
        $client_1->setParameterGet('fields','id,first_name,email,last_name');

        $this->userInfoArray=Mage::helper('core')->jsonDecode($client_1->request()->getBody());
        $this->email=$this->userInfoArray['email'];
        $this->userId=$this->userInfoArray['id'];

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


}