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

$this->startSetup();

$conn = $this->getConnection();

try {
    if ($conn->isTableExists('ch_purolator_manifest')) {
        $conn->renameTable('ch_purolator_manifest', $this->getTable('ch_purolator_manifest'));
    }

    if ($conn->isTableExists('ch_purolator_shipment')) {
        $conn->renameTable('ch_purolator_shipment', $this->getTable('ch_purolator_shipment'));
    }
} catch (Exception $e) {
    Mage::logException($e);
}

$this->endSetup();
