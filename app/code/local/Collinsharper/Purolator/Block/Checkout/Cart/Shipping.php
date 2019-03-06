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
 * Collinsharper_Purolator_Block_Checkout_Cart_Shipping
 *
 * @category Shipping
 * @package  Collinsharper.Purolator
 * @author   Collins Harper  <ch@collinsharper.com>
 * @license  http://collinsharper.com Proprietary License
 * @link     http://collinsharper.com
 */
class Collinsharper_Purolator_Block_Checkout_Cart_Shipping extends Mage_Checkout_Block_Cart_Shipping
{

    /**
     * Get City Active
     *
     * @return boolean
     */
    public function getCityActive()
    {
        return (bool) Mage::getStoreConfig('carriers/dhl/active') ||
            (bool) Mage::getStoreConfig('carriers/purolatormodule/active');
    }

    /**
     * Test address
     *
     * @return object
     */
    public function testAddress()
    {
        if (Mage::getStoreConfig('carriers/purolatormodule/active')) {
            $carrier = Mage::getSingleton('purolatormodule/addressvalidation');
            $_address['City'] = $this->getAddress()->getCity();
            $_address['RegionCode'] = $this->getAddress()->getRegionCode();
            $_address['CountryId'] = $this->getAddress()->getCountryId();
            $_address['Postcode'] = $this->getAddress()->getPostcode();

            return $carrier->testAddress($_address);
        }
    }

    /**
     * Test Address for Admin
     *
     * @param type $shippingAddress Address
     *
     * @return object
     */
    public function testAddressAdmin($shippingAddress)
    {
        if (Mage::getStoreConfig('carriers/purolatormodule/active')) {
            $carrier = Mage::getSingleton('purolatormodule/addressvalidation');
            $_address['City'] = $shippingAddress->getCity();
            $_address['RegionCode'] = $shippingAddress->getRegionCode();
            $_address['CountryId'] = $shippingAddress->getCountryId();
            $_address['Postcode'] = $shippingAddress->getPostcode();

            return $carrier->testAddress($_address);
        }
    }

}

