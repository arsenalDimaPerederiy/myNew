<?php

class Mementia_Ext_AuthorizeController extends Mage_Core_Controller_Front_Action
{


    public function indexAction()
    {
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {

            $this->_redirectUrl(Mage::getUrl('onestepcheckout', array('_secure'=>true)));
            return;
        }
        if ($this->getRequest()->isPost()) {
            $login = $this->getRequest()->getPost('login');

            $session = Mage::getSingleton('customer/session');
            if ($login['password_shown'] == 1 && !empty($login['username']) && !empty($login['password'])) {
                try {
                    $session->login($login['username'], $login['password']);
//                    Mage::getSingleton('core/session')->setAuthorizeLogin(1);
                    $this->_redirectUrl(Mage::getUrl('onestepcheckout', array('_secure'=>true, '_query' => array('authorize' => 1))));
                    return;
                } catch (Mage_Core_Exception $e) {
                    switch ($e->getCode()) {
                        case Mage_Customer_Model_Customer::EXCEPTION_EMAIL_NOT_CONFIRMED:
                            $value = Mage::helper('customer')->getEmailConfirmationUrl($login['username']);
                            $message = Mage::helper('customer')->__('This account is not confirmed. <a href="%s">Click here</a> to resend confirmation email.', $value);
                            break;
                        case Mage_Customer_Model_Customer::EXCEPTION_INVALID_EMAIL_OR_PASSWORD:
                            $message = $e->getMessage();
                            break;
                        default:
                            $message = $e->getMessage();
                    }
                    Mage::getSingleton('core/session')->addError($message);
                    $session->setUsername($login['username']);
                    $this->_redirect('*/*/');
                    return;
                } catch (Exception $e) {
                    // Mage::logException($e); // PA DSS violation: this exception log can disclose customer password
                }
            } else {
                Mage::getSingleton('core/session')->setAuthorizeEmail($login['username']);
                $this->_redirectUrl(Mage::getUrl('onestepcheckout', array('_secure'=>true)));
                return;
            }
        }
        $this->loadLayout();
        $this->renderLayout();
    }
}