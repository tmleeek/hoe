<?php
/**
 * PayU - Credit cards payment method.
 *
 * @category    Innobyte
 * @package     Innobyte_PayULite
 * @author      Bogdan Constantinescu <bogdan.constantinescu@innobyte.com>
 */

class Innobyte_PayULite_Model_Method_Cc extends Innobyte_PayULite_Model_Method_Abstract
{
    /**
     * @var const string    Method 's code.
     */
    const METHOD_CODE = 'innobyte_payu_lite_cc';
    
    /**
     * @Override
     * @var string [a-z0-9_]     Unique internal payment method identifier.
     */
    protected $_code = self::METHOD_CODE; 
    
    /**
     * @Override
     * @var string  Additional form block namespace.
     */
    protected $_formBlockType = 'innobyte_payu_lite/form_cc';
    
    /**
     * @Override
     * @var string  Additional info block namespace.
     */
    protected $_infoBlockType = 'innobyte_payu_lite/info_cc';
    
    
    
    /**
     * Assign data to info model instance.
     * 
     * @Override
     * @param   mixed $data
     * @return  Innobyte_PayULite_Model_Method_Cc
     */
    public function assignData($data)
    {
        if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }
        $info = $this->getInfoInstance();
        $info->setCcType($data->getCcType());
        return $this;
    }
    
    
    
    /**
     * Validates payment method information object.
     * 
     * @Override
     * @return  Innobyte_PayULite_Model_Method_Cc
     */
    public function validate()
    {
        parent::validate();
        $info = $this->getInfoInstance();
        $availableTypes = explode(',', $this->getConfigData('cctypes'));
        if (!in_array($info->getCcType(), $availableTypes)) {
            Mage::throwException(Mage::helper('innobyte_payu_lite')->__('Credit card type is not allowed for this payment method.'));
        }
        return $this;
    }
    
    
    
    /**
     * Retrieve form fields.
     * 
     * @Override
     * return Varien_Object
     */
    public function getFormFields()
    {
        $data = $this->_getFormFields();
        $payMethod = $this->getConfig()->getPayMethodByCcType($this->getOrder()->getPayment()->getCcType());
        $data->getFormFields()->setData('PAY_METHOD', $payMethod);
        $data->getHashFields()->setData('PAY_METHOD', $payMethod);
        $data->getFormFields()->setData(
            'ORDER_HASH',
            Mage::helper('innobyte_payu_lite')->hmacMd5($this->_getK(), $data->getHashFields()->toArray())
        );
        return $data->getFormFields();
    }
}
