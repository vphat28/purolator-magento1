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
 * Collinsharper_Purolator_Block_Renderer_Manifest_Shipment_Link
 *
 * @category Shipping
 * @package  Collinsharper.Purolator
 * @author   Collins Harper  <ch@collinsharper.com>
 * @license  http://collinsharper.com Proprietary License
 * @link     http://collinsharper.com
 */
class Collinsharper_Purolator_Block_Renderer_Manifest_Shipment_Link extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    /**
     * Render
     * 
     * @param Varien_Object $row Row
     *
     * @return type
     */
    public function render(Varien_Object $row)
    {

        $value = $row->getData($this->getColumn()->getIndex());

        return !empty($value) ? '<a href="' . $value . '" target="_blank">' . $value . '</a>' : '-';
    }

}
