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
 * Collinsharper_Purolator_Model_Shipment
 *
 * @category Shipping
 * @package  Collinsharper.Purolator
 * @author   Collins Harper  <ch@collinsharper.com>
 * @license  http://collinsharper.com Proprietary License
 * @link     http://collinsharper.com
 */
class Collinsharper_Purolator_Model_Cache extends Mage_Core_Model_Abstract
{

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('purolatormodule/cache');
    }

}
