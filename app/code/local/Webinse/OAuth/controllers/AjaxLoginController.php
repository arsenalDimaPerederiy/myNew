<?php
/**
 * @category Webinse
 * @package Webinse_All
 * @author Dmitriy Perederiy <perederiy1993@yandex.ua>
 */

class Webinse_OAuth_AjaxLoginController extends Mage_Core_Controller_Front_Action {

    public function createAction(){

        if($this->getRequest()->isAjax()){
            $jsonArray = array();
            $this->getResponse()->setHeader('Content-type', 'application/json');

            $email = $this->getRequest()->getParam('email');
            $password= $this->getRequest()->getParam('password');

            $name=$this->getRequest()->getParam('name');
            $family=$this->getRequest()->getParam('family');

            if(!empty($email)&&!empty($password)){

                $customer = Mage::getModel("customer/customer");
                $customer->setWebsiteId(Mage::app()->getWebsite()->getId());
                $customer->setWebsiteId(Mage::app()->getStore()->getId());
                $customer->loadByEmail($email);

                if(!$customer->getData()){
                    $customer->setFirstname($name)
                             ->setLastname($family)
                             ->setEmail($email)
                             ->setPassword($password);
                    $customer->save();
                    $jsonArray=$this->login($email,$password);

                }
                else{
                    $jsonArray['error']='Пользователь с таким email существует';
                }

            }
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($jsonArray));
    }

    public function indexAction(){

        if ($this->_getSession()->isLoggedIn()) {
            $this->_redirect('*/*/');
            return;
        }
        $jsonArray = array();
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $session = $this->_getSession();

        if ($this->getRequest()->isAjax()){

            $email = $this->getRequest()->getPost('email');
            $password = $this->getRequest()->getPost('password');

            $jsonArray=$this->login($email,$password);

            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($jsonArray));
        }

    }

    protected function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }

    protected function login($email,$password){

        $jsonArray = array();
        $session = $this->_getSession();

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
        return $jsonArray;
    }

} 