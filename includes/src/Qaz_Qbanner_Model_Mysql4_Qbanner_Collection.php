<?php

class Qaz_Qbanner_Model_Mysql4_Qbanner_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {

    public function _construct() {
        parent::_construct();
        $this->_init('qbanner/qbanner');
    }

    /**
     * Add Filter by category
     *
     * @param int $category
     */
    public function addCategoryFilter($categoryId) {
        $this->getSelect()->join(
                        array('category_table' => $this->getTable('qbanner/qbanner_category')), 'main_table.qbanner_id = category_table.qbanner_id', array()
                )
                ->where('category_table.category_id = ?', $categoryId);
        return $this;
    }

    /**
     * Add Filter by page
     *
     * @param int $page
     */
    public function addPageFilter($pageId) {
        $this->getSelect()->join(
                        array('page_table' => $this->getTable('qbanner/qbanner_page')), 'main_table.qbanner_id = page_table.qbanner_id', array()
                )
                ->where('page_table.page_id = ?', $pageId);
        return $this;
    }

    /**
     * Add Filter by store
     *
     * @param int|Mage_Core_Model_Store $store
     */
    public function addStoreFilter($store) {
        if (!Mage::app()->isSingleStoreMode()) {
            if ($store instanceof Mage_Core_Model_Store) {
                $store = array($store->getId());
            }

            $this->getSelect()->join(
                            array('store_table' => $this->getTable('qbanner/qbanner_store')), 'main_table.qbanner_id = store_table.qbanner_id', array()
                    )
                    ->where('store_table.store_id in (?)', array(0, $store));
            return $this;
        }
        return $this;
    }
}