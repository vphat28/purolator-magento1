<?php

/**
 * Collinsharper Purolator Module
 *
 * PHP version 5
 *
 * @category Shipping_Modules
 * @package  Collinsharper.Purolator.Model
 * @author   Collins Harper  <ch@collinsharper.com> 
 * @license  http://collinsharper.com Proprietary License 
 * @link     http://collinsharper.com
 */

/**
 * Collinsharper_Purolator_Model_Observer
 *
 * @category Shipping_Modules
 * @package  Collinsharper.Purolator.Model
 * @author   Collins Harper  <ch@collinsharper.com>
 * @license  http://collinsharper.com Proprietary License 
 * @link     http://collinsharper.com
 */
class Collinsharper_Purolator_Model_Manifestshipment extends Mage_Core_Model_Abstract
{

    /**
     *  Constructor methoda
     *  
     *  @return void
     */
    protected function _construct()
    {
        $this->_init('purolatormodule/manifestshipment');
    }

}
