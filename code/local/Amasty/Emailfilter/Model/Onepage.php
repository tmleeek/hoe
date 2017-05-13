<?php
/**
* @author Amasty Team
* @copyright Amasty
* @package Amasty_Emailfilter
*/
class Amasty_Emailfilter_Model_Onepage extends Mage_Checkout_Model_Type_Onepage
{
    protected function _processValidateCustomer(Mage_Sales_Model_Quote_Address $address)
    {
        $result = parent::_processValidateCustomer($address);
        if (true == $result && Mage::getStoreConfig('customer/amemailfilter/forcheckout'))
        {
            if (self::METHOD_GUEST == $this->getQuote()->getCheckoutMethod() && !Mage::helper('amemailfilter')->validateEmail($address->getData('email')))
            {
                $result = array(
                    'error'   => -1,
                    'message' => Mage::helper('amemailfilter')->__('Sorry, your e-mail address is not available at this store.'),
                );
            }
        }
        return $result;
    }
}
