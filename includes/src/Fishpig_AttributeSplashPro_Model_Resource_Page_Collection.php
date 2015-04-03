<?php
/**
 * @category    Fishpig
 * @package    Fishpig_AttributeSplashPro
 * @license      http://fishpig.co.uk/license.txt
 * @author       Ben Tideswell <ben@fishpig.co.uk>
 */

class Fishpig_AttributeSplashPro_Model_Resource_Page_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
	/**
	 * Init the entity type
	 *
	 */
	public function _construct()
	{
		$this->_init('splash/page');

		$this->_map['fields']['page_id'] = 'main_table.page_id';
		$this->_map['fields']['store'] = 'store_table.store_id';
	}
	
	/**
	 * Add filter by store
	 *
	 * @param int|Mage_Core_Model_Store $store
	 * @param bool $withAdmin
	 * @return Mage_Cms_Model_Resource_Page_Collection
	*/
	public function addStoreFilter($store, $withAdmin = true)
	{
		if (!$this->getFlag('store_filter_added')) {
			if ($store instanceof Mage_Core_Model_Store) {
				$store = array($store->getId());
			}
	
			if (!is_array($store)) {
				$store = array($store);
			}
	
			if ($withAdmin) {
				$store[] = Mage_Core_Model_App::ADMIN_STORE_ID;
			}
	
			$this->addFilter('store', array('in' => $store), 'public');
		}

		return $this;
	}

	/**
	 * Join store relation table if there is store filter
	 *
	 * @return $this
	*/
	protected function _renderFiltersBefore()
	{
		if ($this->getFilter('store')) {
			$this->getSelect()->join(
				array('store_table' => $this->getTable('splash/page_store')),
				'main_table.page_id = store_table.page_id',
				array()
			)->group('main_table.page_id');
		}

		return parent::_renderFiltersBefore();
	}
}