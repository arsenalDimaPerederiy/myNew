<?php
/**
 * @category Webinse
 * @package Webinse_All
 * @author Dmitriy Perederiy <perederiy1993@yandex.ua>
 */


require_once Mage::getModuleDir('controllers', 'Mage_Customer') . '/AccountController.php';

class Webinse_OAuth_AccountController extends Mage_Customer_AccountController
{

    protected $network;


    public function preDispatch()
    {
        $action = $this->getRequest()->getActionName();
        $ExitsopenActions = array(
            'create',
            'login',
            'logoutsuccess',
            'forgotpassword',
            'forgotpasswordpost',
            'resetpassword',
            'resetpasswordpost',
            'confirm',
            'confirmation'
        );
        $newOpenAction = array('loginOauthVk,loginOauthF,loginOauthG');

        $allActions = array_merge($ExitsopenActions, $newOpenAction);

        /* check custom action */
        $Custompattern = '/^(' . implode('|', $newOpenAction) . ')/i';


        if (preg_match($Custompattern, $action)) {
            /* if match then set Current action to create for skip  parent::preDispatch(); */
            $this->getRequest()->setActionName('create');
        }
        parent::preDispatch();

        /**
         * Parent check is complete, reset request action name to origional value
         */
        if ($action != $this->getRequest()->getActionName()) {
            $this->getRequest()->setActionName($action);
        }
        if (!$this->getRequest()->isDispatched()) {
            return;
        }

        $mypattern = '/^(' . implode('|', $allActions) . ')/i';

        if (!preg_match($mypattern, $action)) {
            if (!$this->_getSession()->authenticate($this)) {
                $this->setFlag('', 'no-dispatch', true);
            }
        } else {
            $this->_getSession()->setNoReferer(true);
        }

    }


    public function loginAction()
    {

        if ($this->_getSession()->isLoggedIn()) {
            $this->_redirect('*/*/');
            return;
        }

        $block = $this->loadLayout()->getLayout()->getBlock('customer_form_login')->setTemplate('Webinse/login.phtml');
        $this->getResponse()->setBody($block->toHtml());

    }

    public function loginOauthVkAction()
    {
        if ($this->_getSession()->isLoggedIn()) {
            $this->_redirect('*/*/');
            return;
        }
        $code = $this->getRequest()->getParam('code');

        if (isset($code)) {
            $this->network = new Webinse_OAuth_Model_OauthLib_OauthVk(Mage::getUrl('customer/account/loginOauthVk/'));
            $this->network->setCode($code);
            $this->loginOauth();
            $this->_loginPostRedirect();

        }

    }

    public function loginOauthFAction()
    {

        if ($this->_getSession()->isLoggedIn()) {
            $this->_redirect('*/*/');
            return;
        }
        $code = $this->getRequest()->getParam('code');

        if (isset($code)) {
            $this->network = new Webinse_OAuth_Model_OauthLib_OauthF(Mage::getUrl('customer/account/loginOauthF'));
            $this->network->setCode($code);
            $this->loginOauth();
            $this->_loginPostRedirect();
        }
    }

    public function loginOauthGAction()
    {
        if ($this->_getSession()->isLoggedIn()) {
            $this->_redirect('*/*/');
            return;
        }
        $code = $this->getRequest()->getParam('code');

        if (isset($code)) {
            $this->network = new Webinse_OAuth_Model_OauthLib_OauthG(Mage::getUrl('customer/account/loginOauthG/'));
            $this->network->setCode($code);
            /*$this->loginOauth();
            $this->_loginPostRedirect();*/

        }
    }

