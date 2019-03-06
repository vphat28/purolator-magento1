<?php
$installer = new Mage_Eav_Model_Entity_Setup('core_setup');

$installer->startSetup();

$attribute = new Mage_Eav_Model_Entity_Attribute();

$attributeCode = 'ready_to_ship';
$attribute->loadByCode('catalog_product', $attributeCode);

if($attribute->getAttributeId() == '') {

    $installer->addAttribute('catalog_product', $attributeCode, array(
        'group' => 'General',
        'type' => 'int',
        'backend' => '',
        'frontend' => '',
        'label' => 'Is the Item Ready to Ship?',
        'note' => 'Rate request will be performed as if this item is pre-boxes, it will not be boxed with other items.',
        'input' => 'boolean',
        'class' => '',
        'source' => 'eav/entity_attribute_source_table',
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'visible' => true,
        'required' => false,
        'user_defined' => true,
        'default' => '0',
        'visible_on_front' => false,
        'unique' => false,
        'is_configurable' => false,
        'used_for_promo_rules' => true
    ));

}

$installer->endSetup();
