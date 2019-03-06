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
 * Collinsharper_Purolator_Model_Source_Billduties
 *
 * @category Shipping
 * @package  Collinsharper.Purolator
 * @author   Collins Harper  <ch@collinsharper.com>
 * @license  http://collinsharper.com Proprietary License
 * @link     http://collinsharper.com
 */
class Collinsharper_Purolator_Model_Source_Billduties extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{

    const SENDER = 'Sender';
    const RECEIVER = 'Receiver';
    const BUYER = 'Buyer';

    /**
     * Returns Select element options set
     *
     * @return array options
     */
    public function toOptionArray()
    {
        $arr = array();
        $arr[] = array('value' => self::SENDER, 'label' => 'Sender');
        $arr[] = array('value' => self::RECEIVER, 'label' => 'Receiver');
        $arr[] = array('value' => self::BUYER, 'label' => 'Buyer');


        return $arr;
    }

    /**
     * Returns all possible options
     *
     * @return array options
     */
    public function getAllOptions()
    {
        return $this->toOptionArray();
    }

    /**
     * Returns an options hash set
     * 
     * @return String Hash
     */
    public function toOptionHash()
    {
        $source = $this->_getSource();
        return $source ? $source->toOptionHash() : array();
    }

}

