<?php

class Magestore_Inventoryreceipt_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Import data for product grid
     * 
     * @return null
     */
    public function importProduct($data) {
        if (count($data)) {
            Mage::getModel('admin/session')->setData('receipt_product_import', $data);
        }
    }
}