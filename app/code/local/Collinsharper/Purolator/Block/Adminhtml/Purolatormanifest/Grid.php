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
 * Collinsharper_Purolator_Block_Adminhtml_Purolatormanifest_Grid
 *
 * @category Shipping
 * @package  Collinsharper.Purolator
 * @author   Collins Harper  <ch@collinsharper.com>
 * @license  http://collinsharper.com Proprietary License
 * @link     http://collinsharper.com
 */
class Collinsharper_Purolator_Block_Adminhtml_Purolatormanifest_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    /**
     * Construct
     *
     * @return void
     */
    public function __construct()
    {

        parent::__construct();
        $this->setId('purolatormanifestGrid');
        $this->setDefaultSort('purolatormanifest_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * Get Create Button Html
     *
     * @return void
     */
    public function getCreateButtonHtml()
    {
        return $this->getChildHtml('create_btn');
    }

    /**
     * Get Main buttin html
     *
     * @return void
     */
    public function getMainButtonsHtml()
    {
        $html = parent::getMainButtonsHtml();
        $html.= $this->getCreateButtonHtml();
        return $html;
    }

    /**
     * Prepare colleciton
     *
     * @return collection object
     */
    protected function _prepareCollection()
    {

        $collection = Mage::getModel('purolatormodule/purolatormanifest')
            ->getCollection()
            ->setOrder('id', 'DESC');

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Prepare columns
     *
     * @return object
     */
    protected function _prepareColumns()
    {

        $this->addColumn(
            'id', array(
            'header' => Mage::helper('purolatormodule')->__('Purolator Manifest ID #'),
            'index' => 'id',
            'type' => 'number',
            'filter' => false
            )
        );

        $this->addColumn(
            'created_at', array(
            'header' => Mage::helper('purolatormodule')->__('Date Created'),
            'index' => 'shipment_manifest_date',
            'type' => 'text',
            'filter' => false
            )
        );

        $this->addColumn(
            'updated_at', array(
            'header' => Mage::helper('purolatormodule')->__('Date Updated'),
            'index' => 'manifest_close_date',
            'type' => 'text',
            'filter' => false
            )
        );

        $this->addColumn(
            'document_type', array(
            'header' => Mage::helper('purolatormodule')->__('Document Type'),
            'index' => 'document_type',
            'type' => 'text',
            'filter' => false
            )
        );

        $this->addColumn(
            'description', array(
            'header' => Mage::helper('purolatormodule')->__('Description'),
            'index' => 'description',
            'type' => 'text',
            'filter' => false
            )
        );

        $this->addColumn(
            'url', array(
            'header' => Mage::helper('purolatormodule')->__('Url'),
            'index' => 'url',
            'type' => 'text',
            'renderer' => 'Collinsharper_Purolator_Block_Renderer_Manifest_Shipment_Link',
            'filter' => false
            )
        );
        return parent::_prepareColumns();
    }

    /**
     * Get row url
     * 
     * @param type $row Row
     *
     * @return void
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/view', array('purolatormanifest_id' => $row->getId()));
    }

}

