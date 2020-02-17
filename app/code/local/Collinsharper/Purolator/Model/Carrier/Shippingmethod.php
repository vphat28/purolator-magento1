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
 * Collinsharper_Purolator_Model_Carrier_Shippingmethod
 *
 * @category Shipping
 * @package  Collinsharper.Purolator
 * @author   Collins Harper  <ch@collinsharper.com>
 * @license  http://collinsharper.com Proprietary License
 * @link     http://collinsharper.com
 */
class Collinsharper_Purolator_Model_Carrier_Shippingmethod extends Mage_Shipping_Model_Carrier_Abstract
{
    const TEST_ACCOUNT_NUMBER = '9999999999';
    const TEST_REGISTERED_ACCOUNT_NUMBER = '9999999999';

    const COLLINSHARPER_ACCESS_KEY = '75db2bdc474547b7a91cdcc54e1b3465';
    const COLLINSHARPER_ACCESS_PASSWORD = 'dOnTYSqD';

    const TEST_COLLINSHARPER_ACCESS_KEY = 'a93117b3ced548858d29ad8438a6bc8b';
    const TEST_COLLINSHARPER_ACCESS_PASSWORD = 'Mc+M.1}W';
    const TEST_USER_TOKEN = '3fa7debc-e2af-4f03-b2de-5589c8f8b2e3';


    protected $_code = 'purolatormodule';
    protected $_cr = false;
    protected $_cw = false;
    protected $_th = false;
    protected $_user_message = false;

    /**
     * Is city required
     *
     * @return boolean
     */
    public function isCityRequired()
    {
        return true;
    }

    /**
     * is zip code required
     *
     * @param type $countryId Country id
     *
     * @return boolean
     */
    public function isZipCodeRequired($countryId = null)
    {
        return true;
    }

    /**
     * Get config value
     *
     * @param type $key config key
     *
     * @return string
     */
    public function getConfig($key)
    {
        if ($key === 'accesskey') {
            if ($this->isTest()) {
                return self::TEST_COLLINSHARPER_ACCESS_KEY;
            } else {
                return self::COLLINSHARPER_ACCESS_KEY;
            }
        }

        if ($key === 'accesspassword') {
            if ($this->isTest()) {
                return self::TEST_COLLINSHARPER_ACCESS_PASSWORD;
            } else {
                return self::COLLINSHARPER_ACCESS_PASSWORD;
            }
        }

        if ($key === 'activationkey') {
            if ($this->isTest()) {
                return self::TEST_USER_TOKEN;
            }
        }

        return Mage::getStoreConfig('carriers/' . $this->_code . '/' . $key);
    }

    /**
     * Get API client
     *
     * @return type
     */
    private function getClient()
    {
        return Mage::getSingleton('purolatormodule/soapinterface')->getClient(Collinsharper_Purolator_Model_Soapinterface::ESTIMATINGSERVICE);
    }

    /**
     * Test Mode enabled ?
     *
     * @return boolean
     */
    public function isTest()
    {
        return $this->getConfig('testing') == 1;
    }

    /**
     * Debugging enabled ?
     *
     * @return boolean
     */
    public function isDebug()
    {
        return $this->getConfig('debug') == 1;
    }

    /**
     * State Province required ?
     *
     * @return boolean
     */
    public function isStateProvinceRequired()
    {
        return true;
    }

    /**
     * Is the Module enabled
     *
     * @return boolean
     */
    public function isActive()
    {
        return Mage::helper('purolatormodule')->isActive();
    }

    /**
     * Get the address validation model
     *
     * @return Object
     */
    public function getAddressValidation()
    {
        return Mage::getSingleton('purolatormodule/addressvalidation');
    }

    /**
     * Get Write Connection
     *
     * @return Object
     */
    private function getCw()
    {
        if (!$this->_cw) {
            $this->_cw = Mage::getSingleton('core/resource')->getConnection('core_write');
        }
        return $this->_cw;
    }

    /**
     * Get Read Connection
     *
     * @return Object
     */
    private function getCr()
    {
        if (!$this->_cr) {
            $this->_cr = Mage::getSingleton('core/resource')->getConnection('core_read');
        }
        return $this->_cr;
    }

    /**
     * Get resuouce model
     *
     * @return Object
     */
    private function getTh()
    {
        if (!$this->_th) {
            $this->_th = Mage::getSingleton('core/resource');
        }
        return $this->_th;
    }

    /**
     * Get Method Source model
     *
     * @return Object
     */
    private function getMethodSource()
    {
        return mage::getSingleton('purolatormodule/source_method');
    }

    /**
     * Collect Rates
     *
     * @param Mage_Shipping_Model_Rate_Request $request Request Object
     *
     * @return boolean
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        //No multishipping support
        if (Mage::app()->getRequest()->getControllerName() == "multishipping") {
            return false;
        }
        if ($this->getConfigFlag('hide_frontend')) {
            if(Mage::getSingleton('admin/session')->isLoggedIn()){
                //do stuff
            }else{
                return false;
            }
        }

        $request->setDestPostcode(preg_replace('/[\s]+/','',$request->getDestPostcode()));

        return $this->collectionRatesWithFullEstimate($request);
    }

    /**
     * Full Estimate API Call
     *
     * @param type $request Request object
     *
     * @return boolean
     */
    protected function collectionRatesWithFullEstimate($request)
    {
        if (!$this->isActive()) {
            return false;
        }
        $service_rules = $this->getServiceRules($request);

        $result = Mage::getModel('shipping/rate_result');

        if ($this->getAddressValidation()->getAddressValidationActive()) {
            $address = array();
            $address['City'] = $request->getDestCity();
            $address['RegionCode'] = $request->getDestRegionCode();
            $address['CountryId'] = $request->getDestCountryId();
            $address['Postcode'] = $request->getDestPostcode();
            $tmp = $this->getAddressValidation()->testAddress($address);
            if ($tmp) {
                $this->log(__CLASS__ . "Failed address validation ");
                $error = Mage::getModel('purolatormodule/rate_result_error');
                $error->setCarrier($this->_code);
                $error->setCarrierTitle($this->getConfigData('title'));
                $error->setErrorMessage($tmp['description']);
                $result->append($error);
                return false;
            }
        }


        $freeBoxes = 0;
        foreach ($request->getAllItems() as $item) {

            if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                continue;
            }
            if ($item->getHasChildren() && $item->isShipSeparately()) {
                foreach ($item->getChildren() as $child) {
                    if ($child->getFreeShipping() && !$child->getProduct()->isVirtual()) {
                        $freeBoxes += $item->getQty() * $child->getQty();
                    }
                }
            } elseif ($item->getFreeShipping()) {
                $freeBoxes += $item->getQty();
            }
        }

