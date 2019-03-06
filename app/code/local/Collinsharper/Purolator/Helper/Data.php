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
 * Collinsharper_Purolator_Helper_Data
 *
 * @category Shipping
 * @package  Collinsharper.Purolator
 * @author   Collins Harper  <ch@collinsharper.com>
 * @license  http://collinsharper.com Proprietary License
 * @link     http://collinsharper.com
 */
class Collinsharper_Purolator_Helper_Data extends Mage_Core_Helper_Abstract
{

    /**
     * Get current customer session
     *
     * @return Object
     */
    public function getSession()
    {
        return Mage::getSingleton('customer/session');
    }

    /**
     * Get config value for the module
     *
     * @param String $key Configuration key
     *
     * @return type
     */
    public function getConfig($key)
    {
        return Mage::getStoreConfig('carriers/purolatormodule/' . $key);
    }

    /**
     * Is debugging enabled
     *
     * @return boolean
     */
    public function isDebug()
    {
        return $this->getConfig('debug') == 1;
    }

    /**
     * Get Shipping Method object
     *
     * @return Object
     */
    public function getShippingModule()
    {
        return Mage::getSingleton('purolatormodule/carrier_shippingmethod');
    }

    /**
     * Address validation
     *
     * @return boolean
     */
    public function getAddressvalidation()
    {
        return (bool) Mage::getStoreConfig('carriers/purolatormodule/active') == 1;
    }

    /**
     * Module status
     *
     * @return boolean
     */
    public function isActive()
    {
        return (bool) Mage::getStoreConfig('carriers/purolatormodule/active');
    }

    /**
     * Test address
     *
     * @param stdClass $_address Address object for validation
     *
     * @return boolean
     */
    public function testAddress($_address)
    {
        if ($this->isActive()) {
            return $this->getAddressvalidation()->testAddress($_address);
        }
    }

    /**
     * Validate response object
     *
     * @param stdClass $response Response object
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function checkResponse($response)
    {
        if (!empty($response->ResponseInformation->Errors->Error)) {


            $message = "";
            foreach ($response->ResponseInformation->Errors->Error as $error) {
                if (property_exists($error, "Description")) {
                    $message .= $error->Description . "<br/>";
                }
            }
            if ($this->isDebug()) {
                mage::log($response, "1", "purolator.exception.log");
                $session = Mage::getSingleton('adminhtml/session');
                $session->addError(Mage::Helper('purolatormodule')->__($message));
            }

            throw new Exception($message);
        }

        if (!empty($response->ResponseInformation->Errors->Error->Code)) {
            throw new Exception($response->ResponseInformation->Errors->Error->Code . "-" . $response->ResponseInformation->Errors->Error->Description);
        }
    }

    /**
     * Return user flash message
     *
     * @param Exception $e       Exception object
     * @param boolean   $isDebug Config setting for debugging enabled or not
     *
     * @return String
     */
    public function getUserMessage($e, $isDebug)
    {
        if ($isDebug) {
            return 'There was an error with Purolator. This is normally caused by a misconfiguration in Magento or your environment. Here is the error: ' . $e;
        } else {
            return 'No quotes available at the moment. Contact us ' . Mage::getStoreConfig('trans_email/ident_support/email');
        }
    }

    public function formatDate($date)
    {
        $format = Mage::getStoreConfig('carriers/purolatormodule/date_format');

        switch ($format) {
            case 'full':
                return date('l, F j, Y', strtotime($date));
            case 'long':
                return date('F j, Y', strtotime($date));
            case 'medium':
                return date('M j, Y', strtotime($date));
            case 'short':
                return date('n/j/y', strtotime($date));
        }

        return $date;
    }

