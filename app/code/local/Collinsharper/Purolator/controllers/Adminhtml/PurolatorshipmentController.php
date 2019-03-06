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
 * Collinsharper_Purolator_Adminhtml_PurolatorshipmentController
 *
 * @category Shipping
 * @package  Collinsharper.Purolator
 * @author   Collins Harper  <ch@collinsharper.com>
 * @license  http://collinsharper.com Proprietary License
 * @link     http://collinsharper.com
 */
class Collinsharper_Purolator_Adminhtml_PurolatorshipmentController extends Mage_Adminhtml_Controller_Action
{

    /**
     * Index Action
     *
     * @return void
     */
    public function indexAction()
    {
        $this->loadLayout();

        $block = $this->getLayout()->createBlock('Collinsharper_Purolator_Block_Adminhtml_Purolatorshipment', 'purolatorshipment');

        $this->_title('Manage Purolator Shipment');

        $this->getLayout()->getBlock('content')->append($block);

        $this->renderLayout();
    }

    /**
     * Create Action
     *
     * @return void
     */
    public function createAction()
    {
        $this->_title(Mage::helper('purolatormodule')->__('Purolator Shipment'))
            ->_title(Mage::helper('purolatormodule')->__('Create Purolator Shipment'));

        $this->loadLayout();

        $block = $this->getLayout()->createBlock('Collinsharper_Purolator_Block_Adminhtml_Purolatorshipment', 'purolatorshipment');

        $this->getLayout()->getBlock('content')->append($block);

        $this->renderLayout();
    }

    /**
     *  Void Action
     *
     * @return void
     */
    public function voidAction()
    {
        $this->_title(Mage::helper('purolatormodule')->__('Purolator Shipment'))
            ->_title(Mage::helper('purolatormodule')->__('Void Purolator Shipment'));

        $this->loadLayout();

        $block = $this->getLayout()->createBlock('Collinsharper_Purolator_Block_Adminhtml_Purolatorshipment', 'purolatorshipment');

        $this->getLayout()->getBlock('content')->append($block);

        $this->renderLayout();
    }

