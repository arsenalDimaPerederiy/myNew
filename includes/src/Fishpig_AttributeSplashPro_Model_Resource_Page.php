<?php
/**
 * @category    Fishpig
 * @package    Fishpig_AttributeSplashPro
 * @license      http://fishpig.co.uk/license.txt
 * @author       Ben Tideswell <ben@fishpig.co.uk>
 */

class Fishpig_AttributeSplashPro_Model_Resource_Page extends Mage_Core_Model_Resource_Db_Abstract
{
	/**
	 * The default operator used for multiselect attributes
	 *
	 * @var const string
	 */
	const FILTER_OPERATOR_DEFAULT = 'AND';

	/**
	 * Fields to be serialized before saving
	 * This applies to the filter fields
	 *
	 * @var array
	 */
     protected $_serializableFields = array(
     	'category_filters' => array('a:0:{}', array()),
     	'option_filters' => array('a:0:{}', array()),
     	'price_filters' => array('a:0:{}', array()),
     );
    
	/**
	 * Default attribute codes which can't be used for splash pages
	 *
	 * @var array
	 */
	protected $_nonSplashableAttributes = array(
		'country_of_manufacture',
		'custom_design',
		'enable_googlecheckout',
		'gift_message_available',
		'is_recurring',
		'msrp_enabled',
		'msrp_display_actual_price_type',
		'options_container',
		'page_layout',
		'price_view',
		'status',
		'visibility',
	);
	
	/**
	 * An array of fields that can be used for price filters
	 * These aren't attributes but MySQL query column names
	 *
	 * @var array
	 */
	protected $_splashablePriceFields = array(
		'price' => 'Price', 
		'final_price' => 'Final Price',
	);
	
	/**
	 * Init the entity type
	 *
	 */
	public function _construct()
	{
		$this->_init('splash/page', 'page_id');
	}
	
	/**
	 * Retrieve select object for load object data
	 * This gets the default select, plus the attribute id and code
	 *
	 * @param   string $field
	 * @param   mixed $value
	 * @return  Zend_Db_Select
	*/
	protected function _getLoadSelect($field, $value, $object)
	{
		$select = $this->_getReadAdapter()->select()
			->from(array('main_table' => $this->getMainTable()))
			->where("`main_table`.`{$field}` = ?", $value)
			->limit(1);
			
		$adminId = Mage_Core_Model_App::ADMIN_STORE_ID;
		
		$storeId = $object->getStoreId();
		
		if ($storeId !== $adminId) {
			$select->join(
				array('store' => $this->getTable('splash/page_store')),
				$this->_getReadAdapter()->quoteInto('`store`.`page_id` = `main_table`.`page_id` AND `store`.`store_id` IN (?)', array($adminId, $storeId))
			);
			
			$select->order('store.store_id DESC');
		}
		
		return $select;
	}
	
	/**
	 * Retrieve a collection of products created by the page
	 *
	 * @param Fishpig_AttributeSplashPro_Model_Page $page
	 * @return Mage_Catalog_Model_Resource_Product_Collection
	 */
	public function getProductCollection(Fishpig_AttributeSplashPro_Model_Page $page)
	{
		$products = Mage::getResourceModel('catalog/product_collection');
		
		if (is_array($categoryIds = $page->getCategoryIds())) {
			if ($page->getCategoryOperator() === 'AND' || count($categoryIds) === 1) {
				foreach($categoryIds as $categoryId) {
					$products->addCategoryFilter(Mage::getModel('catalog/category')->setId($categoryId));
				}
			}
			else {
				$read = Mage::getSingleton('core/resource')->getConnection('core_read');
				$cond = $read->quoteInto('`splash_category`.`product_id` = `e`.`entity_id` AND `splash_category`.`category_id` IN (?)', $categoryIds);

				$products->getSelect()->distinct()
					->join(array('splash_category' => $this->getTable('catalog/category_product')),
						$cond,
						''
					);
			}
		}
		
		if (is_array($optionFilters = $page->getOptionFilters())) {
			foreach($optionFilters as $attribute => $data) {
				$values = (array)$data['value'];
				$operator = isset($data['operator']) ? $data['operator'] : self::FILTER_OPERATOR_DEFAULT;

				if ($operator === 'OR') {

					//$products->addAttributeToFilter($attribute, array('in' => $values));

				$alias = $attribute.'_index';
				$read = Mage::getSingleton('core/resource')->getConnection('read');
				$storeId = ($page->getStoreId() == 0) ? Mage::app()->getStore()->getId() : $page->getStoreId();
				$attributeModel = Mage::getModel('eav/entity_attribute')->loadByCode(4, $attribute);
				$products->getSelect()
					->join(
					array($alias => $this->getTable('catalog/product_index_eav')),
					"`{$alias}`.`entity_id` = `e`.`entity_id`"
					. $read->quoteInto(" AND `{$alias}`.`attribute_id` = ? ", $attributeModel->getAttributeId())
					. $read->quoteInto(" AND `{$alias}`.`store_id` = ? ", $storeId)
					. $read->quoteInto(" AND `{$alias}`.`value` = ?", $data['value']),
					''
				);



				}
				else {
					foreach($values as $value) {
						$products->addAttributeToFilter($attribute, $value);
					}
				}
			}
		}


		if (is_array($priceFilters = $page->getPriceFilters())) {
			$splashableFields = $this->getSplashablePriceFields();

			foreach($priceFilters as $attribute => $data) {
				if (!isset($splashableFields[$attribute])) {
					continue;
				}
				
				if ($data['min']) {
					$products->getSelect()->where('price_index.' . $attribute . ' >= ?',  $data['min']);
				}
				
				if ($data['max']) {
					$products->getSelect()->where('price_index.' . $attribute . ' <= ?',  $data['max']);
				}
			}
		}


		return $products;	
	}

