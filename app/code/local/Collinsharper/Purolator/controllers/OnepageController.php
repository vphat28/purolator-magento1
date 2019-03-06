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
include_once("Mage/Checkout/controllers/OnepageController.php");

/**
 * Collinsharper_Purolator_OnepageController
 *
 * @category Shipping
 * @package  Collinsharper.Purolator
 * @author   Collins Harper  <ch@collinsharper.com>
 * @license  http://collinsharper.com Proprietary License
 * @link     http://collinsharper.com
 */
class Collinsharper_Purolator_OnepageController extends Mage_Checkout_OnepageController
{

    private $_address_correction;

    /**
     * Get address corrections
     *
     * @return String
     */
    public function getAddresscorrections()
    {
        return $this->_address_correction;
    }

    /**
     * Set address corrections
     *
     * @param type $val Address
     *
     * @return void
     */
    public function setAddresscorrections($val)
    {
        $this->_address_correction = $val;
    }

    /**
     * Runs a test on the address to display to the customer on the cart page or the shipping checkoutonepage
     *
     * @param type $address Address
     *
     * @return object
     */
    public function testAddress($address)
    {
        if (Mage::getStoreConfig('carriers/purolatormodule/active')) {

            $_address['City'] = $address->getCity();
            $_address['RegionCode'] = $address->getRegionCode();
            $_address['CountryId'] = $address->getCountryId();
            $_address['Postcode'] = $address->getPostcode();
            return Mage::getSingleton('purolatormodule/addressvalidation')->testAddress($_address);
        }
    }

    /**
     * Test Shipping Address
     *
     * @return mixed
     */
    public function testShippingAddressAction()
    {
        mage::log(__CLASS__ . __FUNCTION__);
        $_test = false;
        $this->_expireAjax();
        if ($this->getRequest()->isPost()) {
            $this->setAddresscorrections(0);

            $_test = $this->testAddress($this->getOnepage()->getQuote()->getShippingAddress());

            if ($_test !== false) {
                $this->setAddresscorrections($_test);
                $result['goto_section'] = 'shipping';

                if ($_shaddr = $this->getOnepage()->getQuote()->getShippingAddress()->getLastname()) {
                    $result['shipping_lastname'] = $_shaddr;
                    $result['shipping_firstname'] = $this->getOnepage()->getQuote()->getShippingAddress()->getFirstname();
                    $result['shipping_company'] = $this->getOnepage()->getQuote()->getShippingAddress()->getCompany();
                    $result['shipping_telephone'] = $this->getOnepage()->getQuote()->getShippingAddress()->getTelephone();
                    $result['shipping_street'] = $this->getOnepage()->getQuote()->getShippingAddress()->getStreet(1);
                    $result['shipping_street2'] = $this->getOnepage()->getQuote()->getShippingAddress()->getStreet(2);
                    $result['shipping_city'] = $this->getOnepage()->getQuote()->getShippingAddress()->getCity();
                    $result['shipping_region'] = $this->getOnepage()->getQuote()->getShippingAddress()->getRegionId();
                    $result['shipping_postcode'] = $this->getOnepage()->getQuote()->getShippingAddress()->getPostcode();
                    $result['shipping_country'] = $this->getOnepage()->getQuote()->getShippingAddress()->getCountryId();
                }
            }
            $result['message'] = $_test['message'];
            $result['addresses'] = '';
            if (is_array($_test['addresses']) && count($_test['addresses'])) {
                foreach ($_test['addresses'] as $add) {
                    $result['addresses'] .= '<a href="#" onClick="updateAddress(\'' . $add->City . '\',\'' . $add->Province . '\',\'' . $add->Country . '\',\'' . $add->PostalCode . '\'); return false" title="Update Address with Suggestion">' . $add->City . ', ' . $add->Province . ', ' . $add->Country . ', ' . $add->PostalCode . '</a><br />';
                }
            }

            mage::log("retuirn bits " . print_r($result, 1));
            mage::log("test bits " . print_r($_test, 1));
        }
        $this->getResponse()->setBody(Zend_Json::encode($result));
    }

    /**
     * save checkout billing address
     *
     * @return void
     */
    public function saveBillingAction()
    {
        // This will be done in the parent

        if (Mage::helper('purolatormodule')->isActive() && $this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost('billing', array());
            if ($data['use_for_shipping']) {
                mage::log(__FUNCTION__);
            }
        }
        parent::saveBillingAction();
    }

}
