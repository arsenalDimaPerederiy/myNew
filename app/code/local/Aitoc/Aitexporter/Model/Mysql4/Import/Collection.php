<?php
/**
 * Orders Export and Import
 *
 * @category:    Aitoc
 * @package:     Aitoc_Aitexporter
 * @version      1.2.9
 * @license:     ou1zlIlUK4jGhUJZLohhJ5b8jdvumX7FXHqMPgZHkF
 * @copyright:   Copyright (c) 2015 AITOC, Inc. (http://www.aitoc.com)
 */
class Aitoc_Aitexporter_Model_Mysql4_Import_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    private $_storeId;

    public function _construct()
    {
        $this->_init('aitexporter/import');
    }
    
    public function setStoreId($storeId)
    {
        $this->_storeId = $storeId;

        return $this;
    }

    public function joinErrorsTable()
    {
        $this->getSelect()
            ->joinLeft(array('ie' => $this->getTable('aitexporter/import_error')), 'ie.import_id = main_table.import_id', array('errors_count' => 'COUNT(ie.error_id)'))
            ->group(array('main_table.import_id'));

        return $this;
    }
    
    public function getSelectCountSql()
    {
        $select = parent::getSelectCountSql();
        $select
            ->resetJoinLeft()
            ->reset(Varien_Db_Select::GROUP);

        return $select;
    }
}