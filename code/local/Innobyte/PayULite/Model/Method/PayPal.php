<?php
/**
 * PayU - PayPal payment method.
 *
 * @category    Innobyte
 * @package     Innobyte_PayULite
 * @author      Bogdan Constantinescu <bogdan.constantinescu@innobyte.com>
 */

class Innobyte_PayULite_Model_Method_PayPal extends Innobyte_PayULite_Model_Method_Abstract
{
    /**
     * @var const string    Method 's code.
     */
    const METHOD_CODE = 'innobyte_payu_lite_pp';
    
    /**
     * @Override
     * @var string [a-z0-9_]     Unique internal payment method identifier.
     */
    protected $_code = self::METHOD_CODE;
    
    /**
     * Retrieve form fields.
     * 
     * @Override
     * return Varien_Object
     */
    public function getFormFields()
    {
        $data = $this->_getFormFields();
        $payMethod = Innobyte_PayULite_Model_Config::PAYU_METHOD_PAYPAL;
        $data->getFormFields()->setData('PAY_METHOD', $payMethod);
        $data->getHashFields()->setData('PAY_METHOD', $payMethod);
        $data->getFormFields()->setData(
            'ORDER_HASH',
            Mage::helper('innobyte_payu_lite')->hmacMd5($this->_getK(), $data->getHashFields()->toArray())
        );
        return $data->getFormFields();
    }
}