        $this->setFreeBoxes($freeBoxes);
        $weight = $this->getTotalNumOfBoxes($request->getPackageWeight());
        $weight = ceil($weight * 10) / 10;
        $_results = false;

        $_items = array();

        $_bits = $this->_makeFullRequest($request, $_items);

        $this->log(__METHOD__ . " Purolator XML REquest" . print_r($_bits, 1));
        $_results = $this->_cachedBits($_bits);

        if (!$_results) {
            $_results = $this->_postBits($_bits);
            // NEED ERROR CHECK HERE SO WE DONT CACHE BAD RESPONSE
            if (!$this->_user_message) {
                $this->_makeCache($_results, $_bits);
            }

            $this->log(__METHOD__ . " Purolator XML RESPONSE " . print_r($_results, 1));
        }

        //validate the service rules
        $_results = $this->validateServiceRules($_results, $service_rules, $request, $_bits);

        $handling = 0;
        if ($this->getConfig('handling') > 0) {
            $handling = $this->getConfig('handling');
        }

//        if ($this->getConfig('handling_type') == 'P' && $request->getPackageValue() > 0) {
//            $handling = $request->getPackageValue() * $handling;
//        }
        $this->log(__CLASS__ . __FILE__ . " results: " . print_r($_results, 1));
        $bad = (!is_object($_results) && !is_array($_results)) || ((bool)(!is_array($_results)) && (property_exists($_results->ResponseInformation, 'Errors') && property_exists($_results->ResponseInformation, 'Error')));
        if ($this->_user_message !== false || !count($_results) || is_object($_results) || $_results === false || $bad) {
            if ($this->_user_message !== false) {
                $this->log(__METHOD__ . " Purolator XML ERROR " . print_r($this->_user_message, 1));
                $error = Mage::getModel('purolatormodule/rate_result_error');
                $error->setCarrier($this->_code);
                $error->setCarrierTitle($this->getConfigData('title'));
                $error->setErrorMessage($this->_user_message);
                $result->append($error);
            } else {
                if ($this->getConfig('handling_type') == 'P' && $request->getPackageValue() > 0) {
                    $handling = round(($this->getConfig('failover_rate')/100) * $handling, 2);
                }
                if ($this->getConfig('failover_rate') > 0) {
                    $method = Mage::getModel('shipping/rate_result_method');
                    $method->setCarrier($this->_code);
                    $method->setCarrierTitle($this->getConfig('title'));
                    $method->setMethod('Regular');
                    $method->setMethodTitle($this->getConfig('failover_ratetitle'));
                    $method->setCost($this->getConfig('failover_rate')+$handling);
                    $method->setPrice($this->getConfig('failover_rate')+$handling);
                    if (!empty($_results)) {
                        $method->setBadAddress($_results->ResponseInformation->Errors->Error->Description);
                    } else {
                        $method->setBadAddress("No responses");
                    }

                    $result->append($method);
                } else {
                    $error = Mage::getModel('purolatormodule/rate_result_error');
                    $error->setCarrier($this->_code);
                    $error->setCarrierTitle($this->getConfigData('title'));
                    $error->setErrorMessage($_results->ResponseInformation->Errors->Error->Description);
                    $result->append($error);
                }
            }
            return $result;
        }

        $allowed_methods = explode(',', $this->getConfigData('allowed_methods'));

