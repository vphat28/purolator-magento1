<?php

/**
 * Collinsharper Purolator Module
 *
 * PHP version 5
 *
 * @category Shipping
 * @package  Collinsharper.Purolator
 * @author   Collins Harper  <ch@collinsharper.com>
 * @license  http://collinsharper.com Proprietary License
 * @link     http://collinsharper.com
 */

/**
 * Installer
 *
 * @category Shipping
 * @package  Collinsharper.Purolator
 * @author   Collins Harper  <ch@collinsharper.com>
 * @license  http://collinsharper.com Proprietary License
 * @link     http://collinsharper.com
 */

$installer = $this;
$installer->startSetup();

$eav = new Mage_Eav_Model_Entity_Setup('sales_setup');
$cw = Mage::getSingleton('core/resource')
    ->getConnection('core_write');
$cr = Mage::getSingleton('core/resource')
    ->getConnection('core_read');

$ch_manifest = "CREATE TABLE IF NOT EXISTS `ch_purolator_manifest` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shipment_manifest_date` varchar(256) DEFAULT NULL,
  `manifest_close_date` varchar(256) DEFAULT NULL,
  `status` varchar(128) DEFAULT 'pending',
  `document_type` varchar(256) DEFAULT NULL,
  `description` varchar(256) DEFAULT NULL,
  `url` varchar(512) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1";

