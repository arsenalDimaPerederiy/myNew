<?php
/**
 * @category Webinse
 * @package Webinse_OAuth
 * @author Dmitriy Perederiy <perederiy1993@yandex.ua>
 */
/* @var $installer Mage_Customer_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$entityTypeId     = $installer->getEntityTypeId('customer'); // для товара catalog_product, для пользователя - customer, для адреса -customer_address и т д.
$attributeSetId   = $installer->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = $installer->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);


/*Add to customer client unique id attribute*/
$installer->addAttribute('customer', 'OAuth_id_client',  array(
    'type'     => 'varchar',
    'label'    => 'OAuth client id',
    'input'    => 'text',
    'global'   => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE, //можно использовать другие константы видимости
    'visible'           => true,
    'required'          => false,
    'user_defined'      => false,
    'default'           => ''
));

$installer->addAttributeToGroup(
    $entityTypeId,
    $attributeSetId,
    $attributeGroupId,
    'OAuth_id_client'
);

/*Add to customer unique id social network attribute*/
$installer->addAttribute('customer', 'OAuth_id_social',  array(
    'type'     => 'varchar',
    'label'    => 'OAuth client id',
    'input'    => 'text',
    'global'   => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE, //можно использовать другие константы видимости
    'visible'           => true,
    'required'          => false,
    'user_defined'      => false,
    'default'           => ''
));

$installer->addAttributeToGroup(
    $entityTypeId,
    $attributeSetId,
    $attributeGroupId,
    'OAuth_id_social'
);

$installer->endSetup();