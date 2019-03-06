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

$sql = ' CREATE TABLE IF NOT EXISTS ' . $this->getTable('chpurolatormodule_cache') . ' (
 `chabfs_id` int(10) NOT NULL auto_increment,
`datestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
`md5_request` VARCHAR( 32 ) NOT NULL,
`xmlresponse` TEXT NOT NULL ,
PRIMARY KEY  (`chabfs_id`)
) ENGINE = MYISAM AUTO_INCREMENT=1';


$cw->query($sql);


// Get  entity model id 'sales/order'
$sql = 'SELECT entity_type_id FROM ' . $this->getTable('eav_entity_type') . ' WHERE entity_type_code="catalog_product"';
$row = $cr->fetchRow($sql);
//
//$attribute_model = Mage::getModel('eav/entity_attribute');
//$pattributes = array();
//$attribute_code = 'length';
//$attribute_id = $attribute_model->getIdByCode('catalog_product', $attribute_code);
//
//if (!isset($attribute_id) || !is_numeric($attribute_id) || $attribute_id == 0) {
//    // Create EAV-attribute for the order comment.
//    $c = array(
//        'entity_type_id' => $row['entity_type_id'],
//        'attribute_code' => $attribute_code,
//        'backend_type' => 'decimal', // MySQL-Datatype
//        'frontend_input' => 'text', // Type of the HTML form element
//        'is_global' => '1',
//        'is_visible' => '1',
//        'is_filterable' => '0',
//        'apply_to' => 'simple,configurable',
//        'is_visible_on_front' => 0,
//        'is_comparable' => '0',
//        'is_searchable' => '0',
//        'is_required' => '0',
//        'is_user_defined' => '0',
//        'frontend_label' => 'Length',
//        'note' => 'Length of shippable product - in cm',
//    );
//    $attribute = new Mage_Eav_Model_Entity_Attribute();
//    $attribute->loadByCode($c['entity_type_id'], $c['attribute_code'])
//        ->setStoreId(0)
//        ->addData($c);
//    $attribute->save();
//    $pattributes[] = $c['attribute_code'];
//}
//
//$attribute_code = 'width';
//$attribute_id = $attribute_model->getIdByCode('catalog_product', $attribute_code);
//
//if (!isset($attribute_id) || !is_numeric($attribute_id) || $attribute_id == 0) {
//    // Create EAV-attribute for the order comment.
//    $c = array(
//        'entity_type_id' => $row['entity_type_id'],
//        'attribute_code' => $attribute_code,
//        'backend_type' => 'decimal', // MySQL-Datatype
//        'frontend_input' => 'text', // Type of the HTML form element
//        'is_global' => '1',
//        'is_visible' => '1',
//        'is_filterable' => '0',
//        'apply_to' => 'simple,configurable',
//        'is_visible_on_front' => 0,
//        'is_comparable' => '0',
//        'is_searchable' => '0',
//        'is_required' => '0',
//        'is_user_defined' => '0',
//
//        'frontend_label' => 'Width',
//        'note' => 'Width of shippable product - in cm',
//    );
//    $attribute = new Mage_Eav_Model_Entity_Attribute();
//    $attribute->loadByCode($c['entity_type_id'], $c['attribute_code'])
//        ->setStoreId(0)
//        ->addData($c);
//    $attribute->save();
//    $pattributes[] = $c['attribute_code'];
//}
//
//
//$attribute_code = 'height';
//$attribute_id = $attribute_model->getIdByCode('catalog_product', $attribute_code);
//
//if (!isset($attribute_id) || !is_numeric($attribute_id) || $attribute_id == 0) {
//    // Create EAV-attribute for the order comment.
//    $c = array(
//        'entity_type_id' => $row['entity_type_id'],
//        'attribute_code' => $attribute_code,
//        'backend_type' => 'decimal', // MySQL-Datatype
//        'frontend_input' => 'text', // Type of the HTML form element
//        'is_global' => '1',
//        'is_visible' => '1',
//        'is_filterable' => '0',
//        'apply_to' => 'simple,configurable',
//        'is_visible_on_front' => 0,
//        'is_comparable' => '0',
//        'is_searchable' => '0',
//        'is_required' => '0',
//        'is_user_defined' => '0',
//
//        'frontend_label' => 'Height',
//        'note' => 'Height of shippable product - in cm',
//    );
//    $attribute = new Mage_Eav_Model_Entity_Attribute();
//    $attribute->loadByCode($c['entity_type_id'], $c['attribute_code'])
//        ->setStoreId(0)
//        ->addData($c);
//    $attribute->save();
//    $pattributes[] = $c['attribute_code'];
//}
//
//$attribute_code = 'weight_measure';
//$attribute_id = $attribute_model->getIdByCode('catalog_product', $attribute_code);
//
//if (!isset($attribute_id) || !is_numeric($attribute_id) || $attribute_id == 0) {
//    mage::log(__CLASS__ . " osdfdi " . Collinsharper_Purolator_Model_Source_Weightunits::KG);
//    // Create EAV-attribute for the order comment.
//    $c = array(
//        'entity_type_id' => $row['entity_type_id'],
//        'attribute_code' => $attribute_code,
//        'backend_type' => 'varchar', // MySQL-Datatype
//        'source_model' => 'Collinsharper_Purolator_Model_Source_Weightunits',
//        'frontend_input' => 'select', // Type of the HTML form element
//        'backend_table' => '',
//        'frontend_model' => '',
//        'is_global' => '1',
//        'is_visible' => '1',
//        'is_filterable' => '0',
//        'apply_to' => 'simple,configurable',
//        'is_visible_on_front' => '0',
//        'is_comparable' => '0',
//        'is_searchable' => '0',
//        'is_required' => '0',
//        'is_user_defined' => '0',
//
//        'frontend_label' => 'Weight Units',
//        'default_value' => Collinsharper_Purolator_Model_Source_Weightunits::KG,
//        'note' => 'Select the appropriate unit of measure',
//    );
//    $attribute = new Mage_Eav_Model_Entity_Attribute();
//    $attribute->loadByCode($c['entity_type_id'], $c['attribute_code'])
//        ->setStoreId(0)
//        ->addData($c);
//    $attribute->save();
//    $eav->updateAttribute('catalog_product', $attribute_code, 'source_model', 'Collinsharper_Purolator_Model_Source_Weightunits');
//    $pattributes[] = $c['attribute_code'];
//}

//$attribute_code = 'dimension_units';
//$attribute_id = $attribute_model->getIdByCode('catalog_product', $attribute_code);
//
//if (!isset($attribute_id) || !is_numeric($attribute_id) || $attribute_id == 0) {
//    mage::log(__CLASS__ . " osdfdi " . Collinsharper_Purolator_Model_Source_Weightunits::KG);
//    // Create EAV-attribute for the order comment.
//    $c = array(
//        'entity_type_id' => $row['entity_type_id'],
//        'attribute_code' => $attribute_code,
//        'backend_type' => 'varchar', // MySQL-Datatype
//        'source_model' => 'Collinsharper_Purolator_Model_Source_Dimentionunits',
//        'frontend_input' => 'select', // Type of the HTML form element
//        'backend_table' => '',
//        'frontend_model' => '',
//        'is_global' => '1',
//        'is_visible' => '1',
//        'is_filterable' => '0',
//        'apply_to' => 'simple,configurable',
//        'is_visible_on_front' => '0',
//        'is_comparable' => '0',
//        'is_searchable' => '0',
//        'is_required' => '0',
//        'is_user_defined' => '0',
//
//        'frontend_label' => 'Dimention Units',
//        'default_value' => Collinsharper_Purolator_Model_Source_Dimentionunits::CM,
//        'note' => 'Select the appropriate unit of measure',
//    );
//    $attribute = new Mage_Eav_Model_Entity_Attribute();
//    $attribute->loadByCode($c['entity_type_id'], $c['attribute_code'])
//        ->setStoreId(0)
//        ->addData($c);
//    $attribute->save();
//    $eav->updateAttribute('catalog_product', $attribute_code, 'source_model', 'Collinsharper_Purolator_Model_Source_Dimentionunits');
//    $pattributes[] = $c['attribute_code'];
//}


Mage::app('default');

$attrib_model_setup = $setup = new Mage_Eav_Model_Entity_Setup('core_setup'); //Mage::getModel('eav/entity_setup');
$entityTypeId = $row['entity_type_id'];
$attr_group = 'General';


$sets = $cr->fetchAll('select * from ' . $this->getTable('eav/attribute_set') . ' where entity_type_id=?', $row['entity_type_id']);

foreach ($sets as $set) {
    foreach ($pattributes as $attributeCode) {
        $attrib_model_setup->addAttributeToSet($entityTypeId, $set['attribute_set_id'], $attr_group, $attributeCode);
    }
}


$installer->endSetup();

?>