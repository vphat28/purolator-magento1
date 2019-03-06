<?php
/** 
 *
 * @category    Collinsharper
 * @package     Collinsharper_MeasureUnit
 * @author      Maxim Nulman
 */
class Collinsharper_MeasureUnit_Model_System_Weight
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
                    'label' => Mage::helper('chunit')->__('kg'),
                    'value' => 'kg'
                ),
            array(
                    'label' => Mage::helper('chunit')->__('gram'),
                    'value' => 'gram'
                ),
            array(
                    'label' => Mage::helper('chunit')->__('lb'),
                    'value' => 'lb'
                ),
            array(
                    'label' => Mage::helper('chunit')->__('oz'),
                    'value' => 'oz'
                ),
        );
        return $options;
    }
    
}