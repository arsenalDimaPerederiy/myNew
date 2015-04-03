<?php

/**
 * @category Webinse
 * @package Webinse_All
 * @author Dmitriy Perederiy <perederiy1993@yandex.ua>
 */
class Webinse_OAuth_Model_OauthLib_OauthG extends Webinse_OAuth_Model_OauthLib_Oauth_Oauth
{

    const OAUTH_G_URI_AUTHORIZATION = 'https://accounts.google.com/o/oauth2/auth';
    const OAUTH_G_URI_GET_TOKEN = 'https://accounts.google.com/o/oauth2/token';
    const OAUTH_G_URI_GET_USER_INFO = 'https://www.googleapis.com/oauth2/v1/userinfo';


    protected $clientId;
    protected $client_secret;
    protected $redirect_uri;
    protected $redirect_uri_code;


    public function __construct($redirectUrl)
    {

        $redirectUrl = substr($redirectUrl, 0, strlen($redirectUrl) - 1);

        $this->redirect_uri = $redirectUrl;

        $ar1 = array("/", ":");
        $ar2 = array("%2F", "%3A");

        $this->redirect_uri_code = str_replace($ar1, $ar2, $redirectUrl);

        $this->class_id = 'g';
        $this->clientId = Mage::getStoreConfig('OAuth/OAuth_group_g/g_id_key');
        $this->client_secret = Mage::getStoreConfig('OAuth/OAuth_group_g/g_secret');
        parent::__construct();
    }

    public function getCode()
    {

        return self::OAUTH_G_URI_AUTHORIZATION . '?' . 'client_id=' . $this->clientId . '&' . 'redirect_uri=' . $this->redirect_uri_code . '&response_type=code' . '&' . 'scope=https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fuserinfo.email+https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fuserinfo.profile"';
    }

    public function getToken()
    {

        try {
            $client = new Varien_Http_Client(self::OAUTH_G_URI_GET_TOKEN);
            $client->setHeaders('Content-type', 'application/x-www-form-urlencoded');
            $client->setMethod(Varien_Http_Client::POST);
            $client->setParameterPost('client_id', $this->clientId);
            $client->setParameterPost('client_secret', $this->client_secret);
            $client->setParameterPost('code', $this->code);
            $client->setParameterPost('redirect_uri', $this->redirect_uri);
            $client->setParameterPost('grant_type', 'authorization_code');

            $userTokenArray = Mage::helper('core')->jsonDecode($client->request()->getBody());

            if (isset($userTokenArray['access_token'])) {
                $this->token = $userTokenArray['access_token'];
            } else {
                throw new Exception(Webinse_OAuth_Model_OauthLib_Oauth_Oauth::GET_TOKEN_ERROR . '= ' . $this->class_id . ' access_token is empty');
            }

        } catch (Exception $e) {
            Mage::logException($e);
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());

            return false;
        }
        return true;
    }


    public function getUserInfo()
    {

        try {
            $client_1 = new Varien_Http_Client(self::OAUTH_G_URI_GET_USER_INFO);
            $client_1->setMethod(Varien_Http_Client::GET);
            $client_1->setParameterGet('access_token', $this->token);
            $userInfo = Mage::helper('core')->jsonDecode($client_1->request()->getBody());

            $this->userId = $userInfo['id'];
            $this->email = $userInfo['email'];

            if (isset($userInfo['id'])) {
                $this->userInfoArray = array(
                    'first_name' => $userInfo['given_name'],
                    'last_name' => $userInfo['family_name']
                );
            } else {
                throw new Exception(Webinse_OAuth_Model_OauthLib_Oauth_Oauth::GET_USER_DATA_ERROR . '=' . $this->class_id . ' ' . $client_1->request()->getMessage());
            }

        } catch (Exception $e) {
            Mage::logException($e);
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());

            return false;
        }
        return true;
    }
}