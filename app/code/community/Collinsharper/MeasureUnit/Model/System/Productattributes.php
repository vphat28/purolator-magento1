<?php

class Collinsharper_MeasureUnit_Model_System_Productattributes
{
public function toOptionArray()
{
    $attributes = Mage::getModel('catalog/product')->getAttributes();
    $attributeArray =  array(
                'label' => 'Please select one..',
                'value' => ''
            );


    foreach($attributes as $a){

        foreach ($a->getEntityType()->getAttributeCodes() as $attributeName) {

            //$attributeArray[$attributeName] = $attributeName;
            $attributeArray[] = array(
                'label' => $attributeName,
                'value' => $attributeName
            );
        }
        break;
    }
    return $attributeArray; 
}
}
