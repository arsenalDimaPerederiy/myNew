<?php
/**
 * @category Webinse
 * @package Webinse_OAuth
 * @author Dmitriy Perederiy <perederiy1993@yandex.ua>
 */

$installer = new Mage_Core_Model_Resource_Setup();

$installer->startSetup();

$tableSocial = $installer->getTable('webinse_oauth/webinse_oauth');

$installer->getConnection()->dropTable($tableSocial);

$table = $installer->getConnection()
    ->newTable($tableSocial)
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array('unsigned' => true, 'identity' => true,'primary' => true),'entity id')
    ->addColumn('social_name', Varien_Db_Ddl_Table::TYPE_TEXT, null, array('nullable' => false),'social name')
    ->addColumn('user_social_id', Varien_Db_Ddl_Table::TYPE_TEXT, null, array('nullable' => false), 'Site Social id')
    ->addColumn('user_social_secret', Varien_Db_Ddl_Table::TYPE_TEXT, null, array('nullable' => false), 'Site social secret');

$installer->getConnection()->createTable($table);

$installer->endSetup();