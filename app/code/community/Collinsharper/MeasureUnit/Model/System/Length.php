<?php
/** 
 *
 * @category    Collinsharper
 * @package     Collinsharper_MeasureUnit
 * @author      Maxim Nulman
 */
class Collinsharper_MeasureUnit_Model_System_Length
{
    
    /**
     * Retrieve option values array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = array(
            array(
                    'label' => Mage::helper('chunit')->__('Meter'),
                    'value' => 'm'
                ),
            array(
                    'label' => Mage::helper('chunit')->__('Centimeter'),
                    'value' => 'cm'
                ),
            array(
                    'label' => Mage::helper('chunit')->__('Milimeter'),
                    'value' => 'mm'
                ),
            array(
                    'label' => Mage::helper('chunit')->__('Inch'),
                    'value' => 'inch'
                ),
            array(
                    'label' => Mage::helper('chunit')->__('Feet'),
                    'value' => 'feet'
                ),
        );
        return $options;
    }
    
}