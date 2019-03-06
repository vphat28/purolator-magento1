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
 * Collinsharper_Purolator_Model_Source_Dimentionunits
 *
 * @category Shipping
 * @package  Collinsharper.Purolator
 * @author   Collins Harper  <ch@collinsharper.com>
 * @license  http://collinsharper.com Proprietary License
 * @link     http://collinsharper.com
 */
class Collinsharper_Purolator_Model_Source_Dimentionunits extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{

    const MM = 'mm';
    const CM = 'cm';
    const IN = 'in';
    const FT = 'ft';

    /**
     * To Options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $arr = array();
        $arr[] = array('value' => self::CM, 'label' => 'cms');
        $arr[] = array('value' => self::MM, 'label' => 'mms');
        $arr[] = array('value' => self::IN, 'label' => 'inches');
        $arr[] = array('value' => self::FT, 'label' => 'Feet');
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
     * Get option hash string
     *
     * @return String
     */
    public function toOptionHash()
    {
        $source = $this->_getSource();
        return $source ? $source->toOptionHash() : array();
    }

}

