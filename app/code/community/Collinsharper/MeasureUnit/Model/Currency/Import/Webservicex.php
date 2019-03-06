<?php

class Collinsharper_MeasureUnit_Model_Currency_Import_Webservicex extends Mage_Directory_Model_Currency_Import_Webservicex
{
// stub class so we can accesss the converer

    public function convert($currencyFrom, $currencyTo, $retry=0)
    {
        return $this->_convert($currencyFrom, $currencyTo, $retry);
    }

}
