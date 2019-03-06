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
 * Collinsharper_Purolator_Model_Source_Shipmentopt
 *
 * @category Shipping
 * @package  Collinsharper.Purolator
 * @author   Collins Harper  <ch@collinsharper.com>
 * @license  http://collinsharper.com Proprietary License
 * @link     http://collinsharper.com
 */
class Collinsharper_Purolator_Model_Source_Shipmentopt extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{

    const RESD = 'ResidentialSignatureDomestic';
//    const RESI = 'ResidentialSignatureIntl';
    const ORI = 'OriginSignatureNotRequired';

    /**
     * To options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $arr = array();
        $arr[] = array('value' => self::ORI, 'label' => 'Origin Signature Not Required');
        $arr[] = array('value' => self::RESD, 'label' => 'Residential Signature Domestic');
//        $arr[] = array('value' => self::RESI, 'label' => 'Residential Signature Intl');


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
     * Get options hash
     *
     * @return String
     */
    public function toOptionHash()
    {
        $source = $this->_getSource();
        return $source ? $source->toOptionHash() : array();
    }

}

