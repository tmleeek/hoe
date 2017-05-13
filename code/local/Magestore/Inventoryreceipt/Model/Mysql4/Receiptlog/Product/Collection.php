<?php

class Magestore_Inventoryreceipt_Model_Mysql4_Receiptlog_Product_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('inventoryreceipt/receiptlog_product');
    }
}