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
include_once("Mage/Checkout/controllers/CartController.php");

/**
 * Collinsharper_Purolator_CartController
 *
 * @category Shipping
 * @package  Collinsharper.Purolator
 * @author   Collins Harper  <ch@collinsharper.com>
 * @license  http://collinsharper.com Proprietary License
 * @link     http://collinsharper.com
 */
class Collinsharper_Purolator_CartController extends Mage_Checkout_CartController
{

    /**
     * Initialize shipping information
     *
     * @return void
     */
    public function estimatePostAction()
    {
        $country = (string) $this->getRequest()->getParam('country_id');
        $postcode = (string) $this->getRequest()->getParam('estimate_postcode');
        $city = (string) $this->getRequest()->getParam('estimate_city');
        $regionId = (string) $this->getRequest()->getParam('region_id');
        $region = (string) $this->getRequest()->getParam('region');

        $this->_getQuote()->getShippingAddress()
            ->setCountryId($country)
            ->setCity($city)
            ->setPostcode($postcode)
            ->setRegionId($regionId)
            ->setRegion($region)
            ->setCollectShippingRates(true);
        $this->_getQuote()->save();
        $this->_goBack();
    }

}

