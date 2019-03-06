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
 * Collinsharper_Purolator_Model_Soapinterface
 *
 * @category Shipping
 * @package  Collinsharper.Purolator
 * @author   Collins Harper  <ch@collinsharper.com>
 * @license  http://collinsharper.com Proprietary License
 * @link     http://collinsharper.com
 */
class Collinsharper_Purolator_Model_Soapinterface
{
    // name of WSDL

    const AVALIABILITYSERVICE = 'ServiceAvailabilityService';
    const ESTIMATINGSERVICE = 'EstimatingService';
    const SHIPPINGSERVICE = 'ShippingService';
    const SHIPPINGDOCUMENTSERVICE = 'ShippingDocumentsService';

    protected $_code = 'purolatormodule';
    protected $_is_consolidate = false;
    protected $_cr = false;
    protected $_cw = false;
    protected $_th = false;
    protected $_clients = array();
    // actual path after dev / production host path
    protected $_locations = array(
        self::AVALIABILITYSERVICE => '/ServiceAvailability/ServiceAvailabilityService.asmx',
        self::ESTIMATINGSERVICE => '/Estimating/EstimatingService.asmx',
        self::SHIPPINGSERVICE => '/Shipping/ShippingService.asmx',
        self::SHIPPINGDOCUMENTSERVICE => 'ShippingDocuments/ShippingDocumentsService.asmx'
    );

    /**
     * Get helper
     *
     * @return object
     */
    public function getHelper()
    {
        return mage::Helper('purolatormodule');
    }

    /**
     * Get Shipping module
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
     * Validate type
     *
     * @param type $type Type
     *
     * @return boolean
     */
    private function validType($type)
    {
        return (bool) array_key_exists($type, $this->_locations);
    }

    /**
     * Get client
     *
     * @param type $type           client type
     * @param type $is_consolidate flag
     *
     * @return mixed
     */
    public function getClient($type, $is_consolidate = false)
    {
        if ($is_consolidate == true) {
            $this->_is_consolidate = true;
        } else {
            $this->_is_consolidate = false;
        }

        if (!$this->validType($type)) {
            Mage::throwException($this->getHelper()->__('Invalid Soap class type.'));
        }

        if (!isset($this->_clients[$type])) {
            $this->_clients[$type] = $this->createPWSSOAPClient($type);
        }
        return $this->_clients[$type];
    }

    /**
     * Get Writer object
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
     * Get Reader object
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
     * Get address validation status
     *
     * @return boolean
     */
    private function getAddressValidationActive()
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
     * Get User Token
     *
     * @return String
     */
    private function getUserToken()
    {
        return 'E19FB2A8-3CA4-4A3D-8C40-2C4B4CF44C3F';
    }

    /**
     * WSDL path
     *
     * @param type $type wsdl type
     *
     * @return String
     */
    private function getWsdlPath($type)
    {
        $path_parts = pathinfo(__FILE__);
        $pp = explode(DS, $path_parts['dirname']);
        array_pop($pp);

        $path = 'wsdl' . DS . ($this->isTest() ? 'Development' : 'Production');

        //$paths = implode(DS, $pp) . DS . $path . DS . $type . '.wsdl';
        $paths = Mage::getBaseDir('code').DS."local".DS."Collinsharper".DS."Purolator" . DS . $path . DS . $type . '.wsdl';

        return $paths;
    }

    /**
     * Log messages
     *
     * @param type $v     Log text
     *
     * @param type $force Override test flags
     *
     * @return void
     */
    private function log($v, $force = false)
    {
        if ($this->isTest() || $this->isDebug() || $force) {
            mage::log($v);
        }
    }

    /**
     * Get location - API URL
     *
     * @param type $type API TYPE
     *
     * @return String
     */
    private function getLocation($type)
    {
        $base = ($this->isTest() ?
                'https://devwebservices.purolator.com/EWS/V1/' : 'https://webservices.purolator.com/EWS/V1/' );
        return $base . $this->_locations[$type];
    }

    /**
     * API Client
     *
     * @param type $type API Type
     *
     * @return Object
     */
    private function createPWSSOAPClient($type)
    {
        /** Purpose : Creates a SOAP Client in Non-WSDL mode with the appropriate authentication and
         *           header information
         * */
        //Set the parameters for the Non-WSDL mode SOAP communication with your Development/Production credentials
        $this->_clients[$type] = new SoapClient(
            $this->getWsdlPath($type), array(
            'trace' => $this->isTest(),
            'location' => $this->getLocation($type),
            'uri' => "http://purolator.com/pws/datatypes/v1",
            'login' => $this->getKey(),
            'password' => $this->getPass()
            )
        );

        $version = $this->getVersion($type);
        $lang = "en";
        if (strtolower(Mage::getStoreConfig('general/country/default')) == "fr") {
            $lang = "fr";
        }
        //Define the SOAP Envelope Headers
        $headers[] = new SoapHeader(
            'http://purolator.com/pws/datatypes/v1', 'RequestContext', array(
            'Version' => $version,
            'Language' => $lang,
            'GroupID' => 'xxx',
            'RequestReference' => $type . ' Request',
			'UserToken' => $this->getUserToken()
            )
        );
        //Apply the SOAP Header to your client
        $this->_clients[$type]->__setSoapHeaders($headers);

        return $this->_clients[$type];
    }

    /**
     * Test the address
     *
     * @param type $address Address text
     *
     * @return boolean|string
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
        $this->log(__CLASS__ . __LINE__);
        if (!property_exists($response, 'ResponseInformation') || !property_exists($response->ResponseInformation, 'Errors')) {
            $this->log("Address validation failed with Purolator Request: " . print_r($request, 1) . " Respionse " . print_r($response, 1), true);
            return false;
        }
        $this->log(__CLASS__ . __LINE__);
        if (property_exists($response->ResponseInformation->Errors, 'Error')) {
            $this->log(__CLASS__ . __LINE__);
            $ret = $this->getHelper()->__($response->ResponseInformation->Errors->Error->Description) . "<br />\n";
            if (property_exists($response, 'SuggestedAddresses')) {
                $sugg = $response->SuggestedAddresses;
                if (!is_array($sugg) && property_exists($response->SuggestedAddresses, 'SuggestedAddress')) {
                    $sugg = array($response->SuggestedAddresses->SuggestedAddress);
                }
                foreach ($sugg as $a) {
                    $add = $a->Address;
                    $ret .= $add->City . " ";
                    $ret .= $add->Province . ", ";
                    $ret .= $add->Country . ", ";
                    $ret .= $add->PostalCode . "<br />\n";
                }
            }
            return $ret;
        }
        $this->log(__CLASS__ . __LINE__);
        return false;
    }

    /**
     * Version ID
     *
     * @param type $type API type
     *
     * @return string
     */
    protected function getVersion($type)
    {
        if ($type == 'ShippingDocumentsService') {
            return '1.2';
        }

        if ($type == 'ShippingService') {
			 if ($this->_is_consolidate) {
                return '1.5';
            }
            return '1.5';
        }

        if ($type == 'EstimatingService') {
            return '1.4';
        }

        if ($type == 'ServiceAvailabilityService') {
            return '1.3';
        }
    }

}
