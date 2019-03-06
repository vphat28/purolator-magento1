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
 * Collinsharper_Purolator_Model_Source_Method
 *
 * @category Shipping
 * @package  Collinsharper.Purolator
 * @author   Collins Harper  <ch@collinsharper.com>
 * @license  http://collinsharper.com Proprietary License
 * @link     http://collinsharper.com
 */
class Collinsharper_Purolator_Model_Source_Method
{

    protected $_methods;

    /**
     * Get method title
     *
     * @param String $k Key
     *
     * @return String
     */
    public function getMethodTitle($k)
    {
        if (array_key_exists($k, $this->getMethods())) {
            return Mage::helper('purolatormodule')->__($this->_methods[$k]);
        }
        return Mage::helper('purolatormodule')->__('Unknown Purolator Method');
    }

    public function getMethodsByCountry($country = false, $allowed_methods = false)
    {
        $unitedStates = array(
            'PurolatorExpressU.S.12:00' => 'Purolator Express U.S. 12:00',
            'PurolatorExpressU.S.' => 'Purolator Express U.S.',
            'PurolatorExpressU.S.9AM' => 'Purolator Express U.S. 9AM',
            'PurolatorExpressU.S.10:30AM' => 'Purolator Express U.S. 10:30AM',
            'PurolatorGroundU.S.' => 'Purolator Ground U.S.',
        );
        $international = array(
            'PurolatorExpressInternational' => 'Purolator Express International',
            'PurolatorExpressInternational9AM' => 'Purolator Express International 9AM',
            'PurolatorExpressInternational12:00' => 'Purolator Express International 12:00',
        );
        $canada = array(
            'PurolatorExpress' => 'Purolator Express',
            'PurolatorExpress9AM' => 'Purolator Express 9AM',
            'PurolatorExpress10:30AM' => 'Purolator Express 10:30AM',
            'PurolatorExpress' => 'Purolator Express',
            'PurolatorExpressEnvelope9AM' => 'Purolator Express Envelope 9AM',
            'PurolatorExpressEnvelope10:30AM' => 'Purolator Express Envelope 10:30AM',
            'PurolatorExpressEnvelope' => 'Purolator Express Envelope',
            'PurolatorExpressPack9AM' => 'Purolator Express Pack 9AM',
            'PurolatorExpressPack10:30AM' => 'Purolator Express Pack 10:30AM',
            'PurolatorExpressPack' => 'Purolator Express Pack',
            'PurolatorExpressBox9AM' => 'Purolator Express Box 9AM',
            'PurolatorExpressBox10:30AM' => 'Purolator Express Box 10:30AM',
            'PurolatorExpressBox' => 'Purolator Express Box',
            'PurolatorGround' => 'Purolator Ground',
            'PurolatorGround9AM' => 'Purolator Ground 9AM',
            'PurolatorGround10:30AM' => 'Purolator Ground 10:30AM',
            'PurolatorGroundDistribution' => 'Purolator Ground Distribution',
        );

        $return = array('CA' => $canada, 'US' => $unitedStates, 'OTHER' => $international);

        if($country) {
            $return = isset($return[$country]) ? $return[$country] : $return['OTHER'];
        } else {
            $return = array_merge($canada, $unitedStates, $international);
        }

        if($allowed_methods) {
            foreach($return as $k => $v) {
                if(!in_array($k, $allowed_methods)) {
                    unset($return[$k]);
                }
            }
        }

        return $return;
    }

    /**
     * Return list of Methods
     *
     * @return array
     */
    public function getMethods()
    {
        if (!is_array($this->_methods)) {
            $this->_methods = $this->getMethodsByCountry();
        }
        return $this->_methods;
    }

    /**
     * Return a list of options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $methods = array();
        foreach ($this->getMethods() as $k => $t) {
            $methods[] = array('value' => $k, 'label' => Mage::helper('purolatormodule')->__($t));
        }
        return $methods;
    }

}