	/**
	 * Set the option filter array and clean it
	 *
	 * @param Fishpig_AttributeSplashPro_Model_Page $page
	 * @param array $filters
	 * @return $this
	 */
	public function setCategoryFilters(Fishpig_AttributeSplashPro_Model_Page $page, $filters)
	{
		$page->setData('category_filters', $this->_cleanCategoryFilters($filters));
		
		return $this;
	}
	
	/**
	 * Clean the category filter data
	 *
	 * @param array $filters
	 * @return array
	 */
	protected function _cleanCategoryFilters(array $filters)
	{
		if (!isset($filters['ids']) || !is_array($filters['ids'])) {
			$filters['ids'] = array();
		}
		
		$filters['ids'] = array_unique($filters['ids']);
		
		if (!isset($filters['operator']) || empty($filters['operator'])) {
			$fitlers['operator'] = self::FILTER_OPERATOR_DEFAULT;
		}

		return $filters;
	}

	/**
	 * Set the option filter array and clean it
	 *
	 * @param Fishpig_AttributeSplashPro_Model_Page $page
	 * @param array $filters
	 * @return $this
	 */
	public function setOptionFilters(Fishpig_AttributeSplashPro_Model_Page $page, $filters)
	{
		$page->setData('option_filters', $this->_cleanOptionFilters($filters));
		
		return $this;
	}
	
	/**
	 * Clean the filter array before saving and after loading
	 *
	 * @param array $filters
	 * @return array
	 */
	protected function _cleanOptionFilters(array $filters)
	{
		foreach($filters as $attribute => $data) {
			if (!isset($data['value'])) {
				unset($filters[$attribute]);
				continue;
			}	
			
			if (is_array($data['value'])) {
				foreach($data['value'] as $key => $value) {
					if (trim($value) === '') {
						unset($data['value'][$key]);
					}
				}
				
				if (count($data['value']) === 0) {
					unset($filters[$attribute]);
					continue;
				}
			
				if (!isset($data['operator'])) {
					$filters[$attribute]['operator'] = self::FILTER_OPERATOR_DEFAULT;
				}
			}
			else if (trim($data['value']) === '') {
				unset($filters[$attribute]);
				continue;
			}
		}

		return $filters;
	}

	/**
	 * Retrieve the array of price fields that can be used in the filter
	 *
	 * @return array
	 */
	public function getSplashablePriceFields()
	{
		return $this->_splashablePriceFields;
	}
	
	/**
	 * Set the price filter array and clean it
	 *
	 * @param Fishpig_AttributeSplashPro_Model_Page $page
	 * @param array $filters
	 * @return $this
	 */
	public function setPriceFilters(Fishpig_AttributeSplashPro_Model_Page $page, $filters)
	{
		$page->setData('price_filters', $this->_cleanPriceFilters($filters));
		
		return $this;
	}

	/**
	 * Clean the filter array before saving and after loading
	 *
	 * @param array $filters
	 * @return array
	 */
	protected function _cleanPriceFilters(array $filters)
	{
		foreach($filters as $attribute => $filter) {
			if (!isset($filter['min']) || !isset($filter['max'])) {
				unset($filters[$key]);
				continue;
			}
			
			list($min, $max) = array_values($filter);

			if (!$min) {
				$min = null;
			}
			
			if (!$max) {
				$max = null;
			}
			
			if (is_null($min) && is_null($max)) {
				unset($filters[$attribute]);
				continue;
			}
			
			$filters[$attribute] = array(
				'min' => $min,
				'max' => $max,
			);
		}

		return $filters;
	}
		