        foreach ($_results as $prod) {
            $handling = 0;
            if ($this->getConfig('handling') > 0) {
                $handling = $this->getConfig('handling');
            }
            if (!in_array($prod['service'], $allowed_methods)) {
                $this->log(__CLASS__ . __FUNCTION__ . " skipping as not in allowed " . $prod['service']);
                continue;
            }

            $_method_title = $this->getMethodSource()->getMethodTitle($prod['service']);
            if (strlen($prod['deliveryDate'])) {
                $newdate = strtotime($prod['deliveryDate']);
                if ($this->getConfig('additionaldays') > 0) {
                    $newdate = strtotime('+' . $this->getConfig('additionaldays') . ' day', $newdate);
                    if (strtoupper(date("D", $newdate)) == "SAT") {
                        $newdate = strtotime('+2 day', $newdate);
                    }
                    if (strtoupper(date("D", $newdate)) == "SUN") {
                        $newdate = strtotime('+1 day', $newdate);
                    }
                }
                $newdate = Mage::helper('purolatormodule')->formatDate(date('Y-m-d', $newdate));
                $_method_title = Mage::helper('purolatormodule')->__('%s - Est. Delivery %s', $_method_title, $newdate);
            }
            $this->log(__FUNCTION__ . __LINE__ . " " . $_method_title);
            $_realprice = $_price = $prod['rate'];
            if ((int)$this->getConfig('markupval') > 0) {
                $_price = ((int)$this->getConfig('markupval') * .01) * $prod['rate'];
            }

//            if ($request->getFreeShipping() === true){// || $request->getPackageQty() == $this->getFreeBoxes()) {
//                $_realprice = '0.00';
//            }
            if ($this->getConfig('handling_type') == 'P') {
                $this->log($handling);
                $handling = round(($_realprice/100) * $handling, 2);
            }

            $method = Mage::getModel('shipping/rate_result_method');
            $method->setCarrier($this->_code);
            $method->setCarrierTitle($this->getConfig('title'));
            $method->setMethod($prod['service']);
            $method->setMethodTitle($_method_title);
            $method->setCost($prod['rate']+$handling);
            $method->setPrice($_realprice+$handling);
            $method = $this->applyFreeShipping($method);
            $result->append($method);
        }
        return $result;
    }

    /**
     * Cache bits for faster rate collection
     *
     * @param type $xml_bits Cache bits
     *
     * @return boolean|int
     */
    public function _cachedBits($xml_bits)
    {
        if (!$this->getConfig('usecache')) {
            return false;
        }

        $resp = 0;
        $sql = 'select xmlresponse from  ' . $this->getTh()->getTableName('chpurolatormodule_cache') .
            ' WHERE md5_request = "' . md5(serialize($xml_bits)) . '" and xmlresponse != "" limit 1';
        $res = $this->getCr()->fetchRow($sql);

        if (isset($res['xmlresponse']) && strlen($res['xmlresponse']) > 10) {
            return unserialize($res['xmlresponse']);
        }
        return $resp;
    }

    /**
     * Save the cache
     *
     * @param type $xml_results cached result
     * @param type $xml_bits    cached request
     *
     * @return boolean
     */
    public function _makeCache($xml_results, $xml_bits)
    {
        $cacheModel = Mage::getModel('purolatormodule/cache');
        if (!$this->getConfig('usecache')) {
            return false;
        }
        if ((int)rand() % 3) {
            $sql = 'delete from ' . $this->getTh()->getTableName('chpurolatormodule_cache') . ' where to_days(datestamp) < (to_days(now())-1)';
            $this->getCw()->query($sql);
        }
        if (count($xml_results) == 0) {
            return true;
        }

        $cacheModel->setData('datestamp', now())
            ->setData('md5_request', md5(serialize($xml_bits)))
            ->setData('xmlresponse', serialize($xml_results))
            ->save();

        return true;
    }

    /**
     * Logging
     *
     * @param type $v     content
     * @param type $force fored
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
     * Return helper
     *
     * @return Object
     */
    public function getHelper()
    {
        return mage::Helper('purolatormodule');
    }

    /**
     * Get Estimate's
     *
     * @param type $estimate Estimate Response object
     *
     * @return type
     */
    private function getEstimateRate($estimate)
    {
        return $estimate->TotalPrice;
        if (0) {
            return $estimate->BasePrice;
        }
    }

    /**
     * Run the api call's
     *
     * @param type $bits Request Object
     *
     * @return boolean
     */
    public function _postBits($bits)
    {
        $results = array();

        if ($this->isTest()) {
            $this->log(__FUNCTION__ . __LINE__ . print_r($bits, 1));
        }
        try {

            $response = $this->getClient()->GetFullEstimate($bits);
            if ($this->isDebug()) {
                $this->log($bits);
                $this->log($response);
            }

        } catch (Exception $e) {

            $this->log(__CLASS__ . __FUNCTION__ . "exception");
            $this->log(__CLASS__ . __FUNCTION__ . " " . $e->getMessage());
            return false;
        }
        if ($this->isTest()) {
            $this->log(__FUNCTION__ . __LINE__ . print_r($response, 1));
        }

        if (!property_exists($response, 'ResponseInformation') || !property_exists($response->ResponseInformation, 'Errors')) {
            $this->log(__METHOD__ . " Getting rates from Purolator failed: " . print_r($bits, 1) . " Response " . print_r($response, 1), true);
            Mage::throwException($this->getHelper()->__("Getting rates from Purolator failed: " . print_r($bits, 1) . " Response " . print_r($response, 1)));
            return false;
        }

        if (property_exists($response->ResponseInformation->Errors, 'Error')) {
            $this->log(__METHOD__ . " Got Error from Purolator : " . print_r($bits, 1), true);
            $this->log(__METHOD__ . " Response error  " . print_r($response, 1), true);
            return $response;
        }
        if ($response && $response->ShipmentEstimates->ShipmentEstimate) {
            $d = $response->ShipmentEstimates->ShipmentEstimate;
            $this->log(__METHOD__ . " Here we go request " . print_r($bits, 1));
            $this->log(__METHOD__ . " Here we go response : " . print_r($d, 1));
            if (is_object($d) && property_exists($d, 'ServiceID')) {
                $d = array($response->ShipmentEstimates->ShipmentEstimate);
            }
            //Loop through each Service returned and display the ID and TotalPrice
            foreach ($d as $estimate) {
                $res['service'] = (string)$estimate->ServiceID;
                $res['rate'] = $this->getEstimateRate($estimate);
                $res['transitTime'] = (string)$estimate->EstimatedTransitDays;
                $res['deliveryDate'] = (string)$estimate->ExpectedDeliveryDate;
                $results[] = $res;
            }
        }
        return $results;
    }


    /**
     * Used by Full estimate
     *
     * @param Mage_Shipping_Model_Rate_Request $request Request Object
     * @param type $items   Items
     *
     * @return \stdClass
     */
    public function _makeFullRequest(Mage_Shipping_Model_Rate_Request $request, $items)
    {
        $service_options = $this->getServiceOptions($request);
        $fullrequest = new stdClass();

        //Populate the Origin Information
        $fullrequest->Shipment = new stdClass();
        $fullrequest->Shipment->SenderInformation = new stdClass();
        $fullrequest->Shipment->SenderInformation->Address = new stdClass();
        $fullrequest->Shipment->SenderInformation->Address->City = Mage::getStoreConfig('shipping/origin/city', $this->getStore());
        $region = Mage::getModel('directory/region')->load(Mage::getStoreConfig('shipping/origin/region_id', $this->getStore()));
        $fullrequest->Shipment->SenderInformation->Address->Province = $region->getCode();
        $fullrequest->Shipment->SenderInformation->Address->Country = Mage::getStoreConfig('shipping/origin/country_id', $this->getStore());
        $fullrequest->Shipment->SenderInformation->Address->PostalCode = Mage::getStoreConfig('shipping/origin/postcode', $this->getStore());

        $fullrequest->Shipment->ReceiverInformation = new stdClass();
        $fullrequest->Shipment->ReceiverInformation->Address = new stdClass();
        $fullrequest->Shipment->ReceiverInformation->Address->City = $request->getDestCity();
        $fullrequest->Shipment->ReceiverInformation->Address->Province = $request->getDestRegionCode();
        $fullrequest->Shipment->ReceiverInformation->Address->Country = $request->getDestCountryId();
        $fullrequest->Shipment->ReceiverInformation->Address->PostalCode = str_replace(' ', '', $request->getDestPostcode());

        $fullrequest->Shipment->PaymentInformation = new stdClass();
        $fullrequest->Shipment->PaymentInformation->PaymentType = $this->getConfig("paytype");
        $fullrequest->Shipment->PaymentInformation->BillingAccountNumber = $this->getConfig('billingaccount');
        $fullrequest->Shipment->PaymentInformation->RegisteredAccountNumber = $this->getConfig('registeredaccount');

        //Populate the Package Information
        $fullrequest->PackageType = "CustomerPackaging";
        //Populate the Shipment Weight
        //assume weight is in KG for now
        // we have to tally weight. as products could be stored in lbs / kg etc
        //getConvertedWeight
        $weight = $this->getPackageWeightLb($request);
        $chunit = Mage::helper('chunit');
        $default_weight_unit = Mage::getStoreConfig('catalog/measure_units/weight');
        $default_dim_unit = Mage::getStoreConfig('catalog/measure_units/length');
        //convert
        $weight = $chunit->getConvertedWeight($weight, $default_weight_unit, 'lb');

        $fullrequest->Shipment->PackageInformation = new stdClass();
        $fullrequest->Shipment->PackageInformation->TotalWeight = new stdClass();
        if ($weight >= 1) {
            $fullrequest->Shipment->PackageInformation->TotalWeight->Value = $weight;
        }
        // what if the product weight between 0lb - 1lb ?
        if (($weight < 1) && ($weight > 0)) {
            $weight = 1;
            $fullrequest->Shipment->PackageInformation->TotalWeight->Value = $weight;
        }
        //Packagin algorithm
        $box_sizes = explode(",", $this->getConfig("boxsize"));
        $ratio = (float)$this->getConfig("ratio");


        foreach ($box_sizes as $key => $box_size) {
            $arr = explode("*", $box_size);
            if (count($arr) != 3) {
                unset($box_sizes[$key]);
                continue;
            }

            $temp = array();
            $temp["width"] =$arr[0];
            $temp["height"] =$arr[1];
            $temp["length"] = $arr[2];
            $temp["volume"] = $arr[0] * $arr[1] * $arr[2];
            $temp["useable_volume"] = (float)($temp["volume"] / 100) * $ratio;
            unset($box_sizes[$key]);
            if (!empty($temp)) {
                $box_sizes[$key] = $temp;
            }
        }

        $box_sizes = $this->sortByVolume($box_sizes);


        //Calculate the volume of the ordered items
        $total_item_volume = 0;
        $item_volumes = array();
        foreach ($request->getAllItems() as $item) {
            if ($item->getIsVirtual() || $item->getParentItem()) {
                continue;
            }
            $product = Mage::getModel('catalog/product')->load($item->getProductId());
            $item_width = $this->getConfig("width");
            $item_height = $this->getConfig("height");
            $item_length = $this->getConfig("length");


            if ($product->getItemWidth() && $product->getItemWidth() != "") {
                $item_width = $product->getItemWidth();
            }
            if ($product->getItemHeight() && $product->getItemHeight() != "") {
                $item_height = $product->getItemHeight();
            }
            if ($product->getItemLength() && $product->getItemLength() != "") {
                $item_length = $product->getItemLength();
            }
            $item_volume = $item_length * $item_width * $item_height;
            $dimentions["width"] = $item_width;
            $dimentions["length"] = $item_length;
            $dimentions["height"] = $item_height;
            $item_volumes[$item->getProductId()] = array("volume" => $item_volume, "qty" => $item->getQty(), "dim" => $dimentions);
            $total_item_volume += $item_volume * $item->getQty();
        }

        $fullrequest->Shipment->PackageInformation->PiecesInformation = new stdClass();

        //Just one box
        if ($total_item_volume < $box_sizes[0]["useable_volume"]) {
            $weight =  $chunit->getConvertedWeight($weight, $default_weight_unit, 'lb');
            if ($weight < 1) {
                $weight = 1;
            }
            $fullrequest->Shipment->PackageInformation->TotalPieces = 1;
            $fullrequest->Shipment->PackageInformation->PiecesInformation->Piece = new stdClass();
            $fullrequest->Shipment->PackageInformation->PiecesInformation->Piece->Weight = new stdClass();
            $fullrequest->Shipment->PackageInformation->PiecesInformation->Piece->Weight->Value = $weight;
            $fullrequest->Shipment->PackageInformation->PiecesInformation->Piece->Weight->WeightUnit = "lb";
            $fullrequest->Shipment->PackageInformation->PiecesInformation->Piece->Length = new stdClass();
            $fullrequest->Shipment->PackageInformation->PiecesInformation->Piece->Length->Value = $chunit->getConvertedLength($box_sizes[0]["length"], $default_dim_unit, 'cm');
            $fullrequest->Shipment->PackageInformation->PiecesInformation->Piece->Length->DimensionUnit = "cm";
            $fullrequest->Shipment->PackageInformation->PiecesInformation->Piece->Height = new stdClass();
            $fullrequest->Shipment->PackageInformation->PiecesInformation->Piece->Height->Value = $chunit->getConvertedLength($box_sizes[0]["height"], $default_dim_unit, 'cm');
            $fullrequest->Shipment->PackageInformation->PiecesInformation->Piece->Height->DimensionUnit = "cm";
            $fullrequest->Shipment->PackageInformation->PiecesInformation->Piece->Width = new stdClass();
            $fullrequest->Shipment->PackageInformation->PiecesInformation->Piece->Width->Value = $chunit->getConvertedLength($box_sizes[0]["width"], $default_dim_unit, 'cm');
            $fullrequest->Shipment->PackageInformation->PiecesInformation->Piece->Width->DimensionUnit = "cm";

        } else {
            $result = $this->getBoxes($item_volumes, $box_sizes[0]);
            $fullrequest->Shipment->PackageInformation->TotalPieces = count($result["Pieces"]);

            $width = $box_sizes[0]["width"];
            $height = $box_sizes[0]["height"];
            $length = $box_sizes[0]["length"];

            for ($i = 0; $i < count($result["Pieces"]); $i++) {
                if (array_key_exists("id", $result["Pieces"][$i])) {
                    //Custom Package
                    if (isset($result["Pieces"][$i]["weight"])) {
                        $weight = 1;
                        if ($result["Pieces"][$i]["weight"] > 1) {
                            $weight = $result["Pieces"][$i]["weight"];
                        }
                    }

                    $width = $result["Pieces"][$i]["dim"]["width"];
                    $height = $result["Pieces"][$i]["dim"]["height"];
                    $length = $result["Pieces"][$i]["dim"]["length"];

                } else {
                    //Normal box
                    if (isset($result["Pieces"][$i]["weight"])) {
                        $weight = 1;
                        if ($result["Pieces"][$i]["weight"] > 1) {
                            $weight = $result["Pieces"][$i]["weight"];
                        }
                    }
                }
                $weight =  $chunit->getConvertedWeight($weight, $default_weight_unit, 'lb');
                if ($weight < 1) {
                    $weight = 1;
                }
                $fullrequest->Shipment->PackageInformation->PiecesInformation->Piece[$i] = new stdClass();
                $fullrequest->Shipment->PackageInformation->PiecesInformation->Piece[$i]->Weight = new stdClass();
                $fullrequest->Shipment->PackageInformation->PiecesInformation->Piece[$i]->Length = new stdClass();
                $fullrequest->Shipment->PackageInformation->PiecesInformation->Piece[$i]->Height = new stdClass();
                $fullrequest->Shipment->PackageInformation->PiecesInformation->Piece[$i]->Width = new stdClass();

                $fullrequest->Shipment->PackageInformation->PiecesInformation->Piece[$i]->Weight->Value = $weight;
                $fullrequest->Shipment->PackageInformation->PiecesInformation->Piece[$i]->Weight->WeightUnit = "lb";

                $fullrequest->Shipment->PackageInformation->PiecesInformation->Piece[$i]->Length->Value = $chunit->getConvertedLength($length, $default_dim_unit, 'cm');
                $fullrequest->Shipment->PackageInformation->PiecesInformation->Piece[$i]->Length->DimensionUnit = "cm";

                $fullrequest->Shipment->PackageInformation->PiecesInformation->Piece[$i]->Height->Value = $chunit->getConvertedLength($height, $default_dim_unit, 'cm');
                $fullrequest->Shipment->PackageInformation->PiecesInformation->Piece[$i]->Height->DimensionUnit = "cm";

                $fullrequest->Shipment->PackageInformation->PiecesInformation->Piece[$i]->Width->Value = $chunit->getConvertedLength($width, $default_dim_unit, 'cm');
                $fullrequest->Shipment->PackageInformation->PiecesInformation->Piece[$i]->Width->DimensionUnit = "cm";
            }
        }

        if ($fullrequest->Shipment->PackageInformation->TotalWeight->Value < $this->_getItemsQty($request)) {
            //since the packages are not less than 1lb
            $fullrequest->Shipment->PackageInformation->TotalWeight->Value = $chunit->getConvertedWeight($this->_getItemsQty($request), $default_weight_unit, 'lb');
        }

        $fullrequest->Shipment->PackageInformation->TotalWeight->WeightUnit = "lb";

        // We need to send a service option, instead of sending the first lets try to send one that is
        // allowed and for the destination country.
        $allowed_methods = explode(',', $this->getConfigData('allowed_methods'));
        $countryMethods = $this->getMethodSource()->getMethodsByCountry($request->getDestCountryId(), $allowed_methods);
        $requestOption = $service_options->Services->Service[0];

        foreach($service_options->Services->Service as $k => $method) {

            if(isset($countryMethods[(string)$method->ID])) {
                $requestOption = $method;
                break;
            }
        }

        if ($service_options != false) {


            //Set Service ID
            $fullrequest->Shipment->PackageInformation->ServiceID = $requestOption->ID;

            //
            $fullrequest->ShowAlternativeServicesIndicator = "true";

            $i = 0;
            $fullrequest->Shipment->PackageInformation->OptionsInformation = new stdClass();
            $fullrequest->Shipment->PackageInformation->OptionsInformation->Options = new stdClass();
            $shipmentopt = $this->getConfig("shipmentopt");

            foreach ($requestOption->Options->Option as $option) {
                if ($option->ID == "OriginSignatureNotRequired") {
                    if ($shipmentopt == "OriginSignatureNotRequired") {
                        $fullrequest->Shipment->PackageInformation->OptionsInformation->Options->OptionIDValuePair[$i] = new stdClass();
                        $fullrequest->Shipment->PackageInformation->OptionsInformation->Options->OptionIDValuePair[$i]->ID = "OriginSignatureNotRequired";
                        $fullrequest->Shipment->PackageInformation->OptionsInformation->Options->OptionIDValuePair[$i]->Value = "true";
                        $i++;
                        break;
                    }
                }

                if ($option->ID == "ResidentialSignatureIntl") {
                    $fullrequest->Shipment->PackageInformation->OptionsInformation->Options->OptionIDValuePair[$i] = new stdClass();
                    $fullrequest->Shipment->PackageInformation->OptionsInformation->Options->OptionIDValuePair[$i]->ID = "ResidentialSignatureIntl";
                    $fullrequest->Shipment->PackageInformation->OptionsInformation->Options->OptionIDValuePair[$i]->Value = "true";
                    $i++;
                    break;
                }

                if ($option->ID == "ResidentialSignatureDomestic") {
                    if ($shipmentopt == "ResidentialSignatureDomestic") {
                        $fullrequest->Shipment->PackageInformation->OptionsInformation->Options->OptionIDValuePair[$i] = new stdClass();
                        $fullrequest->Shipment->PackageInformation->OptionsInformation->Options->OptionIDValuePair[$i]->ID = "ResidentialSignatureDomestic";
                        $fullrequest->Shipment->PackageInformation->OptionsInformation->Options->OptionIDValuePair[$i]->Value = "true";
                        $i++;
                        break;
                    }
                }
            }

            if ($this->getConfig("decval") == "1") {

                $totals = Mage::getSingleton('checkout/cart')->getQuote()->getTotals();
                $subtotal = $totals["subtotal"]->getValue();

                if ($subtotal <= 5000) {
                    $amount = number_format($subtotal, 2);
                } else {
                    $amount = 5000.00;
                }

                $fullrequest->Shipment->PackageInformation->OptionsInformation->Options->OptionIDValuePair[$i] = new stdClass();
                $fullrequest->Shipment->PackageInformation->OptionsInformation->Options->OptionIDValuePair[$i]->ID = "DeclaredValue";
                $fullrequest->Shipment->PackageInformation->OptionsInformation->Options->OptionIDValuePair[$i]->Value = $amount;
            }
        }

        return $fullrequest;
    }

    /**
     * Convert Weight to Lb
     *
     * @param type $w Weight
     * @param type $u Unit
     *
     * @return type
     */
    protected function getConvertedWeight($w, $u)
    {
        $weight = $w;
        switch ($u) {
            case Collinsharper_Purolator_Model_Source_Weightunits::LB:
                $weight = round($w * 0.4535, 2);
                break;
            case Collinsharper_Purolator_Model_Source_Weightunits::GR:
                $weight = round($w * 0.001, 2);
                break;
            case Collinsharper_Purolator_Model_Source_Weightunits::OZ:
                $weight = round($w * 0.028349, 2);
                break;
            case Collinsharper_Purolator_Model_Source_Weightunits::KG:
            default:
                $weight = $w;
                break;
        }

        return $weight;
    }

    /**
     * Converted Measure
     *
     * @param type $w Weight
     * @param type $u Unit
     *
     * @return type
     */
    private function getConvertedMeasure($w, $u)
    {
        $unit = $w;
        switch ($u) {
            case Collinsharper_Purolator_Model_Source_Dimentionunits::MM:
                $unit = round($w * 0.1, 0);
                break;
            case Collinsharper_Purolator_Model_Source_Dimentionunits::FT:
                $unit = round($w * 30.48, 0);
                break;
            case Collinsharper_Purolator_Model_Source_Dimentionunits::IN:
                $unit = round($w * 2.54, 0);
                break;
            case Collinsharper_Purolator_Model_Source_Dimentionunits::CM:
            default:
                $unit = $w;
                break;
        }
        return $unit;
    }

    /**
     * Get Pacakge weight in LB
     *
     * @param Mage_Shipping_Model_Rate_Request $request Request object
     *
     * @return type
     */
    private function getPackageWeightLb(Mage_Shipping_Model_Rate_Request $request)
    {
        $weight = 0;
        foreach ($request->getAllItems() as $item) {
            if ($item->getIsVirtual() || $item->getParentItem()) {
                continue;
            }
            $i = 0;
            // Get quanity for each Item and multiply by volume
            $qty = ($item->getQty() * 1);
            $product = Mage::getModel('catalog/product')->load($item->getProductId());
            $itemProduct = $item->getProduct();

            $configurableOption = null;
            if ($itemProduct->getTypeId() == 'configurable') {
                //The Product Id of the Simple Product
                $optionId = $item->getOptionByCode('simple_product')->getProductId();

                //The Simple Product
                $configurableOption = Mage::getModel('catalog/product')->load($optionId);
            }

            if ($configurableOption == null) {
                $weight += $this->getConvertedWeight(($qty * $product->getWeight()), $product->getWeightUnits());
            } else {
                $weight += $this->getConvertedWeight(($qty * $configurableOption->getWeight()), $configurableOption->getWeightUnits());
            }
            // Skip virtual products
        }

        return $weight;
    }

    /**
     * Get Items
     *
     * @param Mage_Shipping_Model_Rate_Request $request Request object
     *
     * @return boolean
     */
    public function _getItems(Mage_Shipping_Model_Rate_Request $request)
    {

        $post_string = '';
        // get the items from the shipping cart
        foreach ($request->getAllItems() as $item) {
            if ($item->getProduct()->getIsVirtual() || $item->getParentItem()) {
                continue;
            }
            $i = 0;
            // Get quantity for each Item and multiply by volume
            $qty = ($item->getQty() * 1);
            $product = Mage::getModel('catalog/product')->load($item->getProductId());
            // Skip virtual products

            $i++;
        }
        return false;
    }

    /**
     * Is tracking available
     *
     * @return boolean
     */
    public function isTrackingAvailable()
    {
        return true;
    }

    /**
     * Get tracking info
     *
     * @param type $tracking Tracking code
     *
     * @return \Mage_Shipping_Model_Tracking_Result|boolean
     */
    public function getTrackingInfo($tracking)
    {
        $info = array();
        $result = $this->getTracking($tracking);
        if ($result instanceof Mage_Shipping_Model_Tracking_Result) {
            if ($trackings = $result->getAllTrackings()) {
                return $trackings[0];
            }
        } elseif (is_string($result) && !empty($result)) {
            return $result;
        }
        return false;
    }

    /**
     * Get tracking
     *
     * @param type $trackings tracking info
     *
     * @return type
     */
    public function getTracking($trackings)
    {
        if (!is_array($trackings)) {
            $trackings = array($trackings);
        }
        return $this->_getCgiTracking($trackings);
    }

    /**
     * Get CGI tracking
     *
     * @param type $trackings tracking
     *
     * @return type
     */
    protected function _getCgiTracking($trackings)
    {
        $result = Mage::getModel('shipping/tracking_result');
        $defaults = $this->getDefaults();
        foreach ($trackings as $tracking) {
            $status = Mage::getModel('shipping/tracking_result_status');
            $status->setCarrier('ups');
            $status->setCarrierTitle($this->getConfigData('title'));
            $status->setTracking($tracking);
            $status->setPopup(1);
            $status->setUrl("https://eshiponline.purolator.com/ShipOnline/Public/Track/TrackingDetails.aspx?pin=$tracking");
            $result->append($status);
        }
        $this->_result = $result;
        return $result;
    }

    /**
     * Source method array
     *
     * @return type
     */
    public function getMethod()
    {
        return Collinsharper_Purolator_Model_Source_Method::toOptionArray();
    }

    /**
     * Get string
     *
     * @param string $string main string
     * @param type $start  start
     * @param type $end    end
     *
     * @return string
     */
    public function get_string($string, $start, $end)
    {
        $string = " " . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) {
            return "";
        }
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }

    /**
     * Get Service rules via API for validation
     *
     * @param type $req Request Object
     *
     * @return boolean
     */
    protected function getServiceRules($req)
    {

        $client = Mage::getSingleton('purolatormodule/soapinterface')->getClient(Collinsharper_Purolator_Model_Soapinterface::AVALIABILITYSERVICE);
        //Define the request object
        $request = new stdClass();
        //Populate the Origin Information``
        $request->SenderAddress = new stdClass();
        $request->SenderAddress->City = Mage::getStoreConfig('shipping/origin/city', $this->getStore());
        $region = Mage::getModel('directory/region')->load(Mage::getStoreConfig('shipping/origin/region_id', $this->getStore()));
        $request->SenderAddress->Province = $region->getCode();
        $request->SenderAddress->Country = Mage::getStoreConfig('shipping/origin/country_id', $this->getStore());
        $request->SenderAddress->PostalCode = Mage::getStoreConfig('shipping/origin/postcode', $this->getStore());

        //Populate the Desination Information
        $request->ReceiverAddress = new stdClass();
        $request->ReceiverAddress->City = $req->getDestCity();
        $request->ReceiverAddress->Province = $req->getDestRegionCode();
        $request->ReceiverAddress->Country = $req->getDestCountryId();
        $request->ReceiverAddress->PostalCode = $req->getDestPostcode();
        //Populate the Payment Information
        $request->BillingAccountNumber = $this->getConfig('billingaccount');
        try {
            $response = $client->GetServiceRules($request);
            if ($this->isDebug()) {
                $this->log(__METHOD__ . " GetServiceRules :");
                $this->log($request);
            }
            Mage::Helper('purolatormodule')->checkResponse($response);
        } catch (Exception $e) {
            $this->log(__CLASS__ . __FUNCTION__ . "exception");
            $this->log(__CLASS__ . __FUNCTION__ . " " . $e->getMessage());

            return false;
        }

        return $response;
    }

    /**
     * Get service options
     *
     * @param type $req            Request
     * @param type $createshipment Request type
     *
     * @return boolean
     */
    protected function getServiceOptions($req, $createshipment = false)
    {

        $client = Mage::getSingleton('purolatormodule/soapinterface')->getClient(Collinsharper_Purolator_Model_Soapinterface::AVALIABILITYSERVICE);
        //Define the request object
        $request = new stdClass();
        $request->SenderAddress = new stdClass();
        $request->ReceiverAddress = new stdClass();
        //Populate the Payment Information
        $request->BillingAccountNumber = $this->getConfig('billingaccount');
        if ($createshipment == false) {

            //Populate the Origin Information
            $request->SenderAddress->City = $this->convertString(Mage::getStoreConfig('shipping/origin/city', $this->getStore()));
            $region = Mage::getModel('directory/region')->load(Mage::getStoreConfig('shipping/origin/region_id', $this->getStore()));
            $request->SenderAddress->Province = $this->convertString($region->getCode());
            $request->SenderAddress->Country = $this->convertString(Mage::getStoreConfig('shipping/origin/country_id', $this->getStore()));
            $request->SenderAddress->PostalCode = Mage::getStoreConfig('shipping/origin/postcode', $this->getStore());

            //Populate the Desination Information
            $request->ReceiverAddress->City = $this->convertString($req->getDestCity());
            $request->ReceiverAddress->Province = $this->convertString($req->getDestRegionCode());
            $request->ReceiverAddress->Country = $this->convertString($req->getDestCountryId());
            $request->ReceiverAddress->PostalCode = $req->getDestPostcode();
        } else {

            //Populate the Origin Information
            $request->SenderAddress->City = $this->convertString(Mage::getStoreConfig('shipping/origin/city', $this->getStore()));
            $region = Mage::getModel('directory/region')->load(Mage::getStoreConfig('shipping/origin/region_id', $this->getStore()));
            $request->SenderAddress->Province = $this->convertString($region->getCode());
            $request->SenderAddress->Country = $this->convertString(Mage::getStoreConfig('shipping/origin/country_id', $this->getStore()));
            $request->SenderAddress->PostalCode = Mage::getStoreConfig('shipping/origin/postcode', $this->getStore());

            //Populate the Desination Information
            $request->ReceiverAddress->City = $createshipment->Shipment->ReceiverInformation->Address->City;
            $request->ReceiverAddress->Province = $createshipment->Shipment->ReceiverInformation->Address->Province;
            $request->ReceiverAddress->Country = $createshipment->Shipment->ReceiverInformation->Address->Country;
            $request->ReceiverAddress->PostalCode = str_replace(' ', '', $createshipment->Shipment->ReceiverInformation->Address->PostalCode);
        }
        try {
            $response = $client->GetServicesOptions($request);
            if ($this->isDebug()) {
                $this->log(__METHOD__ . " GetServicesOptions :");
                $this->log($request);
                $this->log(__METHOD__ . __LINE__ . " looking for response " . print_r($response ,1));
            }
            Mage::Helper('purolatormodule')->checkResponse($response);
        } catch (Exception $e) {
            $this->log(__CLASS__ . __FUNCTION__ . "exception");
            $this->log(__CLASS__ . __FUNCTION__ . " " . $e->getMessage());
            return false;
        }

        return $response;
    }

    /**
     * Get Items Qty
     *
     * @param Mage_Shipping_Model_Rate_Request $request Request Object
     *
     * @return type
     */
    protected function _getItemsQty(Mage_Shipping_Model_Rate_Request $request)
    {
        $qty = 0;
        // get the items from the shipping cart
        foreach ($request->getAllItems() as $item) {
            $product = Mage::getModel('catalog/product')->load($item->getProductId());
            if ($product->getIsVirtual() || $item->getParentItem()) {
                continue;
            }

            // Get quanity for each Item and multiply by volume
            $qty += $item->getQty();

            // Skip virtual products
        }
        return $qty;
    }

    /**
     * Validate Service Rules
     *
     * @param type $available_options Available options
     * @param type $service_rules     Service rule set
     * @param Mage_Shipping_Model_Rate_Request $request           Request Object
     *
     * @return type
     */
    public function validateServiceRules($available_options, $service_rules, Mage_Shipping_Model_Rate_Request $request, $_bits = null)
    {

        $total_weight = $this->getPackageWeightLb($request);
        $total_qty = $this->_getItemsQty($request);
        $i = 0;
        if (!empty($available_options)) {
            foreach ($available_options as $opt) {
                if (empty($service_rules->ServiceRules->ServiceRule)) {
                    continue;
                }
                foreach ($service_rules->ServiceRules->ServiceRule as $service_rule) {

                    if (isset($opt->Errors->Error)) {
                        return $available_options;
                    }

                    if ($service_rule->ServiceID == $opt["service"] && isset($available_options[$i])) {

                        if ($total_weight > $service_rule->MaximumTotalWeight->Value) {
                            unset($available_options[$i]);
                        }

                        if ($total_qty > $service_rule->MaximumTotalPieces) {
                            unset($available_options[$i]);
                        }

                        if ($this->_validateDimentions($service_rule, $request, $_bits)) {
                            unset($available_options[$i]);
                        }

                    }
                }
                $i++;
            }
        }

        return $available_options;
    }


    /**
     * If the item value > declared amount , show according to the config setup
     *
     * @param type $service_rule Service Rule
     * @param type $request      Request
     *
     * @return boolean
     */
    protected function _validateDeclaredValue($service_rule, $request)
    {

        if ($this->getConfig("decval") == "0") {
            //override the declared amount
            return false;
        }

        $totals = Mage::getSingleton('checkout/cart')->getQuote()->getTotals();
        $subtotal = $totals["subtotal"]->getValue();

        if ($subtotal > (int)$service_rule->MaximumDeclaredValue) {
            return true;
        }

        return false;
    }

    /**
     * Validate dimentions
     */
    protected function _validateDimentions($service_rule, $request, $_bits)
    {

        //Total pieces
        if (($_bits->Shipment->PackageInformation->TotalPieces < $service_rule->MinimumTotalPieces) || ($_bits->Shipment->PackageInformation->TotalPieces > $service_rule->MaximumTotalPieces)) {
            return true;
        }

        if ($_bits->Shipment->PackageInformation->TotalPieces == "1") {
            $piece = $_bits->Shipment->PackageInformation->PiecesInformation->Piece;
            if (is_array($piece)) {
                $piece = $piece[0];
            }
            if ($piece->Weight->Value < $service_rule->MinimumPieceWeight->Value || $piece->Weight->Value > $service_rule->MaximumPieceWeight->Value) {
                return true;
            }
            if ($piece->Length->Value < $this->getConvertedMeasure($service_rule->MinimumPieceLength->Value, Collinsharper_Purolator_Model_Source_Dimentionunits::IN) || $piece->Length->Value > $this->getConvertedMeasure($service_rule->MaximumPieceLength->Value, Collinsharper_Purolator_Model_Source_Dimentionunits::IN)) {
                return true;
            }
            if ($piece->Width->Value < $this->getConvertedMeasure($service_rule->MinimumPieceWidth->Value, Collinsharper_Purolator_Model_Source_Dimentionunits::IN) || $piece->Width->Value > $this->getConvertedMeasure($service_rule->MaximumPieceWidth->Value, Collinsharper_Purolator_Model_Source_Dimentionunits::IN)) {
                return true;
            }
            if ($piece->Height->Value < $this->getConvertedMeasure($service_rule->MinimumPieceHeight->Value, Collinsharper_Purolator_Model_Source_Dimentionunits::IN) || $piece->Height->Value > $this->getConvertedMeasure($service_rule->MaximumPieceHeight->Value, Collinsharper_Purolator_Model_Source_Dimentionunits::IN)) {
                return true;
            }

            $max_size = $piece->Length->Value + (2 * $piece->Width->Value) + (2 * $piece->Height->Value);

            if ($max_size > $this->getConvertedMeasure($service_rule->MaximumSize->Value, Collinsharper_Purolator_Model_Source_Dimentionunits::IN)) {
                return true;
            }
        } else {
            foreach ($_bits->Shipment->PackageInformation->PiecesInformation->Piece as $piece) {

                if ($piece->Weight->Value < $service_rule->MinimumPieceWeight->Value || $piece->Weight->Value > $service_rule->MaximumPieceWeight->Value) {
                    return true;
                }
                if ($piece->Length->Value < $this->getConvertedMeasure($service_rule->MinimumPieceLength->Value, Collinsharper_Purolator_Model_Source_Dimentionunits::IN) || $piece->Length->Value > $this->getConvertedMeasure($service_rule->MaximumPieceLength->Value, Collinsharper_Purolator_Model_Source_Dimentionunits::IN)) {
                    return true;
                }
                if ($piece->Width->Value < $this->getConvertedMeasure($service_rule->MinimumPieceWidth->Value, Collinsharper_Purolator_Model_Source_Dimentionunits::IN) || $piece->Width->Value > $this->getConvertedMeasure($service_rule->MaximumPieceWidth->Value, Collinsharper_Purolator_Model_Source_Dimentionunits::IN)) {
                    return true;
                }
                if ($piece->Height->Value < $this->getConvertedMeasure($service_rule->MinimumPieceHeight->Value, Collinsharper_Purolator_Model_Source_Dimentionunits::IN) || $piece->Height->Value > $this->getConvertedMeasure($service_rule->MaximumPieceHeight->Value, Collinsharper_Purolator_Model_Source_Dimentionunits::IN)) {
                    return true;
                }

                $max_size = $piece->Length->Value + (2 * $piece->Width->Value) + (2 * $piece->Height->Value);

                if ($max_size > $this->getConvertedMeasure($service_rule->MaximumSize->Value, Collinsharper_Purolator_Model_Source_Dimentionunits::IN)) {
                    return true;
                }
            }
        }


        return false;
    }

    /**
     * Convert String for API
     *
     * @param type $str String
     *
     * @return type
     */
    public function convertString($str)
    {
        return Mage::Helper('purolatormodule')->transliterateString($str);
    }

    /**
     * Apply freeshipping for the given method
     *
     * @param type $method Method
     *
     * @return type
     */
    public function applyFreeShipping($method)
    {
        $free_methods = explode(",", $this->getConfig("free_method"));
        $free_shipping_enabled = $this->getConfig("free_shipping_enable");
        $free_shipping_subtotal = $this->getConfig("free_shipping_subtotal");
        $free_shipping_handling = $this->getConfig("free_shipping_handling");

        $quote = Mage::getSingleton('checkout/session')->getQuote();

        $quote = Mage::getSingleton('checkout/cart')->getQuote();
        $totals = $quote->getTotals();
        $subtotal = $totals["subtotal"]->getValue();

        if (Mage::app()->getRequest()->getControllerName() == "cart" && Mage::app()->getRequest()->getActionName() == "index") {
            $quote = Mage::getSingleton('checkout/cart')->getQuote();
            $totals = $quote->getTotals();
            $subtotal = $totals["subtotal"]->getValue();
        }
        if (!$quote->getId()) {
            //admin order
            $quote = Mage::getSingleton('adminhtml/sales_order_create')->getQuote();
        }

        if ($free_shipping_enabled == "1" && in_array($method->getMethod(), $free_methods) && ((float)$quote->getBaseSubtotal() >= (float)$free_shipping_subtotal || (float)$subtotal >= (float)$free_shipping_subtotal)) {
            if ($free_shipping_handling == "1") {
                $method->setPrice(0);
            } else {
                $method->setPrice($this->getConfig('handling'));
            }
        }

        return $method;
    }

    /**
     *  Sort by volume
     */
    public function sortByVolume($boxes)
    {
        $temp = array();
        $temp_loc = null;
        for ($i = 0; $i < count($boxes); $i++) {
            for ($j = 0; $j < $i; $j++) {
                if ($boxes[$i] < $boxes[$j]) {
                    $temp_loc = $boxes[$i];
                    $boxes[$i] = $boxes[$j];
                    $boxes[$j] = $temp_loc;
                }
            }
        }

        return $boxes;
    }

    /**
     * Get boxes
     *
     * @param array $item_volumes
     * @param array $box
     *
     * @return array
     */
    public function getBoxes($item_volumes, $box)
    {
        $result = array();
        $result["items"] = array();
        $temp = array();
        $result["Pieces"] = array();
        foreach ($item_volumes as $key => $vol) {

            for ($i = 0; $i < (int)$vol["qty"]; $i++) {
                array_push($temp, array("vol" => $vol["volume"], "id" => $key, "qty" => 1)); //$vol["qty"]));
            }
            if ($vol["volume"] > $box["useable_volume"]) {
                $result["items"][$key] = "nobox";
                for ($i = 0; $i < $vol["qty"]; $i++) {
                    $product = Mage::getModel('catalog/product')->load($key);
                    array_push($result["Pieces"], array("id" => $key, "dim" => $vol["dim"], "weight" => $product->getWeight()));
                }
            } else {
                $result["items"][$key] = "hasbox";
            }
        }
        $volume = 0;
        $box_count = 0;
        $box_volume = $box["useable_volume"];
        $weight = 0;
        foreach ($temp as $key => $row) {
            $product = Mage::getModel('catalog/product')->load($row["id"]);
            $weight += $product->getWeight();
            if ($row["vol"] > $box_volume) {
                continue;
            }

            if ($volume < $box_volume) {
                $volume += $row["vol"];

                if (isset($temp[$key + 1]) && ($volume + $temp[$key + 1]["vol"] > $box_volume)) {
                    array_push($result["Pieces"], array(true, "weight" => $weight));
                    $box_count++;
                    $volume = 0;
                    $weight = 0;
                    continue;
                }

                if (!isset($temp[$key + 1])) {
                    array_push($result["Pieces"], array(true, "weight" => $weight));
                    $box_count++;
                    $volume = 0;
                    $weight = 0;
                    continue;
                }
            }
        }

        $result["boxes"] = $box_count;
        return $result;
    }
}
