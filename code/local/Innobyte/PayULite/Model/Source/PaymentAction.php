<?php
/**
 * Payment actions source model.
 *
 * @category    Innobyte
 * @package     Innobyte_PayULite
 * @author      Bogdan Constantinescu <bogdan.constantinescu@innobyte.com>
 */

class Innobyte_PayULite_Model_Source_PaymentAction
{
    /**
     * @var array   Array with options.
     */
    protected $_options = array();
    
    
    
    /**
     * Getter method for options; implements lazy instantiation.
     * 
     * @return array
     */
    public function toOptionArray()
    {
        if (empty($this->_options)) {
            $this->_options = array(
                array(
                    'value' => Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE,
                    'label' => Mage::helper('innobyte_payu_lite')->__('Authorize Only'),
                ),
                array(
                    'value' => Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE_CAPTURE,
                    'label' => Mage::helper('innobyte_payu_lite')->__('Authorize and Capture'),
                ),
            );
        }
        return $this->_options;
    }
}
