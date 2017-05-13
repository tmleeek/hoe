<?php
/**
 * Place form block.
 * 
 * @category    Innobyte
 * @package     Innobyte_PayULite
 * @author      Bogdan Constantinescu <bogdan.constantinescu@innobyte.com>
 */

class Innobyte_PayULite_Block_Placeform extends Mage_Core_Block_Template
{
    /**
     * @var Mage_Sales_Model_Order      Order model.
     */
    protected $_order = null;
    
    
    
    /**
     * Retrieve checkout session instance.
     * 
     * @return Mage_Checkout_Model_Session
     */
    protected function _getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

    
    
    /**
     * Getter method for order object; implements lazy instantiation.
     * 
     * @return Mage_Sales_Model_Order
     */
    protected function _getOrder()
    {
        if (is_null($this->_order)) {
            $this->_order = Mage::getModel('sales/order')
                ->loadByIncrementId($this->_getCheckout()->getLastRealOrderId());
        }
        return $this->_order;
    }

    
    
    /**
     * Retrieve form data.
     * 
     * @return array
     */
    public function getFormData()
    {
        return $this->_getOrder()->getPayment()->getMethodInstance()->getFormFields()->toArray();
    }

    
    
    /**
     * Retrieve gateway url.
     * 
     * @return string
     */
    public function getFormAction()
    {
        return $this->_getOrder()->getPayment()->getMethodInstance()->getUrl();
    }
}
