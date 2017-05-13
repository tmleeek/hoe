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
 * Warehouse Adminhtml Block
 * 
 * @category    Magestore
 * @package     Magestore_Inventory
 * @author      Magestore Developer
 */
class Magestore_Inventoryplus_Block_Adminhtml_Warehouse extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_warehouse';
        $this->_blockGroup = 'inventoryplus';
        $this->_headerText = Mage::helper('inventoryplus')->__('Manage Warehouses');
        $this->_addButtonLabel = Mage::helper('inventoryplus')->__('Add Warehouse');
        parent::__construct();        
    }
    
    public function getWarehouseHistory($id)
    {
        return Mage::getModel('inventoryplus/warehousehistory')->load($id);
    }
    
    public function getWarehoueContentByHistoryId($id)
    {
        $collection = Mage::getModel('inventoryplus/warehousehistorycontent')
                                    ->getCollection()
                                    ->addFieldToFilter('warehouse_history_id',$id);
        return $collection;
    }
}