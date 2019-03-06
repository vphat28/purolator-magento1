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
 * Collinsharper_Purolator_Model_Source_Dutyccy
 *
 * @category Shipping
 * @package  Collinsharper.Purolator
 * @author   Collins Harper  <ch@collinsharper.com>
 * @license  http://collinsharper.com Proprietary License
 * @link     http://collinsharper.com
 */
class Collinsharper_Purolator_Model_Source_Dutyccy extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{

    const USD = 'USD';
    const CAD = 'CAD';

    /**
     * To option
     *
     * @return void
     */
    public function toOptionArray()
    {
        $arr = array();
        $arr[] = array('value' => self::USD, 'label' => 'USD');
        $arr[] = array('value' => self::CAD, 'label' => 'CAD');

        return $arr;
    }

    /**
     * Get all options
     *
     * @return array
     */
    public function getAllOptions()
    {
        return $this->toOptionArray();
    }

    /**
     * To option hash String
     * 
     * @return String
     */
    public function toOptionHash()
    {
        $source = $this->_getSource();
        return $source ? $source->toOptionHash() : array();
    }

}

