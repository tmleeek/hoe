<?php

class Magestore_Inventoryreceipt_Model_Mysql4_Receiptlog extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {           
        $this->_init('inventoryreceipt/receiptlog', 'receipt_log_id');
    }
}