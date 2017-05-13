<?php
/**
 * Data helper.
 *
 * @category    Innobyte
 * @package     Innobyte_PayULite
 * @author      Bogdan Constantinescu <bogdan.constantinescu@innobyte.com>
 */

class Innobyte_PayULite_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Encodes a string in UTF-8.
     * 
     * @param   string $strValue  The string to encode.
     * @return  string            The string UTF-8 encoded.
     */
    public function utf8izeString($strValue)
    {
        $returnValue = strval($strValue);
        if (!mb_check_encoding($returnValue, 'UTF-8')) {
            $strValue = mb_convert_encoding($returnValue, 'UTF-8', mb_detect_encoding($returnValue));
        }
        return $returnValue;
    }
    
    
    
    /**
     * Encodes an entire array recursevly in UTF-8.
     * 
     * @param   array   $arrToEncode    The array to encode.
     * @return  array
     */
    public function utf8izeArray(array $arrToEncode)
    {
        array_walk_recursive($arrToEncode, array($this, 'utf8izeArrayRecFunction'));
        return $arrToEncode;
    }
    
    
    
    /**
     * Apply HMAC_MD5.
     * 
     * @param   array $arrData    Array with data, can contain subarrays.
     * @return  string            HMAC_MD5 signature.
     */
    public function hmacMd5($strKey, array $arrData)
    {
        $referenceObj = new stdClass;
        $referenceObj->strData = '';
        array_walk_recursive($arrData, array($this, 'hmacMd5RecFunction'), $referenceObj);
        if (version_compare(phpversion(), '5.1.2', '>=') === true) {
            return hash_hmac('md5', $referenceObj->strData, $strKey);
        }
        return bin2hex(mhash(MHASH_MD5, $referenceObj->strData, $strKey));
    }
    
    
    
    /**
     * Instead of using closure for 5.2 compatibility.
     * 
     * @param mixed $item   Value in the array to apply utf8izeArray() upon it.
     */
    protected function utf8izeArrayRecFunction(&$item)
    {
        if (!is_array($item)) {
            $item = $this->utf8izeString($item);
        }
    }
    
    
    
    /**
     * Instead of using closure for 5.2 compatibility.
     * 
     * @param mixed     $item   Value in the array to apply utf8izeArray() upon it.
     * @param mixed     $key    Value 's key.
     * @param stdClass  $obj    Object where string to hash will be stored.
     */
    protected function hmacMd5RecFunction($item, $key, stdClass $obj)
    {
        if (!is_array($item)) {
            $obj->strData .= strlen($item);//mb_strlen(strval($item), 'UTF-8');
            $obj->strData .= strval($item);
        }
    }
}
