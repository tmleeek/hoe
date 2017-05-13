<?php
/**
* @author Amasty Team
* @copyright Amasty
* @package Amasty_Emailfilter
*/
class Amasty_Emailfilter_Model_Customer extends Mage_Customer_Model_Customer
{
    public function validate()
    {
        $errors = parent::validate();
        if (Mage::getStoreConfig('customer/amemailfilter/forreg') && !Mage::helper('amemailfilter')->validateEmail($this->getEmail()))
        {
            if (!is_array($errors))
            {
                $errors = array();
            }
            $errors[] = Mage::helper('amemailfilter')->__('Sorry, your e-mail address is not available at this store.');
        }
        return $errors;
    }
}