$ch_shipment = "CREATE TABLE IF NOT EXISTS `ch_purolator_shipment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shipment_pin` varchar(1024) DEFAULT NULL,
  `piece_pin` varchar(1024) DEFAULT NULL,
  `return_shipment_pin` varchar(1024) DEFAULT NULL,
  `express_shipment_pin` varchar(1024) DEFAULT NULL,
  `magento_shipment_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1
";

$cw->query($ch_manifest);

$cw->query($ch_shipment);

/*
 *  Adding the attributes ...
 */
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$setup->addAttributeGroup('catalog_product', 'Default', 'Purolator Attributes', 1001);

if (!$setup->getAttributeId('catalog_product', 'harmonizedcode_attribute')) {
    $setup->addAttribute(
        'catalog_product', 'harmonizedcode_attribute', array(
        'group' => 'Purolator Attributes',
        'input' => 'text',
        'type' => 'text',
        'label' => 'Harmonized Code',
        'backend' => '',
        'visible' => 1,
        'required' => 0,
        'user_defined' => 1,
        'searchable' => 1,
        'filterable' => 1,
        'comparable' => 1,
        'visible_on_front' => 1,
        'visible_in_advanced_search' => 0,
        'is_html_allowed_on_front' => 0,
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    )
    );
}

if (!$setup->getAttributeId('catalog_product', 'nafta_attribute')) {
    $setup->addAttribute(
        'catalog_product', 'nafta_attribute', array(
        'group' => 'Purolator Attributes',
        'input' => 'select',
        'type' => 'text',
        'label' => 'NAFTA Document Indicator',
        'source' => 'eav/entity_attribute_source_boolean',
        'backend' => '',
        'visible' => 1,
        'required' => 0,
        'user_defined' => 1,
        'searchable' => 1,
        'filterable' => 1,
        'comparable' => 1,
        'visible_on_front' => 1,
        'visible_in_advanced_search' => 0,
        'is_html_allowed_on_front' => 0,
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    )
    );
}
if (!$setup->getAttributeId('catalog_product', 'textileindicator_attribute')) {
    $setup->addAttribute(
        'catalog_product', 'textileindicator_attribute', array(
        'group' => 'Purolator Attributes',
        'input' => 'select',
        'type' => 'text',
        'label' => 'Textile Indicator',
        'source' => 'eav/entity_attribute_source_boolean',
        'backend' => '',
        'visible' => 1,
        'required' => 0,
        'user_defined' => 1,
        'searchable' => 1,
        'filterable' => 1,
        'comparable' => 1,
        'visible_on_front' => 1,
        'visible_in_advanced_search' => 0,
        'is_html_allowed_on_front' => 0,
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    )
    );
}
if (!$setup->getAttributeId('catalog_product', 'textileman_attribute')) {
    $setup->addAttribute(
        'catalog_product', 'textileman_attribute', array(
        'group' => 'Purolator Attributes',
        'input' => 'text',
        'type' => 'text',
        'label' => 'Textile Manufacturer',
        'backend' => '',
        'visible' => 1,
        'required' => 0,
        'user_defined' => 1,
        'searchable' => 1,
        'filterable' => 1,
        'comparable' => 1,
        'visible_on_front' => 1,
        'visible_in_advanced_search' => 0,
        'is_html_allowed_on_front' => 0,
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    )
    );
}
if (!$setup->getAttributeId('catalog_product', 'fcc_attribute')) {
    $setup->addAttribute(
        'catalog_product', 'fcc_attribute', array(
        'group' => 'Purolator Attributes',
        'input' => 'select',
        'type' => 'text',
        'label' => 'FCC Document Indicator',
        'source' => 'eav/entity_attribute_source_boolean',
        'backend' => '',
        'visible' => 1,
        'required' => 0,
        'user_defined' => 1,
        'searchable' => 1,
        'filterable' => 1,
        'comparable' => 1,
        'visible_on_front' => 1,
        'visible_in_advanced_search' => 0,
        'is_html_allowed_on_front' => 0,
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    )
    );
}
if (!$setup->getAttributeId('catalog_product', 'sip_attribute')) {
    $setup->addAttribute(
        'catalog_product', 'sip_attribute', array(
        'group' => 'Purolator Attributes',
        'input' => 'select',
        'type' => 'text',
        'label' => 'Sender Is Producer Indicator',
        'source' => 'eav/entity_attribute_source_boolean',
        'backend' => '',
        'visible' => 1,
        'required' => 0,
        'user_defined' => 1,
        'searchable' => 1,
        'filterable' => 1,
        'comparable' => 1,
        'visible_on_front' => 1,
        'visible_in_advanced_search' => 0,
        'is_html_allowed_on_front' => 0,
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    )
    );
}

if (!$setup->getAttributeId('catalog_product', 'item_width')) {
    $setup->addAttribute(
        'catalog_product', 'item_width', array(
        'group' => 'Purolator Attributes',
        'input' => 'text',
        'type' => 'text',
        'label' => 'Item Width (cm)',
        'backend' => '',
        'visible' => 1,
        'required' => 0,
        'user_defined' => 1,
        'searchable' => 1,
        'filterable' => 1,
        'comparable' => 1,
        'visible_on_front' => 1,
        'visible_in_advanced_search' => 0,
        'is_html_allowed_on_front' => 0,
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    )
    );
}
if (!$setup->getAttributeId('catalog_product', 'item_height')) {
    $setup->addAttribute(
        'catalog_product', 'item_height', array(
        'group' => 'Purolator Attributes',
        'input' => 'text',
        'type' => 'text',
        'label' => 'Item Height (cm)',
        'backend' => '',
        'visible' => 1,
        'required' => 0,
        'user_defined' => 1,
        'searchable' => 1,
        'filterable' => 1,
        'comparable' => 1,
        'visible_on_front' => 1,
        'visible_in_advanced_search' => 0,
        'is_html_allowed_on_front' => 0,
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    )
    );
}

if (!$setup->getAttributeId('catalog_product', 'item_length')) {
    $setup->addAttribute(
        'catalog_product', 'item_length', array(
        'group' => 'Purolator Attributes',
        'input' => 'text',
        'type' => 'text',
        'label' => 'Item Length (cm)',
        'backend' => '',
        'visible' => 1,
        'required' => 0,
        'user_defined' => 1,
        'searchable' => 1,
        'filterable' => 1,
        'comparable' => 1,
        'visible_on_front' => 1,
        'visible_in_advanced_search' => 0,
        'is_html_allowed_on_front' => 0,
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    )
    );
}

$installer->endSetup();
