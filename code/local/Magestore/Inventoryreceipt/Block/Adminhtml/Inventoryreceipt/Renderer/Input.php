<?php
/**
 * Magestore
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    Magestore
 * @package     Magestore_Inventory
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Supplier Adminhtml Block
 * 
 * @category    Magestore
 * @package     Magestore_Inventory
 * @author      Magestore Developer
 */
class Magestore_Inventoryreceipt_Block_Adminhtml_Inventoryreceipt_Renderer_Input extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input {

    protected $_values;

    /**
     * Renders grid column
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function render(Varien_Object $row)
    {
        $value = 0;
        $receiptProducts = Mage::getModel('admin/session')->getData('receipt_product_import');
        $products = array();
        
        if($receiptProducts){
            $productModel = Mage::getModel('catalog/product');
                foreach ($receiptProducts as $receiptProduct) {
                    $productId = $productModel->getIdBySku($receiptProduct['SKU']);     
                   
                    if($productId == $row->getEntityId())
                        $value = $receiptProduct['QTY'];    
                } 
        }
        
       
        $html = '<input type="text" ';
        $html .= 'name="' . $this->getColumn()->getId() . '" ';
        $html .= 'value="' . $value . '"';
        $html .= 'title="'.Mage::helper('inventoryplus')->__('Please tick first column on the box to edit').'"';
        $html .= 'class="input-text' . $this->getColumn()->getInlineCss() . '"/>';
        return $html;
    }
}