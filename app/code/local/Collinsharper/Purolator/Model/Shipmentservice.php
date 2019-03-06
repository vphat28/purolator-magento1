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
 * Collinsharper_Purolator_Model_Shipmentservice
 *
 * @category Shipping
 * @package  Collinsharper.Purolator
 * @author   Collins Harper  <ch@collinsharper.com>
 * @license  http://collinsharper.com Proprietary License
 * @link     http://collinsharper.com
 */
class Collinsharper_Purolator_Model_Shipmentservice extends Collinsharper_Purolator_Model_Carrier_Shippingmethod
{

    private $shipment = null;

    /**
     * Get Shipment
     *
     * @return object
     */
    public function getShipment()
    {
        return $this->shipment;
    }

    /**
     * Set Shipment
     *
     * @param type $shipment Object
     *
     * @return void
     */
    public function setShipment($shipment)
    {
        $this->shipment = $shipment;
    }

    /**
     * Create Shipment
     *
     * @return \stdClass
     */
    protected function _createShipmentRequest()
    {

        $origin = Mage::getStoreConfig('shipping/origin', $this->getStore());
        $_order = Mage::getModel('sales/order')->load($this->getShipment()->getOrderId());
        $shipping_address = $_order->getShippingAddress();
        $billing_address = $_order->getBillingAddress();
        $same_as_billing = 0;
        if ($shipping_address->getCustomerAddressId() == $billing_address->getCustomerAddressId()) {
            $same_as_billing = 1;
        }


        $request = new stdClass();
        //Populate the Origin Information
        $request->Shipment = new stdClass();
        $request->Shipment->SenderInformation = new stdClass();
        $request->Shipment->SenderInformation->Address = new stdClass();
        $request->Shipment->SenderInformation->TaxNumber = $this->getConfig("vatnumber");
        $request->Shipment->SenderInformation->Address->Name = substr(Mage::app()->getStore()->getFrontendName(), 0, 30);
        $request->Shipment->SenderInformation->Address->Company = substr(Mage::app()->getStore()->getFrontendName(), 30, 30);
        $request->Shipment->SenderInformation->Address->StreetNumber = substr($origin["street_line1"], 0, 6);
        $request->Shipment->SenderInformation->Address->StreetName = substr(substr($origin["street_line1"], 6) . $origin["street_line2"], 0, 30);
        $request->Shipment->SenderInformation->Address->StreetAddress2 = substr(substr($origin["street_line1"], 6) . $origin["street_line2"], 30, 25);
        $request->Shipment->SenderInformation->Address->StreetAddress3 = substr(substr($origin["street_line1"], 6) . $origin["street_line2"], 55, 25);

        $request->Shipment->SenderInformation->Address->City = $this->convertString($origin["city"]);
        $region = Mage::getModel('directory/region')->load($origin["region_id"]);
        $request->Shipment->SenderInformation->Address->Province = $this->convertString($region->getCode());
        $request->Shipment->SenderInformation->Address->Country = $this->convertString($origin["country_id"]);
        $request->Shipment->SenderInformation->Address->PostalCode = $origin["postcode"];
        $request->Shipment->SenderInformation->Address->PhoneNumber = new stdClass();

        if ($request->Shipment->SenderInformation->Address->Country == "CA" || $request->Shipment->SenderInformation->Address->Country == "US") {
            $request->Shipment->SenderInformation->Address->PhoneNumber->CountryCode = $this->getCountryCode(Mage::getStoreConfig('general/store_information/phone'));
            $request->Shipment->SenderInformation->Address->PhoneNumber->AreaCode = $this->getAreaCode(Mage::getStoreConfig('general/store_information/phone'));
            $request->Shipment->SenderInformation->Address->PhoneNumber->Phone = $this->getTel(Mage::getStoreConfig('general/store_information/phone'));
        }

        $vat = "";
        $customer_vat = Mage::getModel('customer/customer')->load($_order->getData("customer_id"))->getData('taxvat');
        if ($customer_vat != "" && $customer_vat != NULL) {
            $vat = $customer_vat;

        }
        //Populate the Desination Information
        $request->Shipment->ReceiverInformation = new stdClass();
        if ($vat != "") {
            $request->Shipment->ReceiverInformation->TaxNumber = $vat;
        }
        $request->Shipment->ReceiverInformation->Address = new stdClass();

        $request->Shipment->ReceiverInformation->Address->Name = substr($shipping_address->getFirstname() . " " . $shipping_address->getLastname(), 0, 30);
        $request->Shipment->ReceiverInformation->Address->Company = substr($shipping_address->getFirstname() . " " . $shipping_address->getLastname(), 30, 30);
        $street = $shipping_address->getStreet();
        if (!isset($street[0])) {
            $street[0] = "";
        }
        if (!isset($street[1])) {
            $street[1] = "";
        }

        $request->Shipment->ReceiverInformation->Address->StreetName = substr($this->convertString($street[0] . " " . $street[1]), 0, 35);
        $request->Shipment->ReceiverInformation->Address->StreetAddress2 = substr($this->convertString($street[0] . " " . $street[1]), 35, 25);
        $request->Shipment->ReceiverInformation->Address->StreetAddress3 = substr($this->convertString($street[0] . " " . $street[1]), 60, 25);
        $request->Shipment->ReceiverInformation->Address->City = $this->convertString($shipping_address->getCity());
        $region = Mage::getModel('directory/region')->load($shipping_address->getRegionId());
        $request->Shipment->ReceiverInformation->Address->Province = $this->convertString($region->getCode());
        $request->Shipment->ReceiverInformation->Address->Country = $this->convertString($shipping_address->getCountryId());
        $request->Shipment->ReceiverInformation->Address->PostalCode = str_replace(' ', '', $shipping_address->getPostcode());
        $request->Shipment->ReceiverInformation->Address->PhoneNumber = new stdClass();

        if ($shipping_address->getCountryId() == "CA" || $shipping_address->getCountryId() == "US") {
            $request->Shipment->ReceiverInformation->Address->PhoneNumber->CountryCode = "1";
            $request->Shipment->ReceiverInformation->Address->PhoneNumber->AreaCode = $this->getAreaCode($shipping_address->getTelephone());
            $request->Shipment->ReceiverInformation->Address->PhoneNumber->Phone = $this->getTel($shipping_address->getTelephone());
        }
        //Package Information
        $request->Shipment->PackageInformation = new stdClass();

        //purolator_XXX
        $shipping_method = explode("_", $_order->getShippingMethod());
        $request->Shipment->PackageInformation->ServiceID = $shipping_method[1];

        //QTY calculations
        $_order_items = $_order->getAllItems();
        $total = 0;

        foreach ($_order_items as $_order_item) {
            //skip the bundle product , configurable product count and only get the relavent simple product
            if ($_order_item->getProductType() != 'simple') {
                continue;
            }
            $qty = $_order_item->getQtyOrdered();
            $total = $total + $qty;
        }

        $chunit = Mage::helper('chunit');
        $default_weight_unit = Mage::getStoreConfig('catalog/measure_units/weight');
        $default_dim_unit = Mage::getStoreConfig('catalog/measure_units/length');

        $weight = $this->_getPackageWeightLb($_order);
        $weight =  $chunit->getConvertedWeight($weight, $default_weight_unit, 'lb');

        //weight has to be at least 1lb for the api calls to succeed.
        // 20130904 - why were we comparing rates to the total count of boxes?

        if (floor($weight) < 1) {
            $weight = 1;
        }

        $request->Shipment->PackageInformation->TotalWeight = new stdClass();
        $request->Shipment->PackageInformation->TotalWeight->Value = $weight;
        $request->Shipment->PackageInformation->TotalWeight->WeightUnit = "lb";
        //TODO : Refactor this pacakge logic , replicated in Shippingmethod.php
        $box_sizes = explode(",", $this->getConfig("boxsize"));
        $ratio = (float)$this->getConfig("ratio");
        foreach ($box_sizes as $key => $box_size) {
            $arr = explode("*", $box_size);
            if (count($arr) != 3) {
                unset($box_sizes[$key]);
                continue;
            }

            $temp = array();
            $temp["width"] = $arr[0];
            $temp["height"] = $arr[1];
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
        foreach ($_order_items as $item) {
            //skip the bundle product , configurable product count and only get the relavent simple product
            if ($_order_item->getProductType() != 'simple') {
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
            $item_volumes[$item->getProductId()] = array("volume" => $item_volume, "qty" => $item->getQtyShipped(), "dim" => $dimentions);
            $total_item_volume += $item_volume * $item->getQtyShipped();
        }

        $request->Shipment->PackageInformation->PiecesInformation = new stdClass();

        //Just one box
        if ($total_item_volume < $box_sizes[0]["useable_volume"]) {
            $weight =  $chunit->getConvertedWeight($weight, $default_weight_unit, 'lb');

            if (floor($weight) < 1) {
                $weight = 1;
            }

            $request->Shipment->PackageInformation->TotalPieces = 1;
            $request->Shipment->PackageInformation->PiecesInformation->Piece = new stdClass();
            $request->Shipment->PackageInformation->PiecesInformation->Piece->Weight = new stdClass();
            $request->Shipment->PackageInformation->PiecesInformation->Piece->Weight->Value = $weight;
            $request->Shipment->PackageInformation->PiecesInformation->Piece->Weight->WeightUnit = "lb";
            $request->Shipment->PackageInformation->PiecesInformation->Piece->Length = new stdClass();
            $request->Shipment->PackageInformation->PiecesInformation->Piece->Length->Value = $box_sizes[0]["length"];
            $request->Shipment->PackageInformation->PiecesInformation->Piece->Length->DimensionUnit = "cm";
            $request->Shipment->PackageInformation->PiecesInformation->Piece->Height = new stdClass();
            $request->Shipment->PackageInformation->PiecesInformation->Piece->Height->Value = $box_sizes[0]["height"];
            $request->Shipment->PackageInformation->PiecesInformation->Piece->Height->DimensionUnit = "cm";
            $request->Shipment->PackageInformation->PiecesInformation->Piece->Width = new stdClass();
            $request->Shipment->PackageInformation->PiecesInformation->Piece->Width->Value = $box_sizes[0]["width"];
            $request->Shipment->PackageInformation->PiecesInformation->Piece->Width->DimensionUnit = "cm";

        } else {

            $result = $this->getBoxes($item_volumes, $box_sizes[0]);

            $request->Shipment->PackageInformation->TotalPieces = count($result["Pieces"]);

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
                $request->Shipment->PackageInformation->PiecesInformation->Piece[$i] = new stdClass();
                $request->Shipment->PackageInformation->PiecesInformation->Piece[$i]->Weight = new stdClass();
                $request->Shipment->PackageInformation->PiecesInformation->Piece[$i]->Length = new stdClass();
                $request->Shipment->PackageInformation->PiecesInformation->Piece[$i]->Height = new stdClass();
                $request->Shipment->PackageInformation->PiecesInformation->Piece[$i]->Width = new stdClass();


                $request->Shipment->PackageInformation->PiecesInformation->Piece[$i]->Weight->Value = $weight;
                $request->Shipment->PackageInformation->PiecesInformation->Piece[$i]->Weight->WeightUnit = "lb";

                $request->Shipment->PackageInformation->PiecesInformation->Piece[$i]->Length->Value = $chunit->getConvertedLength($length, $default_dim_unit, 'cm');
                $request->Shipment->PackageInformation->PiecesInformation->Piece[$i]->Length->DimensionUnit = "cm";

                $request->Shipment->PackageInformation->PiecesInformation->Piece[$i]->Height->Value = $chunit->getConvertedLength($height, $default_dim_unit, 'cm');
                $request->Shipment->PackageInformation->PiecesInformation->Piece[$i]->Height->DimensionUnit = "cm";

                $request->Shipment->PackageInformation->PiecesInformation->Piece[$i]->Width->Value =  $chunit->getConvertedLength($width, $default_dim_unit, 'cm');
                $request->Shipment->PackageInformation->PiecesInformation->Piece[$i]->Width->DimensionUnit = "cm";

            }
        }

        //Populate the Payment Information
        $request->Shipment->PaymentInformation = new stdClass();
        $request->Shipment->PaymentInformation->PaymentType = "Sender";
        $request->Shipment->PaymentInformation->BillingAccountNumber = $this->getConfig('billingaccount');

        $request->Shipment->PaymentInformation->RegisteredAccountNumber = $this->getConfig('registeredaccount');

        //Populate the Pickup Information
        $request->Shipment->PickupInformation = new stdClass();
        $request->Shipment->PickupInformation->PickupType = "DropOff";

        //Shipment Reference - sends the order id
        $request->Shipment->TrackingReferenceInformation = new stdClass();
        $request->Shipment->TrackingReferenceInformation->Reference1 = $this->getShipment()->getOrderId();
        //Define the Shipment Document Type
        $request->PrinterType = "Thermal";

        if ($request->Shipment->SenderInformation->Address->Country != $request->Shipment->ReceiverInformation->Address->Country) {
            $request->Shipment->InternationalInformation = new stdClass();
            $request->Shipment->InternationalInformation->DocumentsOnlyIndicator = false;
            //Duty information
            $request->Shipment->InternationalInformation->DutyInformation = new stdClass();
            $request->Shipment->InternationalInformation->DutyInformation->BillDutiesToParty = $this->getConfig("billdutiestoparty");
            $request->Shipment->InternationalInformation->DutyInformation->BusinessRelationship = $this->getConfig("businessrel");
            $request->Shipment->InternationalInformation->DutyInformation->Currency = $this->getConfig("dutyccy");

            $request->Shipment->InternationalInformation->ImportExportType = $this->getConfig("ietype");
            $request->Shipment->InternationalInformation->CustomsInvoiceDocumentIndicator = $this->getConfig("customerinvoice");

            //Content details
            $request->Shipment->InternationalInformation->ContentDetails = new stdClass();
            $i = 0;

            foreach ($_order_items as $_order_item) {
                if ($_order_item->getProductType() != 'simple') {
                    continue;
                }
                $qty = $_order_item->getQtyOrdered();
                $product = Mage::getModel('catalog/product')->load($_order_item->getProductId());

                $request->Shipment->InternationalInformation->ContentDetails->ContentDetail[$i] = new stdClass();

                $harmcode = $product->getHarmonizedcodeAttribute();
                if (trim($harmcode) == "") {
                    $harmcode = $this->getConfig("harmcode");
                }
                $com = $product->getCountryOfManufacture();
                if (trim($com) == "") {
                    $com = $this->getConfig("com");
                }

                $request->Shipment->InternationalInformation->ContentDetails->ContentDetail[$i]->Description = $this->convertString(substr($product->getName(), 0, 49));
                $request->Shipment->InternationalInformation->ContentDetails->ContentDetail[$i]->HarmonizedCode = $harmcode;
                $request->Shipment->InternationalInformation->ContentDetails->ContentDetail[$i]->CountryOfManufacture = $com;
                $request->Shipment->InternationalInformation->ContentDetails->ContentDetail[$i]->ProductCode = $product->getId();
                $request->Shipment->InternationalInformation->ContentDetails->ContentDetail[$i]->UnitValue = $product->getPrice();

                $request->Shipment->InternationalInformation->ContentDetails->ContentDetail[$i]->Quantity = $qty;


                if (strtolower($request->Shipment->ReceiverInformation->Address->Country) == "us") {
                    if ($this->getConfig("textilei") == "1") {
                        $request->Shipment->InternationalInformation->ContentDetails->ContentDetail[$i]->TextileIndicator = true;
                    } else {
                        $request->Shipment->InternationalInformation->ContentDetails->ContentDetail[$i]->TextileIndicator = $product->getTextileindicatorAttribute();
                    }

                    if ($product->getTextilemanAttribute() != "") {
                        $request->Shipment->InternationalInformation->ContentDetails->ContentDetail[$i]->TextileManufacturer = $product->getTextilemanAttribute();
                    } else {
                        $request->Shipment->InternationalInformation->ContentDetails->ContentDetail[$i]->TextileManufacturer = $this->getConfig("textilem");
                    }
                    if ($this->getConfig("nafta") == "1") {
                        $request->Shipment->InternationalInformation->ContentDetails->ContentDetail[$i]->NAFTADocumentIndicator = true;
                    } else {
                        $request->Shipment->InternationalInformation->ContentDetails->ContentDetail[$i]->NAFTADocumentIndicator = $product->getNaftaAttribute();
                    }

                    if ($this->getConfig("fcc") == "1") {
                        $request->Shipment->InternationalInformation->ContentDetails->ContentDetail[$i]->FCCDocumentIndicator = true;
                    } else {
                        $request->Shipment->InternationalInformation->ContentDetails->ContentDetail[$i]->FCCDocumentIndicator = $product->getFccAttribute();
                    }
                }

                if ($this->getConfig("sip") == "1") {
                    $request->Shipment->InternationalInformation->ContentDetails->ContentDetail[$i]->SenderIsProducerIndicator = true;
                } else {
                    $request->Shipment->InternationalInformation->ContentDetails->ContentDetail[$i]->SenderIsProducerIndicator = $product->getSipAttribute();
                }

                $i++;
            }


            // if ($same_as_billing == 0) {
            $request->Shipment->InternationalInformation->BuyerInformation = new stdClass();
            $request->Shipment->InternationalInformation->BuyerInformation->Address = new stdClass();
            $request->Shipment->InternationalInformation->BuyerInformation->Address->Name = substr($billing_address->getFirstname() . " " . $billing_address->getLastname(), 0, 30);
            $request->Shipment->InternationalInformation->BuyerInformation->Address->Company = substr($billing_address->getFirstname() . " " . $billing_address->getLastname(), 30, 30);
            $street = $billing_address->getStreet();
            if (!isset($street[0])) {
                $street[0] = "";
            }
            if (!isset($street[1])) {
                $street[1] = "";
            }
            $request->Shipment->InternationalInformation->BuyerInformation->Address->StreetNumber = substr($this->convertString($street[0] . " " . $street[1]), 0, 6);
            $request->Shipment->InternationalInformation->BuyerInformation->Address->StreetName = substr($this->convertString($street[0] . " " . $street[1]), 6, 35);
            $request->Shipment->InternationalInformation->BuyerInformation->Address->StreetAddress2 = substr($this->convertString($street[0] . " " . $street[1]), 41, 25);
            $request->Shipment->InternationalInformation->BuyerInformation->Address->StreetAddress3 = substr($this->convertString($street[0] . " " . $street[1]), 66, 25);
            $request->Shipment->InternationalInformation->BuyerInformation->Address->City = $this->convertString($billing_address->getCity());
            $region = Mage::getModel('directory/region')->load($billing_address->getRegionId());
            $request->Shipment->InternationalInformation->BuyerInformation->Address->Province = $this->convertString($region->getCode());
            $request->Shipment->InternationalInformation->BuyerInformation->Address->Country = $this->convertString($billing_address->getCountryId());
            $request->Shipment->InternationalInformation->BuyerInformation->Address->PostalCode = $billing_address->getPostcode();
            if ($billing_address->getCountryId() == "US" || $billing_address->getCountryId() == "CA") {
                $request->Shipment->InternationalInformation->BuyerInformation->Address->PhoneNumber = new stdClass();
                $request->Shipment->InternationalInformation->BuyerInformation->Address->PhoneNumber->CountryCode = $this->getCountryCode($billing_address->getTelephone());
                $request->Shipment->InternationalInformation->BuyerInformation->Address->PhoneNumber->AreaCode = $this->getAreaCode($billing_address->getTelephone());
                $request->Shipment->InternationalInformation->BuyerInformation->Address->PhoneNumber->Phone = $this->getTel($billing_address->getTelephone());
            }
            //    }
        }
        //Define OptionsInformation
        $request->Shipment->PackageInformation->OptionsInformation = new stdClass();
        $request->Shipment->PackageInformation->OptionsInformation->Options = new stdClass();
        $request->Shipment->PackageInformation->OptionsInformation->Options->OptionIDValuePair = new stdClass();
        $service_options = $this->getServiceOptions(null, $request);
        $shipmentopt = $this->getConfig("shipmentopt");

        $i = 0;
        $request->Shipment->PackageInformation->OptionsInformation = new stdClass();
        $request->Shipment->PackageInformation->OptionsInformation->Options = new stdClass();

        foreach ($service_options->Services->Service as $service) {
            if ($service->ID != $request->Shipment->PackageInformation->ServiceID) {
                continue;
            }

            foreach ($service->Options->Option as $option) {
                if ($option->ID == "ResidentialSignatureIntl") {
                    if ($shipping_address->getCountryId() == "CA" || $shipping_address->getCountryId() == "US") {
                        if ($this->getConfig("intsig") == "1") {
                            $request->Shipment->PackageInformation->OptionsInformation->Options->OptionIDValuePair[$i] = new stdClass();
                            $request->Shipment->PackageInformation->OptionsInformation->Options->OptionIDValuePair[$i]->ID = "ResidentialSignatureIntl";
                            $request->Shipment->PackageInformation->OptionsInformation->Options->OptionIDValuePair[$i]->Value = "true";
                            $i++;
                        }
                    }
                }

                if ($option->ID == "ResidentialSignatureDomestic") {
                    if ($shipmentopt == "ResidentialSignatureDomestic") {
                        $request->Shipment->PackageInformation->OptionsInformation->Options->OptionIDValuePair[$i] = new stdClass();
                        $request->Shipment->PackageInformation->OptionsInformation->Options->OptionIDValuePair[$i]->ID = "ResidentialSignatureDomestic";
                        $request->Shipment->PackageInformation->OptionsInformation->Options->OptionIDValuePair[$i]->Value = "true";
                        $i++;
                    }
                }

                if ($option->ID == "OriginSignatureNotRequired") {
                    if ($shipmentopt == "OriginSignatureNotRequired") {
                        $request->Shipment->PackageInformation->OptionsInformation->Options->OptionIDValuePair[$i] = new stdClass();
                        $request->Shipment->PackageInformation->OptionsInformation->Options->OptionIDValuePair[$i]->ID = "OriginSignatureNotRequired";
                        $request->Shipment->PackageInformation->OptionsInformation->Options->OptionIDValuePair[$i]->Value = "true";
                        $i++;
                    }
                }
            }
        }
        if ($this->getConfig("decval") == "1") {

            $subtotal = $_order->getSubtotal();

            if ($subtotal <= 5000) {
                $amount = number_format($subtotal, 2);
            } else {
                $amount = 5000.00;
            }


            $request->Shipment->PackageInformation->OptionsInformation->Options->OptionIDValuePair[$i] = new stdClass();
            $request->Shipment->PackageInformation->OptionsInformation->Options->OptionIDValuePair[$i]->ID = "DeclaredValue";
            $request->Shipment->PackageInformation->OptionsInformation->Options->OptionIDValuePair[$i]->Value = $amount;
        }
        return $request;
    }


    /**
     * Get Document Request
     *
     * @param type $pin      PIN
     * @param type $shipment Shipment
     * @param type $type     Type
     *
     * @return \stdClass
     */
    protected function _getDocumentRequest($pin, $shipment, $type = "label")
    {
        $magento_shipment = Mage::getModel("sales/order_shipment")->load($shipment->getMagentoShipmentId());
        $_order = Mage::getModel('sales/order')->load($magento_shipment->getOrderId());
        $shipping_address = $_order->getShippingAddress();
        $origin = Mage::getStoreConfig('shipping/origin', $this->getStore());

        $request = new stdClass();
        $request->DocumentCriterium = new stdClass();
        $request->DocumentCriterium->DocumentCriteria = new stdClass();
        $request->DocumentCriterium->DocumentCriteria->PIN = new stdClass();
        $request->DocumentCriterium->DocumentCriteria->DocumentTypes = new stdClass();

        $request->DocumentCriterium->DocumentCriteria->PIN->Value = $pin;
        $therm = "";
        if ($this->getConfig("printertype")) {
            $therm = "Thermal";
        }
        if ($origin["country_id"] == $shipping_address->getCountryId()) {
            $request->DocumentCriterium->DocumentCriteria->DocumentTypes->DocumentType = "DomesticBillOfLading" . $therm;
        } else {
            $nafta = Mage::getStoreConfig('carriers/purolatormodule/nafta');
            $fcc = Mage::getStoreConfig('carriers/purolatormodule/fcc');
            $customs = Mage::getStoreConfig('carriers/purolatormodule/customerinvoice');
            $request->DocumentCriterium->DocumentCriteria->DocumentTypes->DocumentType = "InternationalBillOfLading" . $therm;

            if ($type == "customs" && $customs == "1") {
                $request->DocumentCriterium->DocumentCriteria->DocumentTypes->DocumentType = "CustomsInvoice" . $therm;
            }
            if ($type == "nafta" && $nafta == "1") {
                $request->DocumentCriterium->DocumentCriteria->DocumentTypes->DocumentType = "NAFTA";
            }
            if ($type == "fcc" && $fcc == "1") {
                $request->DocumentCriterium->DocumentCriteria->DocumentTypes->DocumentType = "FCC740";
            }
        }

        return $request;
    }

    /**
     * Consolidate Request
     *
     * @return \stdClass
     */
    protected function _consolidationRequest()
    {
        return new stdClass();
    }

    /**
     * Create Manifest Request
     *
     * @return \stdClass
     */
    protected function _createManifestRequest()
    {
        $request = new stdClass();
        $request->ShipmentManifestDocumentCriterium = new stdClass();
        $request->ShipmentManifestDocumentCriterium->ShipmentManifestDocumentCriteria = new stdClass();
        $request->ShipmentManifestDocumentCriterium->ShipmentManifestDocumentCriteria->ManifestDate = date("Y-m-d");

        return $request;
    }

    /**
     * Create Void Shipment Request
     *
     * @param type $pin PIN
     *
     * @return \stdClass
     */
    protected function _createVoidShipmentRequest($pin)
    {
        $request = new stdClass();
        $request->PIN = new stdClass();
        $request->PIN->Value = $pin;

        return $request;
    }

    /**
     * Create Shipment
     *
     * @return boolean
     */
    public function createShipment()
    {
        $client = Mage::getSingleton('purolatormodule/soapinterface')->getClient(Collinsharper_Purolator_Model_Soapinterface::SHIPPINGSERVICE);
        try {
            if (property_exists($this->validateShipment(), "ValidShipment")) {

                if ($this->validateShipment()->ValidShipment) {
                    $response = $client->CreateShipment($this->_createShipmentRequest());
                    $request = $this->_createShipmentRequest();
                    if ($this->isDebug()){
                        mage::log("CreateShipment :","1","purolator.log");
                        mage::log($request,"1","purolator.log");
                    }
                    Mage::Helper('purolatormodule')->checkResponse($response);
                    return $response;
                } else {
                    return false;
                }
            }

        } catch (Exception $e) {
            mage::log(__CLASS__ . __FUNCTION__ . "exception");
            mage::log(__CLASS__ . __FUNCTION__ . " " . $e->getMessage());
            $this->_user_message = Mage::Helper('purolatormodule')->getUserMessage($e->getMessage(), $this->isDebug());

            return false;
        }
    }

    /**
     * Consolidate
     *
     * @return boolean
     */
    public function consolidate()
    {
        $client = Mage::getSingleton('purolatormodule/soapinterface')->getClient(Collinsharper_Purolator_Model_Soapinterface::SHIPPINGSERVICE, true);
        try {
            $response = $client->Consolidate($this->_consolidationRequest());
            $request = $this->_consolidationRequest();
            if ($this->isDebug()){
                mage::log("Consolidate :","1","purolator.log");
                mage::log($request,"1","purolator.log");
            }
            Mage::Helper('purolatormodule')->checkResponse($response);
        } catch (Exception $e) {
            mage::log(__CLASS__ . __FUNCTION__ . "exception");
            mage::log(__CLASS__ . __FUNCTION__ . " " . $e->getMessage());
            $this->_user_message = Mage::Helper('purolatormodule')->getUserMessage($e->getMessage(), $this->isDebug());

            return false;
        }

        return $response;
    }

    /**
     * Get manifest
     *
     * @return boolean
     */
    public function getManifest()
    {
        $client = Mage::getSingleton('purolatormodule/soapinterface')->getClient(Collinsharper_Purolator_Model_Soapinterface::SHIPPINGDOCUMENTSERVICE);
        try {
            $response = $client->GetShipmentManifestDocument($this->_createManifestRequest());
            $request = $this->_createManifestRequest();
            if ($this->isDebug()){
                mage::log("GetShipmentManifestDocument :","1","purolator.log");
                mage::log($request,"1","purolator.log");
            }
            Mage::Helper('purolatormodule')->checkResponse($response);
        } catch (Exception $e) {
            mage::log(__CLASS__ . __FUNCTION__ . "exception");
            mage::log(__CLASS__ . __FUNCTION__ . " " . $e->getMessage());
            $this->_user_message = Mage::Helper('purolatormodule')->getUserMessage($e->getMessage(), $this->isDebug());

            return false;
        }

        return $response;
    }

    /**
     * Get Package Weight LB
     *
     * @param type $_order Order
     *
     * @return type
     */
    protected function _getPackageWeightLb($_order)
    {
        $weight = 0;
        foreach ($_order->getAllItems() as $item) {
            $qty = ($item->getQtyOrdered() * 1);
            $product = Mage::getModel('catalog/product')->load($item->getProductId());
            $weight += $this->getConvertedWeight(($qty * $product->getWeight()), $product->getWeightUnits());
        }

        return $weight;
    }

    /**
     * Get Country code
     *
     * @param type $tel Tel
     *
     * @return string
     */
    protected function getCountryCode($tel)
    {
        return "1";
    }

    /**
     * Get Area Code
     *
     * @param type $tel Tel
     *
     * @return string
     */
    protected function getAreaCode($tel)
    {
        $tel = preg_replace('/[^\d]+/i', '', $tel);
        if (strlen($tel) > 0) {
            if (strlen($tel) > 10) {
                return substr($tel, -10, 3);
            }

            return substr($tel, 0, 3);

        }

    }

    /**
     * Get Tel
     *
     * @param type $tel Tel
     *
     * @return string
     */
    protected function getTel($tel)
    {
        $tel = preg_replace('/[^\d]+/i', '', $tel);

        if (strlen($tel) > 0) {
            if (strlen($tel) >= 10) {
                return substr($tel, -7);
            }
            if (strlen($tel) < 10) {
                //since the first 3 digits are taken for the area code , I will pad the incorrect number with zero's
                $num = substr($tel, 3);
                for ($i = 0; $i < (7 - strlen($num)); $i++) {
                    $num = $num . "0";
                }
            }
        }
    }

    /**
     * Get Shipment Document
     *
     * @param type $shipment Shipment
     * @param type $param    Param
     *
     * @return boolean
     */
    public function getShipmentDocument($shipment, $param = null)
    {
        $client = Mage::getSingleton('purolatormodule/soapinterface')->getClient(Collinsharper_Purolator_Model_Soapinterface::SHIPPINGDOCUMENTSERVICE);

        try {
            if ($param != null) {
                $response = $client->GetDocuments($this->_getDocumentRequest($shipment->getShipmentPin(), $shipment, $param));
                $request = $this->_getDocumentRequest($shipment->getShipmentPin(), $shipment, $param);
            } else {
                $response = $client->GetDocuments($this->_getDocumentRequest($shipment->getShipmentPin(), $shipment));
                $request = $this->_getDocumentRequest($shipment->getShipmentPin(), $shipment);
            }
            if ($this->isDebug()){
                mage::log("GetDocuments :","1","purolator.log");
                mage::log($request,"1","purolator.log");
            }
            Mage::Helper('purolatormodule')->checkResponse($response);
        } catch (Exception $e) {
            mage::log(__CLASS__ . __FUNCTION__ . "exception");
            mage::log(__CLASS__ . __FUNCTION__ . " " . $e->getMessage());
            $this->_user_message = Mage::Helper('purolatormodule')->getUserMessage($e->getMessage(), $this->isDebug());

            return false;
        }

        return $response;
    }

    /**
     * Get Customs Document
     *
     * @param type $shipment Shipment
     *
     * @return boolean
     */
    public function getCustomsDocument($shipment)
    {
        $client = Mage::getSingleton('purolatormodule/soapinterface')->getClient(Collinsharper_Purolator_Model_Soapinterface::SHIPPINGDOCUMENTSERVICE);

        try {
            $response = $client->GetDocuments($this->_getDocumentRequest($shipment->getShipmentPin(), $shipment, "customs"));
            $request = $this->_getDocumentRequest($shipment->getShipmentPin(), $shipment, "customs");
            if ($this->isDebug()){
                mage::log("GetDocuments :","1","purolator.log");
                mage::log($request,"1","purolator.log");
            }
            Mage::Helper('purolatormodule')->checkResponse($response);
        } catch (Exception $e) {
            mage::log(__CLASS__ . __FUNCTION__ . "exception");
            mage::log(__CLASS__ . __FUNCTION__ . " " . $e->getMessage());
            $this->_user_message = Mage::Helper('purolatormodule')->getUserMessage($e->getMessage(), $this->isDebug());

            return false;
        }

        return $response;
    }

    /**
     * Validate Shipment
     *
     * @return boolean
     */
    public function validateShipment()
    {
        $client = Mage::getSingleton('purolatormodule/soapinterface')->getClient(Collinsharper_Purolator_Model_Soapinterface::SHIPPINGSERVICE);
        try {
            $response = $client->ValidateShipment($this->_createShipmentRequest());
            $request = $this->_createShipmentRequest();
            if ($this->isDebug()){
                mage::log("ValidateShipment :","1","purolator.log");
                mage::log($request,"1","purolator.log");
            }
            Mage::Helper('purolatormodule')->checkResponse($response);
        } catch (Exception $e) {

            mage::log(__CLASS__ . __FUNCTION__ . "exception");
            mage::log(__CLASS__ . __FUNCTION__ . " " . $e->getMessage());
            $this->_user_message = Mage::Helper('purolatormodule')->getUserMessage($e->getMessage(), $this->isDebug());

            return false;
        }

        return $response;
    }

    /**
     * Void Shipment
     *
     * @param type $pin PIN
     *
     * @return type
     */
    public function voidShipment($pin)
    {
        $client = Mage::getSingleton('purolatormodule/soapinterface')->getClient(Collinsharper_Purolator_Model_Soapinterface::SHIPPINGSERVICE);
        try {
            $response = $client->VoidShipment($this->_createVoidShipmentRequest($pin));
            $request = $this->_createVoidShipmentRequest($pin);
            if ($this->isDebug()){
                mage::log("VoidShipment :","1","purolator.log");
                mage::log($request,"1","purolator.log");
            }
            Mage::Helper('purolatormodule')->checkResponse($response);
        } catch (Exception $e) {
            mage::log(__CLASS__ . __FUNCTION__ . "exception");
            mage::log(__CLASS__ . __FUNCTION__ . " " . $e->getMessage());
            $this->_user_message = Mage::Helper('purolatormodule')->getUserMessage($e->getMessage(), $this->isDebug());

            return $response;
        }

        return $response;
    }

}

