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
 * Collinsharper_Purolator_Model_Source_Buisrel
 *
 * @category Shipping
 * @package  Collinsharper.Purolator
 * @author   Collins Harper  <ch@collinsharper.com>
 * @license  http://collinsharper.com Proprietary License
 * @link     http://collinsharper.com
 */
class Collinsharper_Purolator_Block_Adminhtml_Purolatorshipment extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        $this->_controller = 'adminhtml_purolatorshipment';

        $this->_headerText = Mage::helper('sales')->__('Purolator Shipments');

        $this->_blockGroup = 'purolator';
        parent::__construct();
        $this->_removeButton('add');
        $this->_addBackButton();

        $this->_addButton(
            'create', array(
            'label' => Mage::helper('purolatormodule')->__('Create Purolator Shipments'),
            'onclick' => 'setLocation(\'' . $this->getUrl('*/*/create') . '\');',
            'class' => 'add',
            )
        );

        $this->_addButton(
            'manifest', array(
            'label' => Mage::helper('purolatormodule')->__('Purolator Manifests'),
            'onclick' => 'setLocation(\'' . $this->getUrl('*/purolatormanifest/index') . '\');',
            'class' => 'add',
            )
        );
    }

    /**
     * Get back url
     * 
     * @return void
     */
    protected function getBackUrl()
    {
        return $this->getUrl('*/purolatorshipment');
    }

}

