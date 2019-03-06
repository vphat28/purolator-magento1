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
 * Collinsharper_Purolator_Block_Adminhtml_Purolatormanifest
 *
 * @category Shipping
 * @package  Collinsharper.Purolator
 * @author   Collins Harper  <ch@collinsharper.com>
 * @license  http://collinsharper.com Proprietary License
 * @link     http://collinsharper.com
 */
class Collinsharper_Purolator_Block_Adminhtml_Purolatormanifest extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        $this->_controller = 'adminhtml_purolatormanifest';
        $this->_headerText = Mage::helper('sales')->__('Purolator Manifests');
        $this->_blockGroup = 'purolator';

        parent::__construct();

        $this->_removeButton('add');
        $this->_addButton(
            'add', array(
            'label' => Mage::helper('purolatormodule')->__('Create New Purolator Manifest'),
            'onclick' => 'setLocation(\'' . $this->getUrl('*/*/consolidate') . '\');',
            'class' => 'add',
            )
        );

        $this->_addButton(
            'shipments', array(
            'label' => Mage::helper('purolatormodule')->__('Purolator Shipments'),
            'onclick' => 'setLocation(\'' . $this->getUrl('*/purolatorshipment/index') . '\');',
            'class' => 'add',
            )
        );
    }

}

