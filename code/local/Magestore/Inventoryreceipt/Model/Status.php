<?php

class Magestore_Inventoryreceipt_Model_Status extends Varien_Object
{
    const STATUS_PENDING	= 1;
    const STATUS_COMPLATED	= 2;
    const STATUS_CANCELED	= 3;

    static public function getOptionArray()
    {
        return array(
            self::STATUS_PENDING    => Mage::helper('inventoryreceipt')->__('Pending'),
            self::STATUS_COMPLATED   => Mage::helper('inventoryreceipt')->__('Complete'),
            self::STATUS_CANCELED   => Mage::helper('inventoryreceipt')->__('Cancelled')
        );
    }
}