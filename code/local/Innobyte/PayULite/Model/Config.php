<?php
/**
 * Config model. Don 't forget to set method 's code and store 's id before using it.
 *
 * @category    Innobyte
 * @package     Innobyte_PayULite
 * @author      Bogdan Constantinescu <bogdan.constantinescu@innobyte.com>
 */

class Innobyte_PayULite_Model_Config extends Mage_Payment_Model_Config
{
    /**
     * @var int|null    Store 's id.
     */
    protected $_storeId = null;

    /**
     *
     * @var string      Method 's code.
     */
    protected $_methodCode = '';

    /**
     * @var const string    Constants for PayU gateway pay methods.
     */
    const PAYU_METHOD_CCVISAMC = 'CCVISAMC';
    const PAYU_METHOD_CCJCB    = 'CCJCB';
    const PAYU_METHOD_CCDINERS = 'CCDINERS';
    const PAYU_METHOD_PAYPAL   = 'PAYPAL';
    
    /**
     * @var const string  General settings sys config XML prefix path.
     */
    const GENERAL_SETTINGS_PATH = 'Innobyte_PayULite/general_settings/';
    
    /**
     * @var array   Allowed languages on PayU website.
     */
    protected $_allowedLanguages = array('EN', 'RO', 'FR', 'DE', 'IT', 'ES', 'BG', 'HU', 'RU', 'TR');
    
    
    
    /**
     * Setter method for method code.
     * 
     * @param   string      Method 's code.
     * @return  Innobyte_PayULite_Model_Config
     */
    public function setMethodCode($methodCode)
    {
        if (is_string($methodCode)) {
            $this->_methodCode = $methodCode;
        }
        return $this;
    }

    
    
    /**
     * Getter method for method code.
     * 
     * @return string
     */
    public function getMethodCode()
    {
        return $this->_methodCode;
    }

    
    
    /**
     * Setter method for store id.
     * 
     * @param   int|null    $storeId      Store 's id.
     * @return  Innobyte_PayULite_Model_Config
     */
    public function setStoreId($storeId = null)
    {
        if (is_numeric($storeId)) {
            $this->_storeId = (int) $storeId;
        } else {
            $this->_storeId = null;
        }
        return $this;
    }

    
    
    /**
     * Getter method for store id.
     * 
     * @return int|null
     */
    public function getStoreId()
    {
        return $this->_storeId;
    }
    
    
    
    /**
     * Return PayU config information.
     * 
     * @param   string  $field      Field to get config value for.
     * @return  mixed
     */
    public function getConfigData($field)
    {
        return Mage::getStoreConfig('payment/' . $this->getMethodCode() . '/' . $field, $this->getStoreId());
    }
    
    
    
    /**
     * Return PayU config information as boolean.
     * 
     * @param   string  $field      Field to get config value for.
     * @return  boolean
     */
    public function getConfigDataFlag($field)
    {
        return Mage::getStoreConfigFlag('payment/' . $this->getMethodCode() . '/' . $field, $this->getStoreId());
    }

    
    
    /**
     * Retrieve merchant 's code.
     * 
     * @return string
     */
    public function getMerchantCode()
    {
        return Mage::getStoreConfig(self::GENERAL_SETTINGS_PATH . 'merchant_code', $this->getStoreId());
    }

    
    
    /**
     * Retrieve transaction key (used in HMAC MD5 signature).
     * 
     * @return string
     */
    public function getTransactionKey()
    {
        return Mage::getStoreConfig(self::GENERAL_SETTINGS_PATH . 'trans_key', $this->getStoreId());
    }

    
    
    /**
     * Retrieve live update url.
     * 
     * @return string
     */
    public function getLuUrl()
    {
        return Mage::getStoreConfig(self::GENERAL_SETTINGS_PATH . 'lu_url', $this->getStoreId());
    }

    
    
    /**
     * Retrieve IDN url.
     * 
     * @return string
     */
    public function getIdnUrl()
    {
        return Mage::getStoreConfig(self::GENERAL_SETTINGS_PATH . 'idn_url', $this->getStoreId());
    }

    
    
    /**
     * Retrieve IRN url.
     * 
     * @return string
     */
    public function getIrnUrl()
    {
        return Mage::getStoreConfig(self::GENERAL_SETTINGS_PATH . 'irn_url', $this->getStoreId());
    }
    
    
    
    /**
     * Retrieve IOS url.
     * 
     * @return string
     */
    public function getIosUrl()
    {
        return Mage::getStoreConfig(self::GENERAL_SETTINGS_PATH . 'ios_url', $this->getStoreId());
    }

    
    
    /**
     * Retrieve payment action.
     * 
     * @return string
     */
    public function getPaymentAction()
    {
        return $this->getConfigData('payment_action', $this->getStoreId());
    }
    
    
    
