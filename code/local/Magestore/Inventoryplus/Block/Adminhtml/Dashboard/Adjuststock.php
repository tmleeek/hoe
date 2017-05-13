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
class Magestore_Inventoryplus_Block_Adminhtml_Dashboard_Adjuststock extends Mage_Adminhtml_Block_Template
{
 
    
    public function __construct()
    {        
        parent::__construct();
        $this->setTemplate('inventoryplus/dashboard/adjuststock.phtml');       
    }
    
    protected function _prepareLayout()
    {       
        parent::_prepareLayout();
    }
    
    public function getAdjustStockCollection(){
        $collection = Mage::getModel('inventoryplus/adjuststock')->getCollection()->setOrder('adjuststock_id','DESC')->setPageSize(10);
        return $collection;
    }
    
   
}