	/**
	 * Convert the filter data into a format used to pre-fill Admin forms
	 *
	 * @param Fishpig_AttributeSplashPro_Model_Page $page
	 * @return array
	 */
	protected function _loadAdminData(Fishpig_AttributeSplashPro_Model_Page $page)
	{
		$adminFilterData = array();
		
		if (is_array($options = $page->getOptionFilters())) {
			foreach($options as $key => $data) {
				if (is_array($data) && isset($data['value']) && $data['value'] !== '') {
					$adminFilterData['option_filters_' . $key . '_value'] = $data['value'];
					$adminFilterData['option_filters_' . $key . '_operator'] = isset($data['operator']) && $data['operator'] ? $data['operator'] : 'AND';
				}
			}
		}
		
		if (is_array($prices = $page->getPriceFilters())) {
			foreach($prices as $attribute => $price) {
				$adminFilterData['price_filters_' . $attribute . '_attribute'] = $attribute;
				$adminFilterData['price_filters_' . $attribute . '_min'] = $price['min'];
				$adminFilterData['price_filters_' . $attribute . '_max'] = $price['max'];
			}
		}

		if ($operator = $page->getCategoryOperator()) {
			$adminFilterData['category_operator'] = $operator;
		}

		$page->setAdminFilterData($adminFilterData);
	}
		
	/**
	 * Function called after a model is loaded (but not when a collection of models are loaded)
	 * If filters set, unserialize (convert to an array)
	 *
	 * @param Mage_Core_Model_Abstract $object
	 * @return $this
	 */
	protected function _afterLoad(Mage_Core_Model_Abstract $object)
	{
		if ($object->getId()) {
			$stores = $this->lookupStoreIds($object->getId());

			$object->setData('store_id', $stores);
		}
		
		if ($this->isAdmin()) {
			$this->_loadAdminData($object);
		}
		
		return parent::_afterLoad($object);
	}

	/**
	 * Function called before a model is saved
	 * Serializes the filters array (if set) and performs other data checks
	 *
	 * @param Mage_Core_Model_Abstract $object
	 * @return $this
	 */
	protected function _beforeSave(Mage_Core_Model_Abstract $object)
	{
		if ($object->isObjectNew()) {
			$object->setCreatedAt(Mage::getSingleton('core/date')->gmtDate());
		}

		$object->setUpdatedAt(Mage::getSingleton('core/date')->gmtDate());
		
		return parent::_beforeSave($object);
	}

	/**
	 * Function called after a model is saved
	 * Save store associations
	 *
	 * @param Mage_Core_Model_Abstract $object
	 * @return $this
	 */
	protected function _afterSave(Mage_Core_Model_Abstract $object)
	{
		if ($object->getId()) {
			$oldStores = $this->lookupStoreIds($object->getId());
			$newStores = (array)$object->getStores();
	
			if (empty($newStores)) {
				$newStores = (array)$object->getStoreId();
			}
	
			$table  = $this->getTable('splash/page_store');
			$insert = array_diff($newStores, $oldStores);
			$delete = array_diff($oldStores, $newStores);
			
			if ($delete) {
				$this->_getWriteAdapter()->delete($table, array('page_id = ?' => (int) $object->getId(), 'store_id IN (?)' => $delete));
			}
			
			if ($insert) {
				$data = array();
			
				foreach ($insert as $storeId) {
					$data[] = array(
						'page_id'  => (int) $object->getId(),
						'store_id' => (int) $storeId
					);
				}

				$this->_getWriteAdapter()->insertMultiple($table, $data);
			}
		}
		
		return parent::_afterSave($object);
	}
	
	/**
	 * Retrieve all attributes that can be used as option
	 * filters for splash pages
	 *
	 * @return
	 */
	public function getSplashableAttributes()
	{
		$productEntityTypeId = Mage::getResourceModel('catalog/product')->getTypeId();
		
		$collection = Mage::getResourceModel('eav/entity_attribute_collection')
			->setEntityTypeFilter($productEntityTypeId)
			->addFieldToFilter('attribute_code', array('nin' => $this->_nonSplashableAttributes))
			->addFieldToFilter('frontend_input', array('in' => array('select', 'multiselect', 'boolean')))
			->load();
		
		return $collection;
	}
	
	/**
	 * Get store ids to which specified item is assigned
	 *
	 * @param int $id
	 * @return array
	*/
	public function lookupStoreIds($pageId)
	{
		$select = $this->_getReadAdapter()->select()
			->from($this->getTable('splash/page_store'), 'store_id')
			->where('page_id = ?', (int)$pageId);
	
		return $this->_getReadAdapter()->fetchCol($select);
	}
	
	/**
	 * Determine whether the current store is the Admin store
	 *
	 * @return bool
	 */
	public function isAdmin()
	{
		return (int)Mage::app()->getStore()->getId() === Mage_Core_Model_App::ADMIN_STORE_ID;
	}
}
