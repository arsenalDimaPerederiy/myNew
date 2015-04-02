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


    public function __construct($redirectUrl){

        $this->redirect_uri=$redirectUrl;
        $this->class_id='vk';
        $this->clientId=Mage::getStoreConfig('OAuth/OAuth_group_vk/vk_id_key');
        $this->client_secret=Mage::getStoreConfig('OAuth/OAuth_group_vk/vk_secret');
        parent::__construct();
    }

    public function getCode(){
        return self::OAUTH_VK_URI_AUTHORIZATION.'?'.'client_id='.$this->clientId.'&'.'redirect_uri='.$this->redirect_uri.'&'.'response_type=code'.'&'.'scope=email,offline';
    }

    public function getToken(){

        try{
            $client = new Varien_Http_Client(self::OAUTH_VK_URI_GET_TOKEN);
            $client->setMethod(Varien_Http_Client::GET);
            $client->setParameterGet('client_id',$this->clientId);
            $client->setParameterGet('client_secret',$this->client_secret);
            $client->setParameterGet('code',$this->code);
            $client->setParameterGet('redirect_uri',$this->redirect_uri);

            if($client->request()->isSuccessful()){

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
                }
                else{
                    throw new Exception(Webinse_OAuth_Model_OauthLib_Oauth_Oauth::GET_TOKEN_ERROR.'= '.$this->class_id.' access_token is empty');
                }
            }
            else{
                throw new Exception(Webinse_OAuth_Model_OauthLib_Oauth_Oauth::GET_TOKEN_ERROR.'= '.$this->class_id.' '.$client->request()->getMessage());
            }
        }
        catch(Exception $e){
            Mage::logException($e);
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());

            return false;
        }
        return true;
    }

    public function getUserInfo(){

        try{
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
                        throw new Exception(Webinse_OAuth_Model_OauthLib_Oauth_Oauth::GET_USER_DATA_ERROR.'='.$this->class_id.' '.$client_1->request()->getMessage());
                    }
            }
            else{
                throw new Exception(Webinse_OAuth_Model_OauthLib_Oauth_Oauth::GET_USER_DATA_ERROR.'= '.$this->class_id." response is empty");
            }

        }
        catch(Exception $e){
            Mage::logException($e);
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());

            return false;
        }
        return true;
    }
}