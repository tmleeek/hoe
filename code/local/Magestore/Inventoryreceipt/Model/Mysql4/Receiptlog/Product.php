<?php

class Magestore_Inventoryreceipt_Model_Mysql4_Receiptlog_Product extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {           
        $this->_init('inventoryreceipt/receiptlog_product', 'receipt_product_id');
    }
}