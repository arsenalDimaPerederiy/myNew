<?php
/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

/**
 * Create table 'store_pickup/city'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('store_pickup/city'))
    ->addColumn('city_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'primary'  => true,
        'nullable' => false,
        'unsigned' => true,
    ), 'City ID')
    ->addColumn('name_ru', Varien_Db_Ddl_Table::TYPE_TEXT, Mage_Persistent_Model_Session::KEY_LENGTH, array(
        'nullable' => false,
    ), 'RU city name')
    ->addColumn('name_ua', Varien_Db_Ddl_Table::TYPE_TEXT, Mage_Persistent_Model_Session::KEY_LENGTH, array(
        'nullable' => false,
    ), 'UA city name')
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(), 'Updated At');
$installer->getConnection()->createTable($table);

/**
 * Create table 'store_pickup/warehouse'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('store_pickup/warehouse'))
    ->addColumn('warehouse_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'primary'  => true,
        'nullable' => false,
        'unsigned' => true,
    ), 'Warehouse ID')
    ->addColumn('city_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'primary'  => true,
        'nullable' => false,
        'unsigned' => true,
    ), 'City ID')
    ->addColumn('name_ru', Varien_Db_Ddl_Table::TYPE_TEXT, Mage_Persistent_Model_Session::KEY_LENGTH, array(
        'nullable' => false,
    ), 'RU city name')
    ->addColumn('name_ua', Varien_Db_Ddl_Table::TYPE_TEXT, Mage_Persistent_Model_Session::KEY_LENGTH, array(
        'nullable' => false,
    ), 'UA city name')
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(), 'Updated At')
    ->addForeignKey($installer->getFkName('store_pickup/warehouse', 'city_id', 'store_pickup/city', 'city_id'),
        'city_id', $installer->getTable('store_pickup/city'), 'city_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE);
$installer->getConnection()->createTable($table);

$installer->endSetup();