    /**
     * Convert Special characters to API acceptable format.
     *
     * @param String $txt text with special characters
     *
     * @return String
     */
    public function transliterateString($txt)
    {
        $transliterationTable = array('á' => 'a', 'Á' => 'A', 'à' => 'a', 'À' => 'A', 'ă' => 'a', 'Ă' => 'A', 'â' => 'a', 'Â' => 'A', 'å' => 'a', 'Å' => 'A', 'ã' => 'a', 'Ã' => 'A', 'ą' => 'a', 'Ą' => 'A', 'ā' => 'a', 'Ā' => 'A', 'ä' => 'ae', 'Ä' => 'AE', 'æ' => 'ae', 'Æ' => 'AE', 'ḃ' => 'b', 'Ḃ' => 'B', 'ć' => 'c', 'Ć' => 'C', 'ĉ' => 'c', 'Ĉ' => 'C', 'č' => 'c', 'Č' => 'C', 'ċ' => 'c', 'Ċ' => 'C', 'ç' => 'c', 'Ç' => 'C', 'ď' => 'd', 'Ď' => 'D', 'ḋ' => 'd', 'Ḋ' => 'D', 'đ' => 'd', 'Đ' => 'D', 'ð' => 'dh', 'Ð' => 'Dh', 'é' => 'e', 'É' => 'E', 'è' => 'e', 'È' => 'E', 'ĕ' => 'e', 'Ĕ' => 'E', 'ê' => 'e', 'Ê' => 'E', 'ě' => 'e', 'Ě' => 'E', 'ë' => 'e', 'Ë' => 'E', 'ė' => 'e', 'Ė' => 'E', 'ę' => 'e', 'Ę' => 'E', 'ē' => 'e', 'Ē' => 'E', 'ḟ' => 'f', 'Ḟ' => 'F', 'ƒ' => 'f', 'Ƒ' => 'F', 'ğ' => 'g', 'Ğ' => 'G', 'ĝ' => 'g', 'Ĝ' => 'G', 'ġ' => 'g', 'Ġ' => 'G', 'ģ' => 'g', 'Ģ' => 'G', 'ĥ' => 'h', 'Ĥ' => 'H', 'ħ' => 'h', 'Ħ' => 'H', 'í' => 'i', 'Í' => 'I', 'ì' => 'i', 'Ì' => 'I', 'î' => 'i', 'Î' => 'I', 'ï' => 'i', 'Ï' => 'I', 'ĩ' => 'i', 'Ĩ' => 'I', 'į' => 'i', 'Į' => 'I', 'ī' => 'i', 'Ī' => 'I', 'ĵ' => 'j', 'Ĵ' => 'J', 'ķ' => 'k', 'Ķ' => 'K', 'ĺ' => 'l', 'Ĺ' => 'L', 'ľ' => 'l', 'Ľ' => 'L', 'ļ' => 'l', 'Ļ' => 'L', 'ł' => 'l', 'Ł' => 'L', 'ṁ' => 'm', 'Ṁ' => 'M', 'ń' => 'n', 'Ń' => 'N', 'ň' => 'n', 'Ň' => 'N', 'ñ' => 'n', 'Ñ' => 'N', 'ņ' => 'n', 'Ņ' => 'N', 'ó' => 'o', 'Ó' => 'O', 'ò' => 'o', 'Ò' => 'O', 'ô' => 'o', 'Ô' => 'O', 'ő' => 'o', 'Ő' => 'O', 'õ' => 'o', 'Õ' => 'O', 'ø' => 'oe', 'Ø' => 'OE', 'ō' => 'o', 'Ō' => 'O', 'ơ' => 'o', 'Ơ' => 'O', 'ö' => 'oe', 'Ö' => 'OE', 'ṗ' => 'p', 'Ṗ' => 'P', 'ŕ' => 'r', 'Ŕ' => 'R', 'ř' => 'r', 'Ř' => 'R', 'ŗ' => 'r', 'Ŗ' => 'R', 'ś' => 's', 'Ś' => 'S', 'ŝ' => 's', 'Ŝ' => 'S', 'š' => 's', 'Š' => 'S', 'ṡ' => 's', 'Ṡ' => 'S', 'ş' => 's', 'Ş' => 'S', 'ș' => 's', 'Ș' => 'S', 'ß' => 'SS', 'ť' => 't', 'Ť' => 'T', 'ṫ' => 't', 'Ṫ' => 'T', 'ţ' => 't', 'Ţ' => 'T', 'ț' => 't', 'Ț' => 'T', 'ŧ' => 't', 'Ŧ' => 'T', 'ú' => 'u', 'Ú' => 'U', 'ù' => 'u', 'Ù' => 'U', 'ŭ' => 'u', 'Ŭ' => 'U', 'û' => 'u', 'Û' => 'U', 'ů' => 'u', 'Ů' => 'U', 'ű' => 'u', 'Ű' => 'U', 'ũ' => 'u', 'Ũ' => 'U', 'ų' => 'u', 'Ų' => 'U', 'ū' => 'u', 'Ū' => 'U', 'ư' => 'u', 'Ư' => 'U', 'ü' => 'ue', 'Ü' => 'UE', 'ẃ' => 'w', 'Ẃ' => 'W', 'ẁ' => 'w', 'Ẁ' => 'W', 'ŵ' => 'w', 'Ŵ' => 'W', 'ẅ' => 'w', 'Ẅ' => 'W', 'ý' => 'y', 'Ý' => 'Y', 'ỳ' => 'y', 'Ỳ' => 'Y', 'ŷ' => 'y', 'Ŷ' => 'Y', 'ÿ' => 'y', 'Ÿ' => 'Y', 'ź' => 'z', 'Ź' => 'Z', 'ž' => 'z', 'Ž' => 'Z', 'ż' => 'z', 'Ż' => 'Z', 'þ' => 'th', 'Þ' => 'Th', 'µ' => 'u', 'а' => 'a', 'А' => 'a', 'б' => 'b', 'Б' => 'b', 'в' => 'v', 'В' => 'v', 'г' => 'g', 'Г' => 'g', 'д' => 'd', 'Д' => 'd', 'е' => 'e', 'Е' => 'e', 'ё' => 'e', 'Ё' => 'e', 'ж' => 'zh', 'Ж' => 'zh', 'з' => 'z', 'З' => 'z', 'и' => 'i', 'И' => 'i', 'й' => 'j', 'Й' => 'j', 'к' => 'k', 'К' => 'k', 'л' => 'l', 'Л' => 'l', 'м' => 'm', 'М' => 'm', 'н' => 'n', 'Н' => 'n', 'о' => 'o', 'О' => 'o', 'п' => 'p', 'П' => 'p', 'р' => 'r', 'Р' => 'r', 'с' => 's', 'С' => 's', 'т' => 't', 'Т' => 't', 'у' => 'u', 'У' => 'u', 'ф' => 'f', 'Ф' => 'f', 'х' => 'h', 'Х' => 'h', 'ц' => 'c', 'Ц' => 'c', 'ч' => 'ch', 'Ч' => 'ch', 'ш' => 'sh', 'Ш' => 'sh', 'щ' => 'sch', 'Щ' => 'sch', 'ъ' => '', 'Ъ' => '', 'ы' => 'y', 'Ы' => 'y', 'ь' => '', 'Ь' => '', 'э' => 'e', 'Э' => 'e', 'ю' => 'ju', 'Ю' => 'ju', 'я' => 'ja', 'Я' => 'ja');
        $txt = str_replace(array_keys($transliterationTable), array_values($transliterationTable), $txt);
        return $txt;
    }

}

