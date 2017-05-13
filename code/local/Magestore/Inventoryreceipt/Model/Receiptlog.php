<?php

class Magestore_Inventoryreceipt_Model_Receiptlog extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('inventoryreceipt/receiptlog');
    }
}