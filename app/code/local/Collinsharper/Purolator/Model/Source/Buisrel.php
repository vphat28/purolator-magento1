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
 * Collinsharper_Purolator_Model_Source_Buisrel
 *
 * @category Shipping
 * @package  Collinsharper.Purolator
 * @author   Collins Harper  <ch@collinsharper.com>
 * @license  http://collinsharper.com Proprietary License
 * @link     http://collinsharper.com
 */
class Collinsharper_Purolator_Model_Source_Buisrel extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{

    const RELATED     = 'Related';
    const NOT_RELATED = 'NotRelated';

    /**
     * Returns an array of options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $arr   = array();
        $arr[] = array('value' => self::RELATED, 'label' => 'Related');
        $arr[] = array('value' => self::NOT_RELATED, 'label' => 'Not Related');

        return $arr;
    }

    /**
     * Returns an array of options
     *
     * @return array
     */
    public function getAllOptions()
    {
        return $this->toOptionArray();
    }

    /**
     * Returns an hash string
     *
     * @return String
     */
    public function toOptionHash()
    {
        $source = $this->_getSource();

        return $source ? $source->toOptionHash() : array();
    }

}

