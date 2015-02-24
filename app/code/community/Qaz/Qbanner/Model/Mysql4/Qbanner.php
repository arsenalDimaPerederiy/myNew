<?php

class Qaz_Qbanner_Model_Mysql4_Qbanner extends Mage_Core_Model_Mysql4_Abstract {

    public function _construct() {
        // Note that the qqbanner_id refers to the key field in your database table.
        $this->_init('qbanner/qbanner', 'qbanner_id');
    }

    /**
     * Load images
     */
    public function loadImage(Mage_Core_Model_Abstract $object) {
        return $this->__loadImage($object);
    }

    /**
     *
     * @param Mage_Core_Model_Abstract $object
     */
    protected function _afterLoad(Mage_Core_Model_Abstract $object) {
        if (!$object->getIsMassDelete()) {
            $object = $this->__loadStore($object);
            $object = $this->__loadPage($object);
            $object = $this->__loadCategory($object);
            $object = $this->__loadImage($object);
        }

        return parent::_afterLoad($object);
    }

    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @return Zend_Db_Select
     */
    protected function _getLoadSelect($field, $value, $object) {
        $select = parent::_getLoadSelect($field, $value, $object);

        if ($data = $object->getStoreId()) {
            $select->join(
                            array('store' => $this->getTable('qbanner/qqbanner_store')), $this->getMainTable() . '.qbanner_id = `store`.qbanner_id')
                    ->where('`store`.store_id in (0, ?) ', $data);
        }
        if ($data = $object->getPageId()) {
            $select->join(
                            array('page' => $this->getTable('qbanner/qbanner_page')), $this->getMainTable() . '.qbanner_id = `page`.qbanner_id')
                    ->where('`page`.page_id in (?) ', $data);
        }
        if ($data = $object->getCategoryId()) {
            $select->join(
                            array('category' => $this->getTable('qbanner/qbanner_category')), $this->getMainTable() . '.qbanner_id = `category`.qbanner_id')
                    ->where('`category`.category_id in (?) ', $data);
        }
        $select->order('title DESC')->limit(1);

        return $select;
    }

    /**
     * Call-back function
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object) {
        if (!$object->getIsMassStatus()) {
            $this->__saveToStoreTable($object);
            $this->__saveToPageTable($object);
            $this->__saveToCategoryTable($object);
            $this->__saveToImageTable($object);
        }

        return parent::_afterSave($object);
    }

    /**
     * Call-back function
     */
    protected function _beforeDelete(Mage_Core_Model_Abstract $object) {
        // Cleanup stats on blog delete
        $adapter = $this->_getReadAdapter();
        // 1. Delete blog/store
        $adapter->delete($this->getTable('qbanner/qbanner_store'), 'qbanner_id=' . $object->getId());
        // 2. Delete blog/post_cat
        $adapter->delete($this->getTable('qbanner/qbanner_page'), 'qbanner_id=' . $object->getId());
        // 3. Delete blog/post_comment
        $adapter->delete($this->getTable('qbanner/qbanner_category'), 'qbanner_id=' . $object->getId());
        // Update tags

        return parent::_beforeDelete($object);
    }

    /**
     * Load stores
     */
    private function __loadStore(Mage_Core_Model_Abstract $object) {
        $select = $this->_getReadAdapter()->select()
                ->from($this->getTable('qbanner/qbanner_store'))
                ->where('qbanner_id = ?', $object->getId());

        if ($data = $this->_getReadAdapter()->fetchAll($select)) {
            $array = array();
            foreach ($data as $row) {
                $array[] = $row['store_id'];
            }
            $object->setData('stores', $array);
        }
        return $object;
    }

    /**
     * Load pages
     */
    private function __loadPage(Mage_Core_Model_Abstract $object) {
        $select = $this->_getReadAdapter()->select()
                ->from($this->getTable('qbanner/qbanner_page'))
                ->where('qbanner_id = ?', $object->getId());

        if ($data = $this->_getReadAdapter()->fetchAll($select)) {
            $array = array();
            foreach ($data as $row) {
                $array[] = $row['page_id'];
            }
            $object->setData('pages', $array);
        }
        return $object;
    }

    /**
     * Load categories
     */
    private function __loadCategory(Mage_Core_Model_Abstract $object) {
        $select = $this->_getReadAdapter()->select()
                ->from($this->getTable('qbanner/qbanner_category'))
                ->where('qbanner_id = ?', $object->getId());

        if ($data = $this->_getReadAdapter()->fetchAll($select)) {
            $array = array();
            foreach ($data as $row) {
                $array[] = $row['category_id'];
            }
            $object->setData('category_id', $array);
        }
        return $object;
    }

    /**
     * Load images
     */
    private function __loadImage(Mage_Core_Model_Abstract $object) {
        $select = $this->_getReadAdapter()->select()
                ->from($this->getTable('qbanner/qbanner_image'))
                ->where('qbanner_id = ?', $object->getId())
                ->order(array('position ASC', 'image_id'));

        $object->setData('image', $this->_getReadAdapter()->fetchAll($select));
        return $object;
    }

