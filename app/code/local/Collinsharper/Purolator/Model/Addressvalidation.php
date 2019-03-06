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
 * Collinsharper_Purolator_Model_Addressvalidation
 *
 * @category Shipping
 * @package  Collinsharper.Purolator
 * @author   Collins Harper  <ch@collinsharper.com>
 * @license  http://collinsharper.com Proprietary License
 * @link     http://collinsharper.com
 */
class Collinsharper_Purolator_Model_Addressvalidation
{

    protected $_cr = false;
    protected $_cw = false;
    protected $_th = false;
    protected $_client = false;

    /**
     * Return helper
     *
     * @return object
     */
    public function getHelper()
    {
        return mage::Helper('purolatormodule');
    }

    /**
     * Return Shipping Module
     *
     * @return object
     */
    public function getShippingModule()
    {
        return Mage::getSingleton('purolatormodule/carrier_shippingmethod');
    }

    /**
     * Is active
     *
     * @return boolean
     */
    public function isActive()
    {
        return $this->getShippingModule()->isActive();
    }

    /**
     * Soap inteface
     *
     * @return object
     */
    private function getClient()
    {
        return Mage::getSingleton('purolatormodule/soapinterface')->getClient(Collinsharper_Purolator_Model_Soapinterface::AVALIABILITYSERVICE);
    }

    /**
     * Get Write connection
     *
     * @return object
     */
    private function getCw()
    {
        if (!$this->_cw) {
            $this->_cw = Mage::getSingleton('core/resource')->getConnection('core_write');
        }
        return $this->_cw;
    }

    /**
     * Get Read connection
     *
     * @return object
     */
    private function getCr()
    {
        if (!$this->_cr) {
            $this->_cr = Mage::getSingleton('core/resource')->getConnection('core_read');
        }
        return $this->_cr;
    }

    /**
     * Get Resource object
     *
     * @return object
     */
    private function getTh()
    {
        if (!$this->_th) {
            $this->_th = Mage::getSingleton('core/resource');
        }
        return $this->_th;
    }

    /**
     * Is test
     *
     * @return boolean
     */
    private function isTest()
    {
        return $this->getShippingModule()->isTest();
    }

    /**
     * Is debugging enabled
     *
     * @return boolean
     */
    private function isDebug()
    {
        return $this->getShippingModule()->isDebug();
    }

    /**
     * Is address validation active
     *
     * @return boolean
     */
    public function getAddressValidationActive()
    {
        return true;
    }

    /**
     * Get key
     *
     * @return String
     */
    private function getKey()
    {
        return $this->getShippingModule()->getConfig('accesskey');
    }

    /**
     * Get pass
     *
     * @return String
     */
    private function getPass()
    {
        return $this->getShippingModule()->getConfig('accesspassword');
    }

    /**
     * Magento log
     *
     * @param type $v     Log message     *
     * @param type $force override testing flags
     *
     * @return void
     *
     */
    private function log($v, $force = false)
    {
        if ($this->isTest() || $this->isDebug() || $force) {
            mage::log($v);
        }
    }

    /**
     * Test address
     *
     * @param type $address Addres
     *
     * @return boolean
     */
    public function testAddress($address)
    {
        // We need caching in here if the address has already been tested we shouldnt be making a call again
        if (!$this->getAddressValidationActive()) {
            return false;
        }
        if (!$address['City'] || !$address['Postcode'] || !$address['RegionCode']) {
            return false;
        }
        $request = new stdClass();
        $request->Addresses = new stdClass();
        $request->Addresses->ShortAddress = new stdClass();
        $request->Addresses->ShortAddress->City = $address['City'];
        $request->Addresses->ShortAddress->Province = $address['RegionCode'];
        $request->Addresses->ShortAddress->Country = $address['CountryId'];
        $request->Addresses->ShortAddress->PostalCode = str_replace(' ', '',$address['Postcode']);
        //Execute the request and capture the response
        try {
            $response = $this->getClient()->ValidateCityPostalCodeZip($request);
        } catch (Mage_Core_Exception $e) {
            mage::log(__CLASS__ . __FUNCTION__ . "exception");
            return false;
        } catch (Exception $e) {
            mage::log(__CLASS__ . __FUNCTION__ . "exception");
            return false;
        }

        if ($this->isTest()) {
            $this->log(__CLASS__ . " Request: " . print_r($request, 1));
            $this->log(__CLASS__ . " Response: " . print_r($response, 1));
        }
        if (!property_exists($response, 'ResponseInformation') || !property_exists($response->ResponseInformation, 'Errors')) {
            $this->log("Address validation failed with Purolator Request: " . print_r($request, 1) . " Respionse " . print_r($response, 1), true);
            return false;
        }

        if (property_exists($response->ResponseInformation->Errors, 'Error')) {
            $additional = '';
            $description = $this->getHelper()->__("Purolator Address Suggession");
            if (property_exists($response->ResponseInformation->Errors, 'Error') && property_exists($response->ResponseInformation->Errors->Error, 'Description')) {
                $description = (string) $response->ResponseInformation->Errors->Error->Description;
            }
            $ret = $description . "<br />\n";
            if (property_exists($response, 'SuggestedAddresses')) {

                $sugg = $response->SuggestedAddresses;

                if (!is_array($sugg) && property_exists($response->SuggestedAddresses, 'SuggestedAddress')) {
                    $sugg = array($response->SuggestedAddresses->SuggestedAddress);
                }
                $additional = '';
                $addresses = array();
                foreach ($sugg as $a) {
                    if (property_exists($a, 'Address') || (property_exists($a->ResponseInformation->Errors, 'Error') && $a->ResponseInformation->Errors->Error->Code == 1143)) {
                        $add = $a->Address;
                        $addresses[] = $add;
                    }
                    if (property_exists($a->ResponseInformation->Errors, 'Error') && property_exists($a->ResponseInformation->Errors->Error, 'Description')) {
                        $additional = $a->ResponseInformation->Errors->Error->Description;
                    }
                }
            } else {
                $ret = $a->ResponseInformation->Errors->Error->AdditionalInformation;
                $additional = $a->ResponseInformation->Errors->Error->Description;
            }
            return array('message' => $ret, 'addresses' => $addresses, 'description' => $additional);
        }

        return false;
    }

}

