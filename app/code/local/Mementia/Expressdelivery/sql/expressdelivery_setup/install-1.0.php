<?php
/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

/**
 * Create table 'persistent/session'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('expressdelivery/city'))
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

$installer->endSetup();
