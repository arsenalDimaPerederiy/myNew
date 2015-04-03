<?php
/**
 * @category    Fishpig
 * @package    Fishpig_AttributeSplashPro
 * @license      http://fishpig.co.uk/license.txt
 * @author       Ben Tideswell <ben@fishpig.co.uk>
 */

class Fishpig_AttributeSplashPro_PageController extends Mage_Core_Controller_Front_Action
{
	/**
	 * Display the splash page
	 *
	 */
	public function viewAction()
	{

		if (($page = $this->_getPage()) !== false) {
			Mage::register('current_layer', Mage::getSingleton('splash/layer'));

			$this->_initViewActionLayout($page);

			$this->renderLayout();

		}
		else {
			$this->_forward('noRoute');

		}

	}
	
	/**
	 * Initialise the view action layout for the page
	 * This includes setting META data, page template and other similar things
	 *
	 * @param Fishpig_AttributeSplashPro_Model_Page $page
	 * @return $this
	 */
	protected function _initViewActionLayout(Fishpig_AttributeSplashPro_Model_Page $page)
	{
		$customHandles = array(
			'default',
			'splash_page_view_' . $page->getId(),
		);
		
		if ($template = $page->getTemplate()) {
			array_push($customHandles, 'page_' . $template, 'splash_page_view_' . strtoupper($template));
		}

		$this->_addCustomLayoutHandles($customHandles);

		$layout = $this->getLayout();

		if (($rootBlock = $layout->getBlock('root')) !== false) {
			$rootBlock->addBodyClass('splash-page-' . $page->getId());
		}
				
		if (($headBlock = $layout->getBlock('head')) !== false) {
			if ($page->getMetaDescription()) {
				$headBlock->setDescription($page->getMetaDescription());
			}
			
			if ($page->getMetaKeywords()) {
				$headBlock->setKeywords($page->getMetaKeywords());
			}
			
			if ($page->getRobots()) {
				$headBlock->setRobots($page->getRobots());
			}
			
			if (Mage::getStoreConfigFlag('splash/seo/use_canonical')) {
				$headBlock->addItem('link_rel', $page->getUrl(), 'rel="canonical"');
			}
		}
		
		if (($breadBlock = $layout->getBlock('breadcrumbs')) !== false) {
			$breadBlock->addCrumb('home', array(
				'link' => Mage::getUrl(),
				'label' => $this->__('Home'),
				'title' => $this->__('Home'),				
			));
			
			$breadBlock->addCrumb('splash_page', array(
				'label' => $page->getName(),
				'title' => $page->getName(),
			));
		}
		
		if (($title = $page->getPageTitle()) !== '') {
			$headBlock->setTitle($title);
		}
		
		return $this;
	}

	/**
	 * Adds custom layout handles
	 *
	 * @param array $handles = array()
	 */
	protected function _addCustomLayoutHandles(array $handles = array())
	{
		$update = $this->getLayout()->getUpdate();
		
		foreach($handles as $handle) {
			$update->addHandle($handle);
		}
		
		$this->addActionLayoutHandles();
		$this->loadLayoutUpdates();
		$this->generateLayoutXml();
		$this->generateLayoutBlocks();

		$this->_isLayoutLoaded = true;
		
		return $this;
	}


	/**
	 * Retrieve the current page
	 * This should already have been set in the router so
	 * just retrieve it form the registry
	 *
	 * @return false|Fishpig_AttributeSplashPro_Model_Page
	 */
	protected function _getPage()
	{
		if (($page = Mage::registry('splash_page')) !== null) {
			return $page;
		}
		
		return false;
	}
}