<?php

class Mementia_OrderView_StatusController extends Mage_Core_Controller_Front_Action
{

    public function formAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('core/session');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Please fill in the form to view order.'));
        $this->renderLayout();
    }
    public function processAction() {
        if ($this->_processAction()) {
            $orderId = (int)$this->getRequest()->getParam('id', null);
            $email = $this->getRequest()->getParam('email', null);
            $this->_redirect('orderview/status/view', array('_secure' => true, 'id' => $orderId, 'email' => rawurlencode($email)));
        }
    }
    protected function _processAction()
    {
        $orderId = (int) $this->getRequest()->getParam('id', null);
        $email = $this->getRequest()->getParam('email', null);

        if (!$orderId || !$email)
        {
            Mage::getSingleton('core/session')->addError(Mage::helper('extended')->__('Wrong Order ID and\or Email is supplied.'));
            session_write_close();
            $this->_redirect('orderview/status/form', array('_secure' => true, 'id' => $orderId, 'email' => rawurlencode($email)));
            return false;
        }

        $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
        if (!$order || !$order->getId())
        {
            Mage::getSingleton('core/session')->addError(Mage::helper('extended')->__('Cannot find requested order.'));
            //session_write_close();
            $this->_redirect('orderview/status/form', array('_secure' => true, 'id' => $orderId, 'email' => rawurlencode($email)));
            return false;
        }

        $key = md5($order->getId() . $email);
        Mage::getSingleton('core/session')->setData('direct_' . $key, true);


        return true;
    }

    public function viewAction()
    {
        $this->_viewAction();
    }

    protected function _viewAction()
    {
        if (!$this->_loadValidOrder())
        {
            return;
        }

        Mage::register('direct_order_view', true);

        $this->loadLayout();
        $this->_initLayoutMessages('core/session');
        $this->renderLayout();
    }

    protected function _loadValidOrder($orderId = null)
    {
        if (null === $orderId)
        {
            $this->_processAction();
            $orderId = (int) $this->getRequest()->getParam('id');
        }

        if (!$orderId)
        {
            $this->_forward('noRoute');
            return false;
        }

        $email = $this->getRequest()->getParam('email');
        if (!$email)
        {
            $this->_forward('noRoute');
            return false;
        }

        $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
        $orderEmail = $order->getCustomerEmail();
        $shippingAddress = $order->getShippingAddress();
        $billingAddress = $order->getBillingAddress();

        $isValid = false;
        if ($email == $orderEmail)
        {
            $isValid = true;
        }
        else if ($shippingAddress && ($email == $shippingAddress->getEmail()))
        {
            $isValid = true;
        }
        else if ($billingAddress && ($email == $billingAddress->getEmail()))
        {
            $isValid = true;
        }
//        Mage::log($order);
        if ($isValid && $this->_canViewOrder($order))
        {
            Mage::register('current_order', $order);
            return true;
        }
        else
        {
            Mage::getSingleton('core/session')->addError(Mage::helper('extended')->__('Wrong Order ID and\or Email is supplied.'));
            $this->_redirect('orderview/status/form', array('_secure' => true, 'id' => $orderId, 'email' => rawurlencode($email)));
            return false;
        }
    }

    protected function _canViewOrder($order)
    {
        $session = Mage::getSingleton('core/session');

        $key1 = false;
        $key2 = false;
        $key3 = false;

        if ($order->getCustomerEmail())
        {
            $key1 = md5($order->getId() . $order->getCustomerEmail());
        }

        if ($order->getShippingAddress() && $order->getShippingAddress()->getEmail())
        {
            $key2 = md5($order->getId() . $order->getShippingAddress()->getEmail());
        }

        if ($order->getBillingAddress() && $order->getBillingAddress()->getEmail())
        {
            $key3 = md5($order->getId() . $order->getBillingAddress()->getEmail());
        }

        if (($key1 && ($session->getData('direct_' . $key1) === true))
            || ($key2 && ($session->getData('direct_' . $key2) === true))
            || ($key3 && ($session->getData('direct_' . $key3) === true)))
        {
            return true;
        }

        return false;
    }

    public function getFullActionName($delimiter = '_')
    {
        $routeName = $this->getRequest()->getRequestedRouteName();
        $controllerName = $this->getRequest()->getRequestedControllerName();
        $actionName = $this->getRequest()->getRequestedActionName();

        if ($actionName == 'view')
        {
            $routeName = 'sales';
            $controllerName = 'order';
        }

        return $routeName . $delimiter . $controllerName . $delimiter . $actionName;
    }

    public function historyAction() {
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            Mage::app()->getResponse()->setRedirect(Mage::getUrl('sales/order/history'));

        }
        else
            Mage::app()->getResponse()->setRedirect(Mage::getUrl('*/*/form'));
        return;
    }
}