    /**
     * Save stores
     */
    private function __saveToStoreTable(Mage_Core_Model_Abstract $object) {
        if (!$object->getData('stores')) {
            $condition = $this->_getWriteAdapter()->quoteInto('qbanner_id = ?', $object->getId());
            $this->_getWriteAdapter()->delete($this->getTable('qbanner/qbanner_store'), $condition);

            $storeArray = array(
                'qbanner_id' => $object->getId(),
                'store_id' => '0');
            $this->_getWriteAdapter()->insert(
                    $this->getTable('qbanner/qbanner_store'), $storeArray);
            return true;
        }

        $condition = $this->_getWriteAdapter()->quoteInto('qbanner_id = ?', $object->getId());
        $this->_getWriteAdapter()->delete($this->getTable('qbanner/qbanner_store'), $condition);
        foreach ((array) $object->getData('stores') as $store) {
            $storeArray = array();
            $storeArray['qbanner_id'] = $object->getId();
            $storeArray['store_id'] = $store;
            $this->_getWriteAdapter()->insert(
                    $this->getTable('qbanner/qbanner_store'), $storeArray);
        }
    }

    /**
     * Save stores
     */
    private function __saveToPageTable(Mage_Core_Model_Abstract $object) {
        if ($data = $object->getData('pages')) {
            $this->_getWriteAdapter()->beginTransaction();
            try {
                $condition = $this->_getWriteAdapter()->quoteInto('qbanner_id = ?', $object->getId());
                $this->_getWriteAdapter()->delete($this->getTable('qbanner/qbanner_page'), $condition);

                foreach ((array) $data as $page) {
                    $pageArray = array();
                    $pageArray['qbanner_id'] = $object->getId();
                    $pageArray['page_id'] = $page;
                    $this->_getWriteAdapter()->insert(
                            $this->getTable('qbanner/qbanner_page'), $pageArray);
                }
                $this->_getWriteAdapter()->commit();
            } catch (Exception $e) {
                $this->_getWriteAdapter()->rollBack();
                echo $e->getMessage();
            }
            return true;
        }

        $condition = $this->_getWriteAdapter()->quoteInto('qbanner_id = ?', $object->getId());
        $this->_getWriteAdapter()->delete($this->getTable('qbanner/qbanner_page'), $condition);
    }

    /**
     * Save categories
     */
    private function __saveToCategoryTable(Mage_Core_Model_Abstract $object) {
        if ($data = $object->getData('categories')) {
            $this->_getWriteAdapter()->beginTransaction();
            try {
                $condition = $this->_getWriteAdapter()->quoteInto('qbanner_id = ?', $object->getId());
                $this->_getWriteAdapter()->delete($this->getTable('qbanner/qbanner_category'), $condition);

                $data = array_unique($data);
                foreach ((array) $data as $category) {
                    $categoryArray = array();
                    $categoryArray['qbanner_id'] = $object->getId();
                    $categoryArray['category_id'] = $category;
                    $this->_getWriteAdapter()->insert(
                            $this->getTable('qbanner/qbanner_category'), $categoryArray);
                }
                $this->_getWriteAdapter()->commit();
            } catch (Exception $e) {
                $this->_getWriteAdapter()->rollBack();
                echo $e->getMessage();
            }
            return true;
        }

        $condition = $this->_getWriteAdapter()->quoteInto('qbanner_id = ?', $object->getId());
        $this->_getWriteAdapter()->delete($this->getTable('qbanner/qbanner_category'), $condition);
    }

    /**
     * Save stores
     */
    private function __saveToImageTable(Mage_Core_Model_Abstract $object) {
        if ($_imageList = $object->getData('images')) {
            $_imageList = Zend_Json::decode($_imageList);
            if (is_array($_imageList) and sizeof($_imageList) > 0) {
                $_imageTable = $this->getTable('qbanner/qbanner_image');
                $_adapter = $this->_getWriteAdapter();
                $_adapter->beginTransaction();
                try {
                    $condition = $this->_getWriteAdapter()->quoteInto('qbanner_id = ?', $object->getId());
                    $this->_getWriteAdapter()->delete($this->getTable('qbanner/qbanner_image'), $condition);

					
                    foreach ($_imageList as &$_item) {
                        if (isset($_item['removed']) and $_item['removed'] == '1') {
                            $_adapter->delete($_imageTable, $_adapter->quoteInto('image_id = ?', $_item['value_id'], 'INTEGER'));
                        } else {
                            $_data = array(
                                'label' => $_item['label'],
								'link' => $_item['link'],
                                'file' => $_item['file'],
                                'position' => $_item['position'],
                                'disabled' => $_item['disabled'],
                                'qbanner_id' => $object->getId());
                            $_adapter->insert($_imageTable, $_data);
                        }
                    }
                    $_adapter->commit();
                } catch (Exception $e) {
                    $_adapter->rollBack();
                    echo $e->getMessage();
                }
            }
        }
    }

}