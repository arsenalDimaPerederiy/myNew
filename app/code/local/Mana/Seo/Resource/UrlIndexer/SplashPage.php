<?php
/** 
 * @category    Mana
 * @package     ManaPro_FilterSeoLinks
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Seo_Resource_UrlIndexer_SplashPage extends Mana_Seo_Resource_AttributeUrlIndexer {
    /**
     * @param Mana_Seo_Model_UrlIndexer $indexer
     * @param Mana_Seo_Model_Schema $schema
     * @param array $options
     */
    public function process($indexer, $schema, $options) {
        if (!isset($options['store_id']) &&
            !isset($options['schema_global_id']) && !isset($options['schema_store_id']) && !$options['reindex_all']
        ) {
            return;
        }
        $db = $this->_getWriteAdapter();

        /* @var $core Mana_Core_Helper_Data */
        $core = Mage::helper('mana_core');

        //$urlKeyExpr = $this->_seoify("COALESCE(vg.value, vs.value)", $schema);
        $fields = array(
            'url_key' => new Zend_Db_Expr('`s`.`url_key`'),
            'internal_name' => new Zend_Db_Expr('`s`.`url_key`'),
            'position' => new Zend_Db_Expr('`s`.`page_id`'),
            'splash_id' => new Zend_Db_Expr('`s`.`page_id`'),
            'type' => new Zend_Db_Expr("'splash'"),
            'is_page' => new Zend_Db_Expr('1'),
            'is_parameter' => new Zend_Db_Expr('0'),
            'is_attribute_value' => new Zend_Db_Expr('0'),
            'is_category_value' => new Zend_Db_Expr('0'),
            'include_filter_name' => new Zend_Db_Expr('0'),
            'schema_id' => new Zend_Db_Expr($schema->getId()),
            'unique_key' => new Zend_Db_Expr("CONCAT(`s`.`page_id`, '-', `s`.`url_key`)"),
            'status' => new Zend_Db_Expr("'" . Mana_Seo_Model_Url::STATUS_ACTIVE . "'"),
            'description' => new Zend_Db_Expr("'splash page'"),
        );

        /* @var $select Varien_Db_Select */
        $select = $db->select()
			->from(array('s' => $this->getTable('splash/page')), null)
            ->joinLeft(array('sid' => $this->getTable('splash/page_store')),
                $db->quoteInto("`sid`.`page_id` = `s`.`page_id` AND `sid`.`store_id` = ?", $schema->getStoreId()),
                null)
        ->columns($fields);

		$obsoleteCondition = "(`schema_id` = ". $schema->getId() .") AND (`is_page` = 1) AND (`type` = 'splash')";
        // convert SELECT into UPDATE which acts as INSERT on DUPLICATE unique keys
        $this->logger()->logUrlIndexer('-----------------------------');
        $this->logger()->logUrlIndexer(get_class($this));
        $this->logger()->logUrlIndexer($select->__toString());
        $this->logger()->logUrlIndexer($schema->getId());
        $this->logger()->logUrlIndexer($obsoleteCondition);
        $this->logger()->logUrlIndexer(json_encode($options));
        $sql = $select->insertFromSelect($this->getTargetTableName(), array_keys($fields));
//Mage::log($sql,null,'test112.log',true);
        // run the statement
        $this->makeAllRowsObsolete($options, $obsoleteCondition);
        $db->exec($sql);
    }
}