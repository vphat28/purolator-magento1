<?php
/** 
 *
 * @category    Collinsharper
 * @package     Collinsharper_MeasureUnit
 * @author      Maxim Nulman
 */
class Collinsharper_MeasureUnit_Helper_Data extends Mage_Core_Helper_Abstract
{
    
    
    public function getConvertedWeight($weight, $from_unit, $to_unit)
    {
        
        switch ($from_unit.'-'.$to_unit) {
            
            case 'kg-gram': $weight = 1000*$weight; break;
            
            case 'kg-lb': $weight = 2.21*$weight; break;
            
            case 'kg-oz': $weight = 35.27*$weight; break;
            
            case 'gram-kg': $weight = 0.001*$weight; break;
            
            case 'gram-lb': $weight = 0.0022*$weight; break;
            
            case 'gram-oz': $weight = 0.035*$weight; break;
            
            case 'lb-kg': $weight = 0.45*$weight; break;
            
            case 'lb-gram': $weight = 453.59*$weight; break;
            
            case 'lb-oz': $weight = 16*$weight; break;
            
            case 'oz-kg': $weight = 0.028*$weight; break;
            
            case 'oz-gram': $weight = 28.35*$weight; break;
            
            case 'oz-lb': $weight = 0.0625*$weight; break;
            
        }

        return round($weight, 3);
        
    }

    
    public function getConvertedLength($value, $from_unit, $to_unit)
    {

        switch ($from_unit.'-'.$to_unit) {
            
            case 'm-cm': $value = 100*$value; break;
            
            case 'm-mm': $value = 1000*$value; break;
            
            case 'm-inch': $value = 39.37*$value; break;
            
            case 'm-feet': $value = 3.28*$value; break;
            
            case 'cm-m': $value = 0.01*$value; break;
            
            case 'cm-mm': $value = 10*$value; break;
            
            case 'cm-inch': $value = 0.3937*$value; break;
            
            case 'cm-feet': $value = 0.0328*$value; break;
            
            case 'mm-m': $value = 0.001*$value; break;
            
            case 'mm-cm': $value = 0.1*$value; break;
            
            case 'mm-inch': $value = 0.03937*$value; break;
            
            case 'mm-feet': $value = 0.00328*$value; break;
            
            case 'inch-m': $value = 0.0254*$value; break;
            
            case 'inch-cm': $value = 2.54*$value; break;
            
            case 'inch-mm': $value = 25.4*$value; break;
            
            case 'inch-feet': $value = 0.083*$value; break;
            
            case 'feet-m': $value = 0.3048*$value; break;
            
            case 'feet-cm': $value = 30.48*$value; break;
            
            case 'feet-mm': $value = 304.8*$value; break;
            
            case 'feet-inch': $value = 12*$value; break;
            
        }
        
        return round($value, 1);
        
    }
    
    
}