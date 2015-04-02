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


    public function __construct($redirectUrl){

        $this->redirect_uri=$redirectUrl;
        $this->class_id='f';
        $this->clientId=Mage::getStoreConfig('OAuth/OAuth_group_f/f_id_key');
        $this->client_secret=Mage::getStoreConfig('OAuth/OAuth_group_f/f_secret');
        parent::__construct();
    }

    public function getCode(){
        return self::OAUTH_F_URI_AUTHORIZATION.'?'.'client_id='.$this->clientId.'&'.'redirect_uri='.$this->redirect_uri.'&'.'response_type=code';
    }

    public function getToken(){

        try{
            $client = new Varien_Http_Client(self::OAUTH_F_URI_GET_TOKEN);
            $client->setMethod(Varien_Http_Client::GET);
            $client->setParameterGet('client_id',$this->clientId);
            $client->setParameterGet('code',$this->code);
            $client->setParameterGet('client_secret',$this->client_secret);
            $client->setParameterGet('redirect_uri',$this->redirect_uri);

            if($client->request()->isSuccessful()){
                $userTokenArray=$client->request()->getBody();
                $keywords = explode("&", $userTokenArray);
                if(count($keywords)==2){
                    $keywords=explode('=',$keywords[0]);
                    $this->token=$keywords[1];
                }
                else{
                    throw new Exception(Webinse_OAuth_Model_OauthLib_Oauth_Oauth::GET_TOKEN_ERROR.'='.$this->class_id.' '.'Token is empty');
                }
            }
            else{
                throw new Exception(Webinse_OAuth_Model_OauthLib_Oauth_Oauth::GET_TOKEN_ERROR.'='.$this->class_id.' '.$client->request()->getMessage());
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
            $client_1 = new Varien_Http_Client(self::OAUTH_F_URI_GET_USER_INFO);
            $client_1->setMethod(Varien_Http_Client::GET);
            $client_1->setParameterGet('access_token',$this->token);
            $client_1->setParameterGet('fields','id,first_name,email,last_name');

            if($client_1->request()->isSuccessful())
            {
                $this->userInfoArray=Mage::helper('core')->jsonDecode($client_1->request()->getBody());
                if((isset($this->userInfoArray['email']))&&(isset($this->userInfoArray['id']))){
                    $this->email=$this->userInfoArray['email'];
                    $this->userId=$this->userInfoArray['id'];
                }
                else{
                    throw new Exception(Webinse_OAuth_Model_OauthLib_Oauth_Oauth::GET_USER_DATA_ERROR.'='.$this->class_id.' '.$client_1->request()->getMessage());
                }
            }
            else{
                throw new Exception(Webinse_OAuth_Model_OauthLib_Oauth_Oauth::GET_USER_DATA_ERROR.'= '.$this->class_id." email and id are empty");
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