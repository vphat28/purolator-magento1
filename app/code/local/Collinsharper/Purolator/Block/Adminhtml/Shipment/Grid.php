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
 * Collinsharper_Purolator_Block_Adminhtml_Purolatorshipment_Grid
 *
 * @category Shipping
 * @package  Collinsharper.Purolator
 * @author   Collins Harper  <ch@collinsharper.com>
 * @license  http://collinsharper.com Proprietary License
 * @link     http://collinsharper.com
 */
class Collinsharper_Purolator_Block_Adminhtml_Purolatorshipment_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('sales_shipment_grid');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
    }

    /**
     * Retrieve collection class
     *
     * @return string
     */
    protected function _getCollectionClass()
    {
        return 'sales/order_shipment_grid_collection';
    }

    /**
     * Prepare and set collection of grid
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('sales/order_shipment')->getCollection();
        $action = $this->getRequest()->getActionName();

        //dispatch event for the observer
        Mage::dispatchEvent('prepare_grid_collection_shipment', array("collection" => $collection, "action" => $action));

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Prepare and add columns to grid
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareColumns()
    {

        $this->addColumn(
            'shipment_increment_id', array(
            'header' => Mage::helper('sales')->__('Shipment #'),
            'index' => 'shipment_increment_id',
            'filter_index' => 'main_table.increment_id',
            'type' => 'text',
            )
        );
        if ($this->getRequest()->getActionName() != "create") {
            $this->addColumn(
                'shipment_pin', array(
                'header' => Mage::helper('sales')->__('Purolator Shipment PIN'),
                'index' => 'shipment_pin',
                'type' => 'text',
                'filter_index' => 'cs.shipment_pin',
                )
            );
        }
        $this->addColumn(
            'created_at', array(
            'header' => Mage::helper('sales')->__('Date Shipped'),
            'index' => 'created_at',
            'filter_index' => 'main_table.created_at',
            'type' => 'datetime',
            )
        );

        $this->addColumn(
            'order_increment_id', array(
            'header' => Mage::helper('sales')->__('Order #'),
            'index' => 'order_increment_id',
            'filter_index' => 'o.increment_id',
            'type' => 'text',
            )
        );

        $this->addColumn(
            'order_created_at', array(
            'header' => Mage::helper('sales')->__('Order Date'),
            'index' => 'order_created_at',
            'filter_index' => 'o.created_at',
            'type' => 'datetime',
            )
        );

        $this->addColumn(
            'ordered_by', array(
            'header' => Mage::helper('sales')->__('Ordered By'),
            'index' => 'ordered_by',
            'filter_index' => 'CONCAT(o.customer_firstname, \' \',o.customer_lastname)',
            )
        );

        $this->addColumn(
            'total_qty', array(
            'header' => Mage::helper('sales')->__('Total Qty'),
            'index' => 'total_qty',
            'filter_index' => 'main_table.total_qty',
            'type' => 'number',
            )
        );

        $this->addColumn(
            'action', array(
            'header' => Mage::helper('sales')->__('Action'),
            'width' => '50px',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => Mage::helper('sales')->__('View'),
                    'url' => array('base' => '*/sales_shipment/view'),
                    'field' => 'shipment_id'
                )
            ),
            'filter' => false,
            'sortable' => false,
            'is_system' => true
            )
        );

        $this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV'));
        $this->addExportType('*/*/exportExcel', Mage::helper('sales')->__('Excel XML'));

        return parent::_prepareColumns();
    }

    /**
     * Get url for row
     *
     * @param string $row Row
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        if (!Mage::getSingleton('admin/session')->isAllowed('sales/order/shipment')) {
            return false;
        }
        $response = $this->getUrl(
            '*/sales_shipment/view', array(
            'shipment_id' => $row->getId(),
            )
        );
        return $response;
    }

    /**
     * Prepare and set options for massaction
     *
     * @return Mage_Adminhtml_Block_Sales_Shipment_Grid
     */
    protected function _prepareMassaction()
    {


        $action = $this->getRequest()->getActionName();
        $this->setMassactionIdField('main_table.entity_id');

        $this->getMassactionBlock()->setFormFieldName('shipment_ids');

        if ($action == "index") {
            $this->getMassactionBlock()->addItem(
                'void_shipments', array(
                'label' => Mage::helper('purolatormodule')->__('Void Purolator Shipment'),
                'url' => $this->getUrl('*/*/massVoid')
                )
            );

            $this->getMassactionBlock()->addItem(
                'create_shipments', array(
                'label' => Mage::helper('sales')->__('Print Purolator labels'),
                'url' => $this->getUrl('*/*/massLabel'),
                )
            );
        }

        if ($action == "create") {
            $this->getMassactionBlock()->addItem(
                'create_shipments', array(
                'label' => Mage::helper('purolatormodule')->__('Create Purolator shipments'),
                'url' => $this->getUrl('*/*/massCreate'),
                )
            );
        }


        return $this;
    }

    /**
     * Get url of grid
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/*', array('_current' => true));
    }

    /**
     * Get print purolator manifest
     *
     * @param type $purolatormanifest_id id
     *
     * @return type
     */
    public function getPrintPurolatormanifest($purolatormanifest_id)
    {
        $response = $this->getUrl(
            '*/purolatormanifest/print', array(
            'purolatormanifest_id' => $purolatormanifest_id
            )
        );
        return $response;
    }

}
