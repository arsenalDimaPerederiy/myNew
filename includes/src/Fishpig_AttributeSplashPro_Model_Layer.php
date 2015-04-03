<?php
/**
 * @category    Fishpig
 * @package    Fishpig_AttributeSplashPro
 * @license      http://fishpig.co.uk/license.txt
 * @author       Ben Tideswell <ben@fishpig.co.uk>
 */

class Fishpig_AttributeSplashPro_Model_Layer extends Mage_Catalog_Model_Layer
{
	/**
	 * Retrieve the current category for use when generating product collections
	 * As we are using splash pages and not categories, this returns the splash page
	 *
	 * @return false|Fishpig_AttributeSplashPro_Model_Page
	 */
	public function getCurrentCategory()
	{
		return $this->getPage();
	}

	/**
	 * Retrieve the splash page
	 * We add an array to children_categories so that it can act as a category
	 *
	 * @return false|Fishpig_AttributeSplashPro_Model_Page
	 */
	public function getPage()
	{
		if (($page = Mage::registry('splash_page')) !== null) {
			$page->setChildrenCategories(array());

			return $page;
		}
		
		return false;
	}

    public function getCountTest()
    {

        // clone select from collection with filters
        $page=Mage::getModel('splash/page')->load(Mage::registry('splash_page')->getId());
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
        $select = clone $products->getSelect();
        // reset columns, order and limitation conditions
        $select->reset(Zend_Db_Select::COLUMNS);
        $select->reset(Zend_Db_Select::ORDER);
        $select->reset(Zend_Db_Select::LIMIT_COUNT);
        $select->reset(Zend_Db_Select::LIMIT_OFFSET);

        $connection = Mage::getSingleton('core/resource')->getConnection('core_read');
        $attribute  = Mage::getModel('eav/entity_attribute')->loadByCode('4', 'color');
        $tableAlias = sprintf('%s_idx', $attribute->getAttributeCode());
        $conditions = array(
            "{$tableAlias}.entity_id = e.entity_id",
            $connection->quoteInto("{$tableAlias}.attribute_id = ?", $attribute->getAttributeId()),
            $connection->quoteInto("{$tableAlias}.store_id = ?", Mage::app()->getStore()->getId()),
        );

        $select
            ->join(
                array($tableAlias => 'catalog_product_index_eav'),
                join(' AND ', $conditions),
                array('value', 'count' => new Zend_Db_Expr("COUNT({$tableAlias}.entity_id)")))
            ->group("{$tableAlias}.value");

        return $connection->fetchPairs($select);
    }

}
