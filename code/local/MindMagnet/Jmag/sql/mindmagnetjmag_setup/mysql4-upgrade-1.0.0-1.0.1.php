<?php
/**
 * Customer Groups Price extension
 *
 * @category   MindMagnet
 * @package    MindMagnet_Jmag
 * @author     MindMagnet
 */

/** @var $this Mage_Catalog_Model_Resource_Setup */

$installer = $this;

$installer->startSetup();

$entityTypeId     = $installer->getEntityTypeId(Mage_Catalog_Model_Product::ENTITY);
$attributeSetId   = $installer->getAttributeSetId($entityTypeId, 'Produse');
$attributeGroupId = $installer->getAttributeGroupId($entityTypeId, $attributeSetId,'Jmag.ro Filtre');

$installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'gen', array(
    'type'              => 'int',
    'label'             => 'Jucarii pentru',
    'input'             => 'select',
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible'           => true,
    'required'          => false,
    'user_defined'      => false,
    'searchable'        => true,
    'filterable'        => true,
    'comparable'        => false,
    'visible_on_front'  => true,
    'unique'            => false,
));

$installer->addAttributeToGroup(
    $entityTypeId,
    $attributeSetId,
    $attributeGroupId,
    'gen',
    50
);

$installer->endSetup();
