<?php
/**
 * Orders Export and Import
 *
 * @category:    Aitoc
 * @package:     Aitoc_Aitexporter
 * @version      1.2.8
 * @license:     ou1zlIlUK4jGhUJZLohhJ5b8jdvumX7FXHqMPgZHkF
 * @copyright:   Copyright (c) 2014 AITOC, Inc. (http://www.aitoc.com)
 */
class Aitoc_Aitexporter_ProcessorController extends Mage_Adminhtml_Controller_Action
{
	protected function _initAction()
	{
		$this->loadLayout()
			->_setActiveMenu('system/convert')
			->_addBreadcrumb(Mage::helper('aitexporter')->__('Aitoc Export/Import Processor'), Mage::helper('aitexporter')->__('Aitoc Export/Import Processor'));
		
		$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
	
		return $this;
	}
	
    public function indexAction()
    {
        /*
        $processor = Mage::getSingleton('aitexporter/processor');
        $processor->setProcess('export::makeExport');
        $this->_forward('run');
        */
        /*
    	$this->_initAction();
    	$this->renderLayout();
    	*/
        $this->_forward('run');
    }
    
    public function runAction()
    {
        $processor = Mage::getSingleton('aitexporter/processor');
        if($processor->isAjax())
        {
            $processor->run();
            $response = Mage::getSingleton('aitexporter/processor_response');
            $config = Mage::getSingleton('aitexporter/processor_config');
            
            $block = $this->getLayout()->createBlock('aitexporter/processor')->toHtml();
            
            $result = array(
                'block'            => $block,
                'continueProcess'  => (bool)$processor->getProcess(),
                'messages'         => $response ->getMessages(),
                'limit'            => $config->get('limit', 0),
                'redirect'         => $response->getRedirect(),
            );
            
            $this->getResponse()->setBody(Zend_Json::encode($result));
        }
    }
}