    public function loginOauth()
    {

        $this->network->getToken('GET');

        $this->network->getUserInfo('GET');

        if (!isset($this->network->email)) {
            $model = $this->network->GetUserBySocialIdSocId();
            if ($model->count() == 0) {
                $this->network->createUserEmail();
            } //esli net mila to mi ego sozdayom
            else {
                $customer = $model->getFirstItem()->getCustomerId();
                $StoreId = $model->getFirstItem()->getStoreId();
                $websiteId = $model->getFirstItem()->getWebsiteId();

                $customerModel = Mage::getModel('customer/customer')
                    ->setWebsiteId($websiteId)
                    ->setStoreId($StoreId);
                $customerModel->load($customer);
                $this->network->email = $customer->getEmail();
            }
            //ili ishem polizovatela s takim user id
        }

        $customerId = $this->network->CheckCustomer();
        if (!$customerId) {

            //register user
            $this->network->setNewCustomer();

            $userByThisSocial = $this->network->GetUserBySocialIdSocId();

            if (!$userByThisSocial->getData()) {
                $this->network->setSocialNewRecord();
            } else {
                $this->network->changeEmailCustomer();
            }
            $this->_getSession()->loginById($this->network->customer_id);
        } else {

            $userByThisSocial = $this->network->GetUserBySocialIdSocId();

            if ($userByThisSocial->getData()) {
                $customerIdSoc = $userByThisSocial->getFirstItem()->getCustomerId();
                if ($customerIdSoc != $customerId) {
                    $this->_getSession()->loginById($customerId);
                }
            } else {
                $this->_getSession()->loginById($customerId);
            }
        }

    }

    /*public  function _loginPostRedirect()
    {
        $session = $this->_getSession();
        if ($referer = $this->getRequest()->getParam(Mage_Customer_Helper_Data::REFERER_QUERY_PARAM_NAME)) {
            $referer = Mage::helper('core')->urlDecode($referer);
            if ((strpos($referer, Mage::app()->getStore()->getBaseUrl()) === 0)
                || (strpos($referer, Mage::app()->getStore()->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK, TRUE)) === 0)) {
                $session->setBeforeAuthUrl($referer);
            } else {
                $session->setBeforeAuthUrl(Mage::helper('customer')->getDashboardUrl());
            }
        } else {
            $session->setBeforeAuthUrl(Mage::helper('customer')->getDashboardUrl());
        }

        $this->_redirectUrl($session->getBeforeAuthUrl(TRUE));
    }*/


    /*    public function checkAction(){

            if ($this->_getSession()->isLoggedIn()) {
                $this->_redirect('');
                return;
            }
            $cookie=$this->getUserCookie();
            if($cookie!=null){
                $social_id=$this->getRequest()->getParam('socialId');
                if(isset($cookie[$social_id])){
                    $social= Mage::getModel('webinse_oauth/Oauth')->load($cookie[$social_id]);
                    $customerId=$social->getCustomerId();
                    $this->_getSession()->loginById($customerId);
                    $this->getResponse()->setBody($successUrl);
                }

            }
            else{
                $this->getResponse()->setBody('0');
            }
        }*/


    /*public function getUserCookie(){
        return $this->getSerialize(Mage::getModel('core/cookie')->get('WebinseOauth'));
    }

    public function setUserCookie($data){
        $name = 'WebinseOauth';
        $value = $this->setSerialize($data);
        $period = 3600; //Mage::getModel('core/cookie')->getLifetime()
        $path = '/';
        $domain = Mage::getModel('core/cookie')->getDomain();
        $secure = Mage::getModel('core/cookie')->isSecure();
        $httponly = false;
        Mage::getModel('core/cookie')->set($name, $value, $period, $path, $domain, $secure, $httponly);
    }

    public function deleteUserCookie(){

        $name = 'WebinseOauth';
        $path = Mage::getModel('core/cookie')->getPath();
        $domain = Mage::getModel('core/cookie')->getDomain();
        $secure = Mage::getModel('core/cookie')->isSecure();
        $httponly = false;
        Mage::getModel('core/cookie')->delete($name, $path, $domain, $secure, $httponly);
    }*/


    /*
        public function getSerialize($data){
            return unserialize($data);
        }

        public function setSerialize($data){
            return serialize($data);
        }*/


}