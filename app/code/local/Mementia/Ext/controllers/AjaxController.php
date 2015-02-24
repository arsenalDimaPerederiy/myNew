<?php

class Mementia_Ext_AjaxController extends Mage_Core_Controller_Front_Action
{

    protected function _getUpdatedLayout()
    {
        if ($this->_current_layout === null)
        {
            $layout = $this->getLayout();
            $update = $layout->getUpdate();
            $update->load('onestepcheckout_index_index');
            $layout->generateXml();
            $layout->generateBlocks();
            $this->_current_layout = $layout;
        }

        return $this->_current_layout;
    }

    /**
     * Get mini cart block
     */
    protected function _getCartHtml() {
        $layout	= $this->_getUpdatedLayout();
        return $layout->getBlock('extended_minicart')->setData(array('step' =>  $this->getRequest()->getPost('step',0),'show_all' => 1))->toHtml();
    }

    public function updateAction()
    {
        $paymentdata = $this->getRequest()->getPost('payment', array());

        if($paymentdata) {
            $paymentresult = $this->getOnepage()->savePayment($paymentdata);
        }

        $result = array();
        $result['mini_cart'] = $this->_getCartHtml();

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /* function:includes the core checkout onepage model  */
    public function getOnepage() {
        return Mage::getSingleton('checkout/type_onepage');
    }

}
