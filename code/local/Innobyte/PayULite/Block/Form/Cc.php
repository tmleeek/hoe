<?php
/**
 * PayU - Credit Card form block.
 *
 * @category    Innobyte
 * @package     Innobyte_PayULite
 * @author      Bogdan Constantinescu <bogdan.constantinescu@innobyte.com>
 */

class Innobyte_PayULite_Block_Form_Cc extends Mage_Payment_Block_Form
{
    /**
     * Initializes stuffs.
     * 
     * @Override
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('innobyte/payu-lite/form/cc.phtml');
    }
    
    
    
    /**
     * Retrieve available credit card types.
     * 
     * @return array
     */
    public function getCcAvailableTypes()
    {
        $types = Mage::getSingleton('payment/config')->getCcTypes();
        if ($method = $this->getMethod()) {
            $availableTypes = $method->getConfigData('cctypes');
            if ($availableTypes) {
                $availableTypes = explode(',', $availableTypes);
                foreach ($types as $code => $name) {
                    if (!in_array($code, $availableTypes)) {
                        unset($types[$code]);
                    }
                }
            }
        }
        return $types;
    }
}
