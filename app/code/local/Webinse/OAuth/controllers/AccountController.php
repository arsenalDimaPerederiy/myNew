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
        $newOpenAction = array('loginOauthVk,loginOauthF,loginOauthG,createCustomer,loginAjax,forgotPasswordPostAjax');

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
            $this->_Error();
        }

        if(Mage::getStoreConfig('OAuth/Ajax_login_setup/loginAjax')){

            $block = $this->loadLayout()->getLayout()->createBlock('core/template')->setTemplate('Webinse/mainOauthTemplate.phtml');

            $login_block = $this->loadLayout()->getLayout()->createBlock('customer/form_login','login.form')->setTemplate('Webinse/login.phtml');

            $soc_block = $this->loadLayout()->getLayout()->createBlock('webinse_oauth/oauthData','social.icons');
            $forgot_block = $this->loadLayout()->getLayout()->createBlock('customer/account_forgotpassword','forgot.form')->setTemplate('Webinse/forgot_password_form.phtml');
            $create_block =  $this->loadLayout()->getLayout()->createBlock('customer/form_register','create.form')->setTemplate('Webinse/create_form.phtml');

            $login_block->append($soc_block);

            $block ->append($login_block)
                   ->append($forgot_block)
                   ->append($create_block);

            $this->getResponse()->setBody($block->toHtml());
        }
        else{
            parent::loginAction();
        }
    }

    public function loginOauthVkAction()
    {

        try{
            if ($this->_getSession()->isLoggedIn()) {
                $this->_Error();
            }
            $code = $this->getRequest()->getParam('code');

            if (isset($code)) {
                $this->network = new Webinse_OAuth_Model_OauthLib_OauthVk(Mage::getUrl('customer/account/loginOauthVk/'));
                $this->network->setCode($code);
                $this->loginOauth();
                $this->_loginPostRedirect();

            }else{
                throw new Exception('Vk error code');
            }
        }
        catch(Exception $e){
            Mage::logException($e);
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            $this->_Error();
        }


    }

    public function loginOauthFAction()
    {
        try{
            if ($this->_getSession()->isLoggedIn()) {
                $this->_Error();
            }
            $code = $this->getRequest()->getParam('code');

            if (isset($code)) {
                $this->network = new Webinse_OAuth_Model_OauthLib_OauthF(Mage::getUrl('customer/account/loginOauthF'));
                $this->network->setCode($code);
                $this->loginOauth();
                $this->_loginPostRedirect();
            }
            else{
                throw new Exception('f invalid code');
            }
        }
        catch(Exception $e){
            Mage::logException($e);
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            $this->_Error();
        }

    }

    public function loginOauthGAction()
    {
        try{
            if ($this->_getSession()->isLoggedIn()) {
                $this->_Error();
            }
            $code = $this->getRequest()->getParam('code');

            if (isset($code)) {
                $this->network = new Webinse_OAuth_Model_OauthLib_OauthG(Mage::getUrl('customer/account/loginOauthG'));
                $this->network->setCode($code);
                $this->loginOauth();
                $this->_loginPostRedirect();
            }
            else{
                throw new Exception('G error code');
            }
        }
       catch(Exception $e){
           Mage::logException($e);
           Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
           $this->_Error();
       }

    }

    public function loginOauth()
    {
        $message = array();
        try{
            if(!$this->network->getToken('GET')){
                throw new Exception($this->network->class_id.' '.'Token not received');
            }

            if(!$this->network->getUserInfo('GET')){
                throw new Exception($this->network->class_id.' '.'user data not received');
            }

            if (!isset($this->network->email)) {
                $model = $this->network->GetUserBySocialIdSocId();
                if ($model->count() == 0) {
                    $this->network->createUserEmail();
                    $message[]=$this->__('You have been set random email. Please change it to true');
                } //esli net mila to mi ego sozdayom
                else {
                    if(!$this->network->getCustomerEmail($model)){
                        throw new Exception($this->network->class_id.' '.'getCustomerEmail error');
                    }
                }
                //ili ishem polizovatela s takim user id
            }

            $customerId = $this->network->CheckCustomer();
            if (!$customerId) {
                //register user
                if(!$this->network->setNewCustomer()){
                    throw new Exception($this->network->class_id.' '.'new customer not create');
                }
                $customerId=$this->network->customer_id;
                $message[]=Mage::helper('customer')->__('Welcome! Your email has been sent an email with access to your account as a gift');
            }

            $userByThisSocial = $this->network->GetUserBySocialIdSocId();/*get record by user_id in social network and code social network (vk,f)*/

            if ($userByThisSocial->count()>0) {/*if is this user*/
                $customerIdSoc = $userByThisSocial->getFirstItem()->getCustomerId();/*check customer id by this email with customer id in table*/
                if ($customerIdSoc != $customerId) {/*if id is changed// change id in social table*/
                    $this->network->setNewCustomerId($userByThisSocial->getFirstItem()->getId());
                }
            }
            else{
                if(!$this->network->setSocialNewRecord()){//create new record in table social
                    throw new Exception($this->network->class_id.' '.'new social data not create');
                }
            }

        }
        catch(Exception $e){
            Mage::logException($e);
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            $this->_Error();
        }
        $session=$this->_getSession();
        if(empty($message)){
            $session->addSuccess($this->__('Welcome back, %s %s',$this->network->userInfoArray['first_name'],$this->network->userInfoArray['last_name']));
        }
        else{
            foreach($message as $var){
                $session->addSuccess($var);
            }
        }
        $this->_getSession()->loginById($customerId);//load user
    }

    public function loginAjaxAction(){

        if ($this->getRequest()->isAjax()){
            $jsonArray = array();
            $session = $this->_getSession();
            $this->getResponse()->setHeader('Content-type', 'application/json');

            if ($session->isLoggedIn()) {
                $jsonArray['href']=Mage::getBaseUrl();
            }
            else{

                $email = $this->getRequest()->getPost('email');

                if(preg_match ('/test.loc/', $email)!=0){
                    $jsonArray['error']='Вы не можете авторизироватся через данный email.';
                    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($jsonArray));
                    return;
                }

                $password = $this->getRequest()->getPost('password');

                if (!empty($email) && !empty($password)) {
                    try{
                        $session->login($email, $password);
                    }
                    catch (Mage_Core_Exception $e) {
                        switch ($e->getCode()) {
                            case Mage_Customer_Model_Customer::EXCEPTION_EMAIL_NOT_CONFIRMED:
                                $value = $this->_getHelper('customer')->getEmailConfirmationUrl($login['username']);
                                $message = $this->_getHelper('customer')->__('This account is not confirmed. <a href="%s">Click here</a> to resend confirmation email.', $value);
                                break;
                            case Mage_Customer_Model_Customer::EXCEPTION_INVALID_EMAIL_OR_PASSWORD:
                                $message = $e->getMessage();
                                break;
                            default:
                                $message = $e->getMessage();
                        }
                        $jsonArray['error']=$message;
                    }

                } else {
                    $jsonArray['error']='Login and password are incorrect.';
                }
                if($session->isLoggedIn()){
                    $jsonArray['href']=Mage::getUrl('customer/account/index');
                }
            }
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($jsonArray));
        }
    }

    public function createCustomerAction(){

        if ($this->getRequest()->isAjax()){
            $jsonArray = array();
            $session = $this->_getSession();
            $this->getResponse()->setHeader('Content-type', 'application/json');

            if ($session->isLoggedIn()) {
                $jsonArray['href']=Mage::getBaseUrl();
            }
            else{

                if(preg_match ('/.loc/', $this->getRequest()->getParam('email'))!=0){
                    $jsonArray['error']='Вы не можете зарегестрироваться через данный email.';
                    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($jsonArray));
                    return;
                }

                $customer = $this->_getCustomer();

                try {
                    $errors = $this->_getCustomerErrors($customer);

                    if (empty($errors)) {
                        $customer->cleanPasswordsValidationData();
                        $customer->save();
                        $this->_dispatchRegisterSuccess($customer);

                        $session->login($this->getRequest()->getParam('email'),$this->getRequest()->getParam('password'));

                        if($session->isLoggedIn()){
                            $jsonArray['href']=Mage::getUrl('customer/account/index');
                            $session->addSuccess($this->__('Thank you for registering with %s.', Mage::app()->getStore()->getFrontendName()));
                        }
                        else{
                            $jsonArray['href']=Mage::getBaseUrl();
                        }
                    } else {
                        $jsonArray['errors']=$errors;
                    }
                } catch (Mage_Core_Exception $e) {
                    $session->setCustomerFormData($this->getRequest()->getPost());
                    if ($e->getCode() === Mage_Customer_Model_Customer::EXCEPTION_EMAIL_EXISTS) {
                        $jsonArray['error'] = $this->__('There is already an account with this email address.');
                    } else {
                        $jsonArray['error'] = $e->getMessage();
                    }
                } catch (Exception $e) {
                    $session->setCustomerFormData($this->getRequest()->getPost())
                        ->addException($e, $this->__('Cannot save the customer.'));
                }
            }
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($jsonArray));
        }
    }

    public function forgotPasswordPostAjaxAction(){
        if ($this->getRequest()->isAjax()){
            $jsonArray = array();
            $session = $this->_getSession();
            $this->getResponse()->setHeader('Content-type', 'application/json');

            if ($session->isLoggedIn()) {
                $jsonArray['href']=Mage::getBaseUrl();
            }
            else{
                $email = (string) $this->getRequest()->getPost('email');
                if ($email) {
                    if (!Zend_Validate::is($email, 'EmailAddress')) {
                        $this->_getSession()->setForgottenEmail($email);
                        $jsonArray['error']=$this->__('Invalid email address.');
                        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($jsonArray));
                        return;
                    }

                    /** @var $customer Mage_Customer_Model_Customer */
                    $customer = $this->_getModel('customer/customer')
                        ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                        ->loadByEmail($email);

                    if ($customer->getId()) {
                        try {
                            $newResetPasswordLinkToken =  $this->_getHelper('customer')->generateResetPasswordLinkToken();
                            $customer->changeResetPasswordLinkToken($newResetPasswordLinkToken);
                            $customer->sendPasswordResetConfirmationEmail();
                        } catch (Exception $exception) {
                            $jsonArray['error']=$exception->getMessage();
                            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($jsonArray));
                            return;
                        }
                    }
                    else {
                        $jsonArray['error']=$this->__('Please enter your email.');
                        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($jsonArray));
                        return;
                    }

                    $jsonArray['message']=$this->_getHelper('customer')
                        ->__('If there is an account associated with %s you will receive an email with a link to reset your password.',
                            $this->_getHelper('customer')->escapeHtml($email));

                    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($jsonArray));

                    return;
                }
            }
        }
    }

    public function _Error(){
        $this->norouteAction();
    }

    public function SessionMessage(){
         return $this->_getSession();
    }
}