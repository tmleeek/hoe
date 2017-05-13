<?php
/**
 * Backend model for system config field that validates an IPs list.
 *
 * @category    Innobyte
 * @package     Innobyte_PayULite
 * @author      Bogdan Constantinescu <bogdan.constantinescu@innobyte.com>
 */

class Innobyte_PayULite_Model_System_Config_Ips extends Mage_Core_Model_Config_Data
{
    /**
     * Validate/filter IPs.
     * 
     * @Override
     * @return Innobyte_PayULite_Model_System_Config_Ips
     */
    protected function _beforeSave()
    {
        /* begin Innobyte customization */
        $ips = $this->getValue();
        if (strlen($ips)) {
            $ipsArr = array_unique(
                array_filter(
                    array_map('trim', explode(',', $ips)),
                    array($this, 'checkIp')
                )
            );
            $ips = implode(',', $ipsArr);
            if (!strlen($ips)) {
                Mage::throwException(Mage::helper('innobyte_payu_lite')->__('The IP(s) you entered are invalid.'));
            }
            $this->setValue($ips);
        }
        /* end Innobyte customization */
        return parent::_beforeSave();
    }
    
    /**
     * Checks if an IP is valid.
     * 
     * @param string $value
     * @return TRUE if ip is valid, FALSE otherwise.
     */
    protected function checkIp($value) {
        return Zend_Validate::is($value, 'Ip');
    }
}
