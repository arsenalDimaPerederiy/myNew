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
            $this->network = new Webinse_OAuth_Model_OauthLib_OauthG(Mage::getUrl('customer/account/loginOauthG'));
            $this->network->setCode($code);
            $this->loginOauth();
            $this->_loginPostRedirect();

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

            if ($userByThisSocial->count()>0) {
                $customerIdSoc = $userByThisSocial->getFirstItem()->getCustomerId();
                if ($customerIdSoc != $customerId) {
                    $this->network->setNewCustomerId($userByThisSocial->getFirstItem()->getId());

                }
            }
            $this->_getSession()->loginById($customerId);
        }

    }



}