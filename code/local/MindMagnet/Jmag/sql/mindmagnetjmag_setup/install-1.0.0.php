<?php
/** @var $installer Mage_Catalog_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->addAttributeGroup(Mage_Catalog_Model_Product::ENTITY, 'Produse', 'Jmag.ro Filtre', 1);

$entityTypeId     = $installer->getEntityTypeId(Mage_Catalog_Model_Product::ENTITY);
$attributeSetId   = $installer->getAttributeSetId($entityTypeId, 'Produse');
$attributeGroupId = $installer->getAttributeGroupId($entityTypeId, $attributeSetId,'Jmag.ro Filtre');

$installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'abilitati_jmag', array(
    'type'              => 'varchar',
    'label'             => 'Abilitati',
    'backend'           => 'eav/entity_attribute_backend_array',
    'input'             => 'multiselect',
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

$installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'product_tag', array(
    'type'              => 'varchar',
    'label'             => 'Tag Produs',
    'backend'           => 'eav/entity_attribute_backend_array',
    'input'             => 'multiselect',
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

$installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'varste', array(
    'type'              => 'varchar',
    'label'             => 'Varste',
    'backend'           => 'eav/entity_attribute_backend_array',
    'input'             => 'multiselect',
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

$installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'producatori', array(
    'type'              => 'int',
    'label'             => 'Producator',
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

// update attributes group and sort
$attributes = array(
    'abilitati_jmag'          => array(
        'sort'  => 10
    ),
    'product_tag'          => array(
        'sort'  => 20
    ),
    'varste'           => array(
        'sort'  => 30
    ),
    'producatori'         => array(
        'sort'  => 40
    )
);

foreach ($attributes as $attributeCode => $attributeProp) {
    $installer->addAttributeToGroup(
        $entityTypeId,
        $attributeSetId,
        $attributeGroupId,
        $attributeCode,
        $attributeProp['sort']
    );
}

$installer->endSetup();
