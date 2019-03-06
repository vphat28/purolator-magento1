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
 * Collinsharper_Purolator_Model_Source_Weightunits
 *
 * @category Shipping
 * @package  Collinsharper.Purolator
 * @author   Collins Harper  <ch@collinsharper.com>
 * @license  http://collinsharper.com Proprietary License
 * @link     http://collinsharper.com
 */
class Collinsharper_Purolator_Model_Source_Weightunits extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{

    const KG = 'kg';
    const LB = 'lb';
    const OZ = 'oz';
    const GR = 'gr';

    /**
     * To options array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $arr = array();
        $arr[] = array('value' => self::KG, 'label' => 'KGs');
        $arr[] = array('value' => self::GR, 'label' => 'Grams');
        $arr[] = array('value' => self::LB, 'label' => 'LBs');
        $arr[] = array('value' => self::OZ, 'label' => 'Ozs');
        return $arr;
    }

    /**
     * Get all options array
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

