<?php
/**
* @author Amasty Team
* @copyright Amasty
* @package Amasty_Emailfilter
*/
class Amasty_Emailfilter_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function validateEmail($email)
    {
        if (!Mage::getStoreConfig('customer/amemailfilter/filters'))
        {
            return true;
        }
        $filters = preg_split('/,|' . "\n" . '/', Mage::getStoreConfig('customer/amemailfilter/filters'));
        if (is_array($filters))
        {
            foreach ($filters as $filter)
            {
                $expr = '/' . trim(str_replace('*', '(.*?)',  str_replace('.', '\.', $filter))) . '/';
                if (preg_match($expr, $email))
                {
                    return false;
                }
            }
        }
        return true;
    }
}
