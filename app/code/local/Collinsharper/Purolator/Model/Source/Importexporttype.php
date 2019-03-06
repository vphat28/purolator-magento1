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
 * Collinsharper_Purolator_Model_Source_Importexporttype
 *
 * @category Shipping
 * @package  Collinsharper.Purolator
 * @author   Collins Harper  <ch@collinsharper.com>
 * @license  http://collinsharper.com Proprietary License
 * @link     http://collinsharper.com
 */
class Collinsharper_Purolator_Model_Source_Importexporttype extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{

    const PERMANENT = 'Permanent';
    const TEMPORARY = 'Temporary';
    const REPAIR = 'Repair';
    const RTURN = 'Return';

    /**
     * To options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $arr = array();
        $arr[] = array('value' => self::PERMANENT, 'label' => 'Permanant');
        $arr[] = array('value' => self::TEMPORARY, 'label' => 'Temporary');
        $arr[] = array('value' => self::REPAIR, 'label' => 'Repair');
        $arr[] = array('value' => self::RTURN, 'label' => 'Return');

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
     * To options hash
     *
     * @return String
     */
    public function toOptionHash()
    {
        $source = $this->_getSource();
        return $source ? $source->toOptionHash() : array();
    }

}