    /**
     * Retreive payment timeout.
     * 
     * @return false|int    False if no payment timeout was set in config, otherwise the config value.
     */
    public function getPaymentTimeout()
    {
        $returnValue = false;
        $configValue = Mage::getStoreConfig(self::GENERAL_SETTINGS_PATH . 'payment_timeout', $this->getStoreId());
        if (is_numeric($configValue) && $configValue > 0) {
            $returnValue = (int) $configValue;
        }
        return $returnValue;
    }
    
    
    
    /**
     * Retreive language code.
     * 
     * @return string
     */
    public function getLanguage()
    {
        $returnValue = Mage::getStoreConfig(self::GENERAL_SETTINGS_PATH . 'language', $this->getStoreId());
        if (strlen($returnValue) != 2) {
            if ($this->getStoreId() != Mage::app()->getStore()->getId()) {
                Mage::app()->getLocale()->emulate($this->getStoreId());
                $returnValue = strtoupper(Mage::app()->getLocale()->getLocale()->getLanguage());
                Mage::app()->getLocale()->revert();
            } else {
                $returnValue = strtoupper(Mage::app()->getLocale()->getLocale()->getLanguage());
            }
        }
        if (!in_array($returnValue, $this->_allowedLanguages)) {
            $returnValue = 'EN';
        }
        return $returnValue;
    }
    

    
    /**
     * Retrieve return url. You should replace the ORDER_ID placeholder.
     * 
     * @return string
     */
    public function getReturnUrl()
    {
        return Mage::getUrl('payulite/processing/return', array('_secure' => true, 'order' => '{{ORDER_ID}}'));
    }

    
    
    /**
     * Retrieve cancel payment url. You should replace the ORDER_ID placeholder.
     * 
     * @return string
     */
    public function getCancelUrl()
    {
        return Mage::getUrl('payulite/processing/cancel', array('_secure' => true, 'order' => '{{ORDER_ID}}'));
    }
    
    
    
    /**
     * Check if access to IPN url is restricted.
     * 
     * @return boolean
     */
    public function isIpnUrlAccessRestricted()
    {
        return Mage::getStoreConfigFlag(self::GENERAL_SETTINGS_PATH . 'restrictaccess', $this->getStoreId());
    }

    
    
    /**
     * Retreive IPs that are allowed to access the IPN url.
     * 
     * @return array
     */
    public function getAllowedIpsForIpn()
    {
        return explode(',', Mage::getStoreConfig(self::GENERAL_SETTINGS_PATH . 'allowedips', $this->getStoreId()));
    }
    
    
    
    /**
     * Retrieve automode flag.
     * 
     * @return boolean
     */
    public function isAutomode()
    {
        return $this->getConfigDataFlag('automode', $this->getStoreId());
    }
    
    
    
    /**
     * Retrieve test mode flag.
     * 
     * @return boolean
     */
    public function isTestmode()
    {
        return $this->getConfigDataFlag('test', $this->getStoreId());
    }
    
    
    
    /**
     * Return list of supported credit card types by PayU.
     * 
     * @return array
     */
    public function getCcTypesAsOptionArray()
    {
        $model = Mage::getModel('payment/source_cctype')
            ->setAllowedTypes(array('VI', 'MC', 'JCB', 'DINCLB'));
        return $model->toOptionArray();
    }
    
    
    
    /**
     * Retrieve PayU pay methods by cc type.
     * 
     * @param   string  $ccTypeCode    Cc type code.
     * @return  string
     * @throws  Mage_Core_Exception
     */
    public function getPayMethodByCcType($ccTypeCode)
    {
        $returnValue = '';
        switch ($ccTypeCode) {
            case 'VI':
            case 'MC':
                $returnValue = self::PAYU_METHOD_CCVISAMC;
                break;
            case 'JCB':
                $returnValue = self::PAYU_METHOD_CCJCB;
                break;
            case 'DINCLB':
                $returnValue = self::PAYU_METHOD_CCDINERS;
                break;
            default:
                Mage::throwException(Mage::helper('innobyte_payu_lite')->__('CC type not supported by PayU.'));
        }
        return $returnValue;
    }
    
    
    
    /**
     * Retrieve adapter for api requests.
     * 
     * @return  string  One of the Zend_Http_Client adapters.
     */
    public function getMakeApiCallAdapter()
    {
        $returnValue = 'Zend_Http_Client_Adapter_Curl'; // default value
        $configValue = strval(Mage::getConfig()->getNode('stores/default/innobyte_payu_lite_make_api_call_adapter'));
        if (strlen($configValue)
            && class_exists($configValue)
            && $configValue instanceof Zend_Http_Client_Adapter_Interface) {
            $returnValue = $configValue;
        }
        return $returnValue;
    }
    
    
    
    /**
     * Retrieve timeout for api requests.
     * 
     * @return  int     Timeout expressed in seconds.
     */
    public function getMakeApiCallTimeout()
    {
        $returnValue = 40; // default value
        $configValue = strval(Mage::getConfig()->getNode('stores/default/innobyte_payu_lite_make_api_call_timeout'));
        if (is_numeric($configValue) && $configValue >= 0) {
            $returnValue = intval($configValue);
        }
        return $returnValue;
    }
}
