<?php
/** 
 *
 * @category    Collinsharper
 * @package     Collinsharper_Purolator
 * @author      Maxim Nulman
 */
class Collinsharper_Purolator_Model_Source_Dateformats
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 'full',
                'label' => Mage::helper('core')->formatDate(date('Y-m-d H:i:s'), 'full')
            ),
            array(
                'value' => 'long',
                'label' => Mage::helper('core')->formatDate(date('Y-m-d H:i:s'), 'long')
            ),
            array(
                'value' => 'medium',
                'label' => Mage::helper('core')->formatDate(date('Y-m-d H:i:s'), 'medium')
            ),
            array(
                'value' => 'short',
                'label' => Mage::helper('core')->formatDate(date('Y-m-d H:i:s'), 'short')
            )
        );
    }
}
