<?php

/**
 * Collinsharper Purolator Module
 *
 * PHP version 5
 *
 * @category Shipping_Modules
 * @package  Collinsharper.Purolator
 * @author   Collins Harper  <ch@collinsharper.com>
 * @license  http://collinsharper.com Proprietary License
 * @link     http://collinsharper.com
 */

/**
 * Collinsharper_Purolator_Adminhtml_PurolatormanifestController
 *
 * @category Shipping_Modules
 * @package  Collinsharper.Purolator
 * @author   Collins Harper  <ch@collinsharper.com>
 * @license  http://collinsharper.com Proprietary License
 * @link     http://collinsharper.com
 */
class Collinsharper_Purolator_Adminhtml_PurolatormanifestController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Index Action
     *
     * @return void
     */
    public function indexAction()
    {

        $this->loadLayout();

        $block = $this->getLayout()->createBlock(
            'Collinsharper_Purolator_Block_Adminhtml_Purolatormanifest', 'purolatormanifest'
        );

        $this->_title('Manage Purolator Manifests');

        $this->getLayout()->getBlock('content')->append($block);

        $this->renderLayout();
    }
    /**
     * Create action
     *
     * @return void
     */
    public function createAction()
    {
        $this->_title(Mage::helper('purolatormodule')->__('Purolator Manifest'))
            ->_title(Mage::helper('purolatormodule')->__('Create Purolator Manifest'));
        $this->loadLayout();
        $block = $this->getLayout()->createBlock(
            'Collinsharper_Purolator_Block_Adminhtml_Purolatormanifest_Shipment', 'manifest'
        );

        $this->getLayout()->getBlock('content')->append($block);

        $this->renderLayout();
    }
    /**
     * Consolidate Shipments for manifests
     *
     * @throws Exception
     *
     * @return void
     */
    public function consolidateAction()
    {
        $shipmentseviceModel = Mage::getSingleton('purolatormodule/shipmentservice');
        $manifest = Mage::getModel('purolatormodule/purolatormanifest');
        $shipment = Mage::getSingleton('purolatormodule/shipment');
        $manifest_shipment = Mage::getSingleton('purolatormodule/manifestshipment');
        //consolidate shipments
        $consolidate_response = $shipmentseviceModel->consolidate();

        $manifest_response = $shipmentseviceModel->getManifest();
        $manifest_response = $shipmentseviceModel->getManifest();
        if (!property_exists($manifest_response->ManifestBatches->ManifestBatch->ManifestBatchDetails, "ManifestBatchDetail")) {
            $this->_redirect('*/*/index');
        }
        $manifest_details = $manifest_response->ManifestBatches->ManifestBatch->ManifestBatchDetails->ManifestBatchDetail;

        $manifest_arr = array();
        if (count($manifest_details) > 1) {
            foreach ($manifest_details as $item) {
                array_push($manifest_arr, $item);
            }
            $latest_manifest = $manifest_arr[count($manifest_arr) - 1];
        } else {
            $latest_manifest = $manifest_details;
        }

        try {
            if (!property_exists($latest_manifest, "URL") || ($latest_manifest->URL == "")) {
                throw new Exception();
            }

            $manifest = Mage::getModel('purolatormodule/purolatormanifest');

            $manifest_collection = $manifest->load($latest_manifest->URL, 'url');
            if ($manifest_collection->getData('id') == null) {
                $manifest->setStatus($latest_manifest->DocumentStatus)
                    ->setShipmentManifestDate($manifest_response->ManifestBatches->ManifestBatch->ShipmentManifestDate)
                    ->setManifestCloseDate($manifest_response->ManifestBatches->ManifestBatch->ManifestCloseDateTime)
                    ->setUrl($latest_manifest->URL)
                    ->setDocumentType($latest_manifest->DocumentType)
                    ->setDescription($latest_manifest->Description)
                    ->save();
            } else {
                $this->_getSession()->addError(Mage::helper('adminhtml')->__('Manifest already created for the shipments.'));
            }
        } catch (Exception $e) {
            $this->_getSession()->addError(Mage::helper('adminhtml')->__('Purolator Module was not able to consolidate manifest.'));
        }

        $this->_redirect('*/*/index');
    }
    /**
     * Mass Create Shipment Action
     *
     * @throws Exception
     *
     * @return void
     */
    public function massCreateAction()
    {
        $shipment_ids = $this->getRequest()->getParam('shipment_ids');
        $temp_shipment = array();
        $added = 0;
        $shipmentseviceModel = Mage::getSingleton('purolatormodule/shipmentservice');
        $manifest = Mage::getSingleton('purolatormodule/purolatormanifest')->setStatus()->save();
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
                    $shipment = Mage::getModel('purolatormodule/shipment')->setShipmentPin($response->ShipmentPIN->Value)
                        ->setPiecePIN(implode(":", $piecePIN))
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
                    $manifest->delete();
                    throw new Exception("Failed to create shipment");
                }
                //consolidate the shipments
                $consolidate_response = $shipmentseviceModel->consolidate();
                //Get the manifest document
                $manifest_response = $shipmentseviceModel->getManifest();
                $manifest_response = $shipmentseviceModel->getManifest();
                $manifest_details = $manifest_response->ManifestBatches->ManifestBatch->ManifestBatchDetails;
                $manifest_arr = array();
                foreach ($manifest_details as $item) {
                    foreach ($item as $row) {
                        array_push($manifest_arr, $row);
                    }
                }
                $latest_manifest = $manifest_arr[count($manifest_arr) - 1];
                $manifest = Mage::getModel('purolatormodule/purolatormanifest')->load($manifest->getId());
                $manifest->setStatus($latest_manifest->DocumentStatus)
                    ->setShipmentManifestDate($manifest_response->ManifestBatches->ManifestBatch->ShipmentManifestDate)
                    ->setManifestCloseDate($manifest_response->ManifestBatches->ManifestBatch->ManifestCloseDateTime)
                    ->setUrl($latest_manifest->URL)
                    ->setDocumentType($latest_manifest->DocumentType)
                    ->setDescription($latest_manifest->Description)
                    ->save();
                foreach ($shipment_ids as $shipment_id) {
                    $manifest_shipment = Mage::getModel('purolatormodule/manifestshipment')
                            ->setManifestId((int) $manifest->getId())
                            ->setMagentoShipmentId($shipment_id)
                            ->setShipment((int) $temp_shipment[$shipment_id])
                            ->save();
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
     *  View Shipment action
     *
     *  @return void
     */
    public function viewAction()
    {
        $this->_redirect('*/*/index');
    }
    /**
     * Create Labels for the shipments
     *
     * @return boolean
     * @throws Exception
     */
    public function massLabelAction()
    {
        try {
            $purolatormanifest_id = $this->getRequest()->getParam('purolatormanifest_id');
            $ids = $this->getRequest()->getParam('shipment_ids');
            $shipmentModel = Mage::getSingleton('purolatormodule/shipment');
            $manifest_shipment = Mage::getSingleton('purolatormodule/manifestshipment');
            $shipmentseviceModel = Mage::getSingleton('purolatormodule/shipmentservice');

            $pdf = new Zend_Pdf;
            foreach ($ids as $id) {
                $mani_shipment = $manifest_shipment->load($id, "magento_shipment_id");
                $shipment = $shipmentModel->load($mani_shipment->getShipment());

                //Print Labels
                $response = $shipmentseviceModel->getShipmentDocument($shipment, $param);
                try {
                    if (isset($response->Documents->Document->DocumentDetails->DocumentDetail->URL) && $response->Documents->Document->DocumentDetails->DocumentDetail->URL != "") {
                        $content = file_get_contents($response->Documents->Document->DocumentDetails->DocumentDetail->URL);
                    } else {
                        throw new Exception("File content empty ! , Please try again");
                    }
                } catch (Exception $e) {
                    return false;
                }
                $pdfString = $content;
                $pdf = $this->addPage($pdf, $pdfString);
            }

            header('content-type: application/pdf');
            header('Content-Disposition: attachment; filename="labels-' . date('Y-m-d--H-i-s') . '.pdf"');
            echo $pdf->render();
        } catch (Exception $e) {
            return false;
        }
        $this->_redirect('*/*');
    }
    /**
     * Add a page to the pdf file generated.
     *
     * @param Object $pdf       Pdf file
     * @param string $pdfString New page content
     *
     * @return Zend_Pdf
     */
    public function addPage($pdf, $pdfString)
    {

        $extractor = new Zend_Pdf_Resource_Extractor();

        $temp_pdf = Zend_Pdf::parse($pdfString);

        $page = $extractor->clonePage($temp_pdf->pages[0]);

        $pdf->pages[] = $page;
        return $pdf;
    }
    /**
     *  Mass Void Shipment
     *
     *  @return void
     */
    public function massVoidAction()
    {
        $purolatormanifest_id = $this->getRequest()->getParam('purolatormanifest_id');
        $ids = $this->getRequest()->getParam('shipment_ids');
        $shipmentModel = Mage::getSingleton('purolatormodule/shipment');
        $manifest_shipment = Mage::getSingleton('purolatormodule/manifestshipment');
        $shipmentseviceModel = Mage::getSingleton('purolatormodule/shipmentservice');
        $manifest = Mage::getModel('purolatormodule/purolatormanifest')->load($purolatormanifest_id);

        $temp_shipment = array();
        foreach ($ids as $id) {
            $manifest_ship = $manifest_shipment->load($id, "magento_shipment_id");
            $shipment = $shipmentModel->load($manifest_ship->getShipment());
            $response = $shipmentseviceModel->voidShipment($shipment->getShipmentPin());
            if ($response->ShipmentVoided == 1) {
                $track = Mage::getModel('sales/order_shipment_track')->load($manifest_ship->getMagentoShipmentId(), "parent_id");
                $track->delete();
                $manifest->delete();
                $shipment->delete();
                $manifest_ship->delete();
            }
        }

        $this->_redirect('*/*/index');
    }
    /**
     *  Prepare Piece PIN's
     *
     * @param stdClass $response Response Object
     * @param array    $piecePIN Pieces PIN Array
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
        return Mage::getSingleton('admin/session')->isAllowed('sales/puro_purolatormanifest');
    }
}