    /**
     * Mass Create
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function massCreateAction()
    {

        $shipment_ids = $this->getRequest()->getParam('shipment_ids');
        $temp_shipment = array();
        $added = 0;
        $shipmentseviceModel = Mage::getSingleton('purolatormodule/shipmentservice');

        if (!empty($shipment_ids) && is_array($shipment_ids)) {

            try {

                //Create the shipments
                foreach ($shipment_ids as $shipment_id) {
                    //Purolator API calls
                    $magento_shipment = Mage::getModel("sales/order_shipment")->load($shipment_id);
                    $shipmentseviceModel->setShipment($magento_shipment);
                    $response = $shipmentseviceModel->createShipment();

                    if ($response == false) {
                        continue;
                    }
                    $piecePIN = array();
                    if (isset($response->PiecePINs) && $response->PiecePINs != "") {
                        $piecePIN = $this->_preparePiecePins($response, $piecePIN);
                    }

                    $shipment = Mage::getModel('purolatormodule/shipment')
                        ->setShipmentPin($response->ShipmentPIN->Value)
                        ->setPiecePin(implode(":", $piecePIN))
                        ->setMagentoShipmentId($shipment_id)
                        ->save();
                    $track = Mage::getModel('sales/order_shipment_track')->addData(
                        array(
                            'carrier_code' => 'purolatormodule',
                            'title' => Mage::helper('purolatormodule')->__('Shipment PIN #'),
                            'number' => $shipment->getShipmentPin()
                        )
                    );
                    //Save tracking information for the shipment
                    $magento_shipment->addTrack($track);
                    $magento_shipment->save();

                    $temp_shipment[$shipment_id] = $shipment->getId();
                    $added++;
                }

                if (empty($temp_shipment)) {
                    //No Shipments created so delete the manifest and quit the process.
                    throw new Exception("Failed to create shipment");
                }

                Mage::getSingleton('core/session')->addSuccess(Mage::helper('purolatormodule')->__('%s of %s Shipment has been successfully created', $added, count($shipment_ids)));
            } catch (Exception $e) {
                mage::log(__CLASS__ . __FUNCTION__ . "exception");
                mage::log(__CLASS__ . __FUNCTION__ . " " . $e->getMessage());
                $this->_getSession()->addError(Mage::helper('adminhtml')->__('Purolator Module was not able to create a shipment.'));
                $this->_redirect('*/*/index');
            }

            $this->_redirect('*/*/index');
        }
    }

    /**
     * View Action
     *
     * @return void
     */
    public function viewAction()
    {
        $purolatormanifest_id = $this->getRequest()->getParam('purolatormanifest_id');

        $this->loadLayout();

        $block = $this->getLayout()->createBlock(
            'Collinsharper_Purolator_Block_Adminhtml_Purolatormanifest_Shipment', 'purolatorshipment'
        );

        $this->_title('Manage Purolator Shipments');

        $this->getLayout()->getBlock('content')->append($block);

        $this->renderLayout();
    }

    /**
     * Mass label action
     *
     * @throws Exception
     *
     * @return boolean
     */
    public function massLabelAction()
    {

        try {

            $ids = $this->getRequest()->getParam('shipment_ids');
            $shipmentModel = Mage::getSingleton('purolatormodule/shipment');
            $manifest_shipment = Mage::getSingleton('purolatormodule/manifestshipment');
            $shipmentseviceModel = Mage::getSingleton('purolatormodule/shipmentservice');

            $pdf = new Zend_Pdf();

            foreach ($ids as $id) {

                $shipment = $shipmentModel->load($id, "magento_shipment_id");
                $magento_shipment = Mage::getModel("sales/order_shipment")->load($id);

                $origin = Mage::getStoreConfig('shipping/origin', Mage::app()->getStore());
                $_order = Mage::getModel('sales/order')->load($magento_shipment->getOrderId());
                $shipping_address = $_order->getShippingAddress();

                $response = $shipmentseviceModel->getShipmentDocument($shipment);
                try {
                    if (isset($response->Documents->Document->DocumentDetails->DocumentDetail->URL) && $response->Documents->Document->DocumentDetails->DocumentDetail->URL != "") {
                        $content = file_get_contents($response->Documents->Document->DocumentDetails->DocumentDetail->URL);
                        if ($content == false) {
                            throw new Exception("File content empty ! , Please try again");
                        }
                    } else {
                        throw new Exception("File content empty ! , Please try again");
                    }
                } catch (Exception $e) {
                    $this->_getSession()->addError(Mage::helper('adminhtml')->__($e->getMessage()));
                    return false;
                }
                $pdfString = $content;
                $pdf = $this->addPage($pdf, $pdfString);

                $pdf = $this->customsDocument($origin, $shipping_address, $shipmentseviceModel, $shipment, $pdf);
            }

            return $this->_prepareDownloadResponse(
                'labels-' . Mage::getSingleton('core/date')->date('Y-m-d_H-i-s') . '.pdf', $pdf->render(),
                'application/pdf'
            );
        } catch (Exception $e) {
            return false;
        }

        $this->_redirect('*/*');
    }

    /**
     * Customs Document
     *
     * @param type $origin              Origin
     * @param type $shipping_address    Shipping Address
     * @param type $shipmentseviceModel Shipment Model
     * @param type $shipment            Shipment Object
     * @param type $pdf                 PDF file
     *
     * @return boolean
     *
     * @throws Exception
     */
    public function customsDocument($origin, $shipping_address, $shipmentseviceModel, $shipment, $pdf)
    {
        $nafta = Mage::getStoreConfig('carriers/purolatormodule/nafta');
        $fcc = Mage::getStoreConfig('carriers/purolatormodule/fcc');
        $customs = Mage::getStoreConfig('carriers/purolatormodule/customerinvoice');

        if (strtolower($origin["country_id"]) != strtolower($shipping_address->getCountryId())) {
            /*
             *  Customs Invoice
             */
            if ($customs == "1") {
                $response = $shipmentseviceModel->getShipmentDocument($shipment, "customs");
                try {
                    if (isset($response->Documents->Document->DocumentDetails->DocumentDetail->URL) && $response->Documents->Document->DocumentDetails->DocumentDetail->URL != "") {
                        $content = file_get_contents($response->Documents->Document->DocumentDetails->DocumentDetail->URL);
                        if ($content == false) {
                            throw new Exception("File content empty ! , Please try again");
                        }
                    } else {
                        throw new Exception("File content empty ! , Please try again");
                    }
                } catch (Exception $e) {
                    $this->_getSession()->addError(Mage::helper('adminhtml')->__($e->getMessage()));
                    return false;
                }
                $pdfString = $content;
                $pdf = $this->addPage($pdf, $pdfString);
            }
            if (strtolower($shipping_address->getCountryId()) == "us") {
                /*
                 *  Nafta Document
                 */
                if ($nafta == "1") {

                    $response = $shipmentseviceModel->getShipmentDocument($shipment, "nafta");
                    try {
                        if (isset($response->Documents->Document->DocumentDetails->DocumentDetail->URL) && $response->Documents->Document->DocumentDetails->DocumentDetail->URL != "") {
                            $content = file_get_contents($response->Documents->Document->DocumentDetails->DocumentDetail->URL);
                            if ($content == false) {
                                throw new Exception("File content empty ! , Please try again");
                            }
                        } else {
                            throw new Exception("File content empty ! , Please try again");
                        }
                    } catch (Exception $e) {
                        $this->_getSession()->addError(Mage::helper('adminhtml')->__($e->getMessage()));
                        return false;
                    }
                    $pdfString = $content;
                    $pdf = $this->addPage($pdf, $pdfString);
                }
                /*
                 *  FCC Document
                 */
                if ($fcc == "1") {
                    $response = $shipmentseviceModel->getShipmentDocument($shipment, "fcc");
                    try {
                        if (isset($response->Documents->Document->DocumentDetails->DocumentDetail->URL) && $response->Documents->Document->DocumentDetails->DocumentDetail->URL != "") {
                            $content = file_get_contents($response->Documents->Document->DocumentDetails->DocumentDetail->URL);
                            if ($content == false) {
                                throw new Exception("File content empty ! , Please try again");
                            }
                        } else {
                            throw new Exception("File content empty ! , Please try again");
                        }
                    } catch (Exception $e) {
                        $this->_getSession()->addError(Mage::helper('adminhtml')->__($e->getMessage()));
                        return false;
                    }
                    $pdfString = $content;
                    $pdf = $this->addPage($pdf, $pdfString);
                }
            }
        }

        return $pdf;
    }

    /**
     * Add page
     *
     * @param type $pdf       PDF File
     * @param type $pdfString PDF Page content
     *
     * @return Object
     */
    public function addPage($pdf, $pdfString)
    {

        $extractor = new Zend_Pdf_Resource_Extractor();

        $temp_pdf = Zend_Pdf::parse($pdfString);

        foreach ($temp_pdf->pages as $k => $v) {
            $page = $extractor->clonePage($temp_pdf->pages[$k]);
            $pdf->pages[] = $page;
        }


        return $pdf;
    }

    /**
     * Void Shipments
     *
     * @return void
     */
    public function massVoidAction()
    {

        $ids = $this->getRequest()->getParam('shipment_ids');

        $shipmentModel = Mage::getSingleton('purolatormodule/shipment');
        $manifest_shipment = Mage::getSingleton('purolatormodule/manifestshipment');
        $shipmentseviceModel = Mage::getSingleton('purolatormodule/shipmentservice');
        $temp_shipment = array();
        $added = 0;
        $failed = 0;
        foreach ($ids as $id) {
            $shipment = $shipmentModel->load($id, "magento_shipment_id");
            $response = $shipmentseviceModel->voidShipment($shipment->getShipmentPin());
            if ($response->ShipmentVoided == 1) {
                $added++;
                $track = Mage::getModel('sales/order_shipment_track')->load($id, "parent_id");
                $track->delete();
                $shipment->delete();
            } else {
                $failed++;
            }
        }

        if ($added > 0) {
            Mage::getSingleton('core/session')->addSuccess(Mage::helper('purolatormodule')->__("{$added} of " . count($ids) . " shipments sucessfully voided."));
        }
        if ($failed > 0) {
            $this->_getSession()->addError(Mage::helper('adminhtml')->__("{$failed} of " . count($ids) . " shipments could not be voided. Shipments already added to a manifest can not be voided."));
        }

        $this->_redirect('*/*/index');
    }

    /**
     *  Prepare Piece PIN's
     *
     * @param stdClass $response Response Object
     * @param array $piecePIN Pieces PIN Array
     *
     * @return array Piece PIN Array
     */
    protected function _preparePiecePins($response, $piecePIN)
    {
        foreach ($response->PiecePINs as $pin) {
            if (isset($pin->Value) && $pin->Value != "") {
                array_push($piecePIN, $pin->Value);
            }
        }

        return $piecePIN;
    }

    protected function _isAllowed(){
        return Mage::getSingleton('admin/session')->isAllowed('sales/puro_purolatorshipment');
    }

}

