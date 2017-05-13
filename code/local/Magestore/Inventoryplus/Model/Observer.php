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
 * Inventory Observer Model
 * 
 * @category    Magestore
 * @package     Magestore_Inventory
 * @author      Magestore Developer
 */
class Magestore_Inventoryplus_Model_Observer {
    /**
     * process controller_action_predispatch event
     *
     * @return Magestore_Inventory_Model_Observer
     */
    public function controllerActionPredispatch($observer) {
        $refreshCache = false;
  
        if (Mage::helper('core')->isModuleEnabled('Magestore_Standardinventory')) {
           
            $this->disableModule('Magestore_Standardinventory');
            $template = file_get_contents(Mage::getBaseDir('etc') . DS . 'modules'.DS.'Magestore_Standardinventory.xml');
            $standardInventory = Mage::getBaseDir('etc') . DS . 'modules'. DS .'Magestore_Standardinventory.xml';
            if ($template) {
                $template = str_replace('true', 'false', $template);
            }
            chmod($standardInventory, 0777);
            file_put_contents($standardInventory, $template);
            
            $refreshCache = true;
        }

        if (Mage::helper('core')->isModuleEnabled('Magestore_Inventory')) {
            // set permission for warehouse
            $checkUpdate = Mage::getModel('inventoryplus/checkupdate')->getCollection()->addFieldToFilter('inserted_data',0)->getFirstItem();
            if($checkUpdate->getId()){

                $admins = Mage::getModel('admin/user')->getCollection();
          
                
                $warehouseCollection = Mage::getModel('inventoryplus/warehouse')->getCollection()
                                                ->addFieldToFilter('status',1);

                foreach ($warehouseCollection as $warehouse) {
                    try{
                        foreach($admins as $admin){
                           
                            if($warehouse->getIsUnwarehouse()){
                                $checkPermissionExists = Mage::getModel('inventoryplus/warehouse_permission')->getCollection()
                                        ->addFieldToFilter('admin_id',$admin->getId())
                                        ->addFieldToFilter('warehouse_id',$warehouse->getId())
                                        ->getFirstItem();
                                if($checkPermissionExists->getId()){
                                    try{
                                        $checkPermissionExists->setCanEditWarehouse(1)
                                                ->setCanAdjust(1)
                                                ->save();
                                    }catch(Exception $e){
                                        Mage::log($e->getMessage(), null, 'inventory_installation.log');
                                    }
                                }else{
                                    try{
                                        Mage::getModel('inventoryplus/warehouse_permission')
                                                ->setData('warehouse_id',$warehouse->getId())
                                                ->setData('admin_id',$admin->getId())
                                                ->setData('can_edit_warehouse',1)
                                                ->setData('can_adjust',1)
                                                ->save();
                                    }catch(Exception $e){
                                        Mage::log($e->getMessage(), null, 'inventory_installation.log');
                                    }
                                }
                            }else{
                                $checkPermissionExists = Mage::getModel('inventoryplus/warehouse_permission')->getCollection()
                                        ->addFieldToFilter('admin_id',$admin->getId())
                                        ->addFieldToFilter('warehouse_id',$warehouse->getId())
                                        ->getFirstItem();
                                
                                 $warehousePermission = Mage::getModel('inventory/assignment')->getCollection()
                                                        ->addFieldToFilter('warehouse_id',$warehouse->getId())
                                                        ->addFieldToFilter('admin_id',$admin->getId())
                                                        ->getFirstItem();
                                 if($checkPermissionExists->getId()){
                                    try{
                                        $checkPermissionExists->setCanEditWarehouse($warehousePermission->getCanEditWarehouse())
                                                                ->setCanAdjust($warehousePermission->getCanAdjust())
                                                                ->save();
                                    }catch(Exception $e){
                                        Mage::log($e->getMessage(), null, 'inventory_installation.log');
                                    }
                                }else{
                                    try{
                                        Mage::getModel('inventoryplus/warehouse_permission')
                                                ->setData('warehouse_id',$warehouse->getId())
                                                ->setData('admin_id',$admin->getId())
                                                ->setData('can_edit_warehouse',$warehousePermission->getCanEditWarehouse())
                                                ->setData('can_adjust',$warehousePermission->getCanAdjust())
                                                ->save();
                                     }catch(Exception $e){
                                        Mage::log($e->getMessage(), null, 'inventory_installation.log');
                                    }
                                }
                            }
                        }
                        
                    } catch (Exception $e) {
                            Mage::log($e->getMessage(), null, 'inventory_installation.log');
                    }
                }
                try{
                    $checkUpdate->setInsertedData(1)->save();
                } catch (Exception $e) {
                            Mage::log($e->getMessage(), null, 'inventory_installation.log');
                    }

                $refreshCache = true;
            }

            $this->disableModule('Magestore_Inventory');
            $templateInventory = file_get_contents(Mage::getBaseDir('etc') . DS . 'modules'.DS.'Magestore_Inventory.xml');
            $inventory = Mage::getBaseDir('etc') . DS . 'modules'.DS.'Magestore_Inventory.xml';
 
            if ($templateInventory) {
                $templateInventory = str_replace('true', 'false', $templateInventory);
            }
            chmod($inventory, 0777);
            file_put_contents($inventory, $templateInventory);
            
			if (Mage::helper('core')->isModuleEnabled('Magestore_Inventorydropship')) {
                  $version = Mage::getConfig()->getModuleConfig("Magestore_Inventorydropship")->version;
                  
                  if(trim($version)=='0.1.1')
                  {
                      try{
                        $this->disableModule('Magestore_Inventorydropship');
                        $templateDropship = file_get_contents(Mage::getBaseDir('etc') . DS . 'modules'.DS.'Magestore_Inventorydropship.xml');
                        $dropship = Mage::getBaseDir('etc') . DS . 'modules'. DS .'Magestore_Inventorydropship.xml';
                        if ($templateDropship) {
                                $templateDropship = str_replace('true', 'false', $templateDropship);
                        }
                        chmod($dropship, 0777);
                        file_put_contents($dropship, $templateDropship);

                        $adminHTMLDropship = Mage::getBaseDir('code') . DS . 'local'. DS .'Magestore'. DS .'Inventorydropship'. DS .'etc'. DS .'adminhtml.xml';
                        chmod($adminHTMLDropship, 0777);
                        unlink($adminHTMLDropship);
                        $refreshCache = true;
                      }catch(Exception $e){
                          Mage::log($e->getMessage(), null, 'inventory_installation.log');
                      }
                }
                                   
			}
			
            $refreshCache = true;
        }
        //refresh cache
        if($refreshCache){
            $types = array('config','layout','block_html','collections','eav','translate');
            foreach ($types as $type) {
                Mage::app()->getCacheInstance()->cleanType($type);
                Mage::dispatchEvent('adminhtml_cache_refresh_type', array('type' => $type));                
            }
        }
    }

    public function afterLoginAdminUser($observer){
        $user = $observer->getUser();
        $adminId = $user->getId();
        $warehouse = Mage::getModel('inventoryplus/warehouse')->getCollection()
                ->getFirstItem();
        $permissionCollection = Mage::getModel('inventoryplus/warehouse_permission')->getCollection()
                ->addFieldToFilter('admin_id',$adminId);
        if(count($permissionCollection) == 0){
            $permission = Mage::getModel('inventoryplus/warehouse_permission');
            $permission->setData('warehouse_id', $warehouse->getId())
                    ->setData('admin_id', $adminId)
                    ->setData('can_edit_warehouse', 1)
                    ->setData('can_adjust', 1);
            try {
                $permission->save();
            } catch (Exception $e) {

            }
        }
    }

    /**
     * disable Module 
     * 
     */
    protected function disableModule($module) {
        $section = 'advanced';
        $groups = array();
        foreach (Mage::app()->getWebsites() as $website) {
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                foreach ($stores as $store) {
                    $groups['modules_disable_output']['fields'][$module] = array('value' => 1);
                    
                    try {
                        Mage::getSingleton('adminhtml/config_data')
                                ->setSection($section)
                                ->setWebsite($website->getCode())
                                ->setStore($store->getCode())
                                ->setGroups($groups)
                                ->save();
                    } catch (Exception $e) {
                        Mage::log($e->getMessage(), null, 'inventory_installation.log');
                    }
                }
            }
        }
        $groups['modules_disable_output']['fields'][$module] = array('value' => 1);
        try{
            Mage::getSingleton('adminhtml/config_data')
                    ->setSection($section)
                    ->setWebsite(null)
                    ->setStore(null)
                    ->setGroups($groups)
                    ->save();
        } catch (Exception $e) {
                        Mage::log($e->getMessage(), null, 'inventory_installation.log');
        }
    }

    /**
     * process catalog_product_save_before event
     *
     * @return Magestore_Inventory_Model_Observer
     */
    //get old qty of product before update
    public function catalogProductSaveBefore($observer) {
        if (Mage::helper('core')->isModuleEnabled('Magestore_Inventorywarehouse'))
            return;
        if (Mage::registry('INVENTORY_CORE_PRODUCT_SAVE_BEFORE'))
            return;
        Mage::register('INVENTORY_CORE_PRODUCT_SAVE_BEFORE', true);
        $product = $observer->getProduct();
        if (in_array($product->getTypeId(), array('configurable', 'bundle', 'grouped')))
            return;
        $qty = 0;
        if ($product->getId()) {
            $item = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
            $qty = $item->getQty();
        }
        Mage::getModel('admin/session')->setData('inventory_before_update_product_item', $qty);
    }

    /**
     * process catalog_product_save_after event
     *
     * @return Magestore_Inventory_Model_Observer
     */
    //update qty product for warehouse when product change
    public function catalogProductSaveAfter($observer) {
        if (Mage::helper('core')->isModuleEnabled('Magestore_Inventorywarehouse'))
            return;
        if (Mage::registry('INVENTORY_CORE_PRODUCT_SAVE_AFTER'))
            return;
        Mage::register('INVENTORY_CORE_PRODUCT_SAVE_AFTER', true);
        $product = $observer->getProduct();
        if (in_array($product->getTypeId(), array('configurable', 'bundle', 'grouped')))
            return;
        $oldQty = 0;
        if (Mage::getModel('admin/session')->getData('inventory_before_update_product_item')) {
            $oldQty = Mage::getModel('admin/session')->getData('inventory_before_update_product_item');
            Mage::getModel('admin/session')->setData('inventory_before_update_product_item', null);
        }
        $item = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
        $newQty = $item->getQty();
        $addQtyMore = $newQty - $oldQty;
        $warehouseId = Mage::getModel('inventoryplus/warehouse')->getCollection()->getFirstItem()->getId();
        $warehouseProduct = Mage::getModel('inventoryplus/warehouse_product')
                ->getCollection()
                ->addFieldToFilter('warehouse_id', $warehouseId)
                ->addFieldToFilter('product_id', $product->getId())
                ->getFirstItem();
        try {
            if ($warehouseProduct->getId()) {
                if ($addQtyMore != '0') {
                    $totalQty = $warehouseProduct->getTotalQty();
                    $availableQty = $warehouseProduct->getTotalQty();
                    $warehouseProduct->setTotalQty($totalQty + $addQtyMore)
                            ->setAvailableQty($availableQty + $addQtyMore)
                            ->save();
                }
            } else {
                $warehouseProduct = Mage::getModel('inventoryplus/warehouse_product');
                $warehouseProduct->setData('warehouse_id', $warehouseId)
                        ->setData('product_id', $product->getId())
                        ->setTotalQty($newQty)
                        ->setAvailableQty($newQty)
                        ->save();
            }
        } catch (Exception $e) {
            Mage::log($e->getMessage(), null, 'inventory_management.log');
        }
    }

    //minus qty available warehouse
    public function salesOrderPlaceAfter($observer) {
   
        if (Mage::helper('core')->isModuleEnabled('Magestore_Inventorywarehouse'))
            return;
        if (Mage::registry('INVENTORY_CORE_ORDER_PLACE'))
            return;
        Mage::register('INVENTORY_CORE_ORDER_PLACE', true);
        $order = $observer->getOrder();
        $items = $order->getAllItems();
        $warehouseIds = null;
        $warehouseId = Mage::getModel('inventoryplus/warehouse')->getCollection()->getFirstItem()->getId();
        if (!$warehouseId) {
            Mage::log($observer->getOrder(), null, 'inventory_management.log');
            return;
        }
        foreach ($items as $item) {
            $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($item->getProductId());  
            $manageStock = $stockItem->getManageStock();
            if($stockItem->getUseConfigManageStock()){
               $manageStock = Mage::getStoreConfig('cataloginventory/item_options/manage_stock',Mage::app()->getStore()->getStoreId());                                        
            }
            if(!$manageStock){
                continue;
            }
            
            if (in_array($item->getProductType(), array('configurable', 'bundle', 'grouped')))
                continue;
            $qtyOrdered = 0;
            if (!$item->getQtyOrdered() || $item->getQtyOrdered() == 0) {
                if ($item->getParentItemId()) {
                    $qtyOrdered = Mage::getModel('sales/order_item')->load($item->getParentItemId())->getQtyOrdered();
                }
            } else {
                $qtyOrdered = $item->getQtyOrdered();
            }
            $warehouseProduct = Mage::getModel('inventoryplus/warehouse_product')->getCollection()
                    ->addFieldToFilter('warehouse_id', $warehouseId)
                    ->addFieldToFilter('product_id', $item->getProductId())
                    ->getFirstItem();
            $currentQty = $warehouseProduct->getAvailableQty() - $qtyOrdered;
            try {
                $warehouseProduct->setAvailableQty($currentQty)
                        ->save();
                Mage::getModel('inventoryplus/warehouse_order')->setOrderId($order->getId())
                        ->setWarehouseId($warehouseId)
                        ->setProductId($item->getProductId())
                        ->setItemId($item->getId())
                        ->setQty($qtyOrdered)
                        ->save();
            } catch (Exception $e) {
                Mage::log($e->getMessage(), null, 'inventory_management.log');
            }
        }
    }

    /**
     * process sales_order_shipment_save_after event
     *
     * @return Magestore_Inventory_Model_Observer
     */
    //create shipment
    public function salesOrderShipmentSaveAfter($observer) {

        if (Mage::helper('core')->isModuleEnabled('Magestore_Inventorywarehouse'))
            return;

        if (Mage::registry('INVENTORY_CORE_ORDER_SHIPMENT'))
            return;
        Mage::register('INVENTORY_CORE_ORDER_SHIPMENT', true);
        $inventoryShipmentData = array();
        $data = Mage::app()->getRequest()->getParams();
        $shipment = $observer->getEvent()->getShipment();
        $order = $shipment->getOrder();
        $orderId = $data['order_id'];
        $shipmentId = $shipment->getId();
        $total_qty_order = $order->getTotalQtyOrdered();
        $total_qty_shipped = array();
        $total_shipped = array();
        $warehouse = Mage::getModel('inventoryplus/warehouse')->getCollection()->getFirstItem();
        if (!$warehouse->getId())
            return;
        $warehouseId = $warehouse->getId();

        foreach ($order->getAllItems() as $ordered) {
            $basePrice = $ordered->getBasePrice();
            $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($ordered->getProductId());   
            
            $manageStock = $stockItem->getManageStock();
            if($stockItem->getUseConfigManageStock()){
                $manageStock = Mage::getStoreConfig('cataloginventory/item_options/manage_stock',Mage::app()->getStore()->getStoreId());                                        
            }
            
            if(!$manageStock){
                continue;
            }
            
            if (in_array($ordered->getProductType(), array('configurable', 'bundle', 'grouped')))
                continue;

            //row_total_incl_tax
           
            if ($ordered->getParentItemId()) {//neu no co cha 
                 
                if (isset($data['shipment']['items'][$ordered->getParentItemId()])) { //neu cha no đc gán qty to ship
                   
                    $item_parrent = Mage::getModel('sales/order_item')->load($ordered->getParentItemId());
                    $options = $ordered->getProductOptions();
                    
                    if (isset($options['bundle_selection_attributes'])) {
                            $option = unserialize($options['bundle_selection_attributes']);

                            $parentQty = $data['shipment']['items'][$ordered->getParentItemId()];

                            $itemQty = (int) $option['qty'] * (int) $parentQty;

                            $inventoryShipmentData[$ordered->getItemId()]['qty'] = $itemQty;
                            $inventoryShipmentData[$ordered->getItemId()]['price'] = $basePrice;

                            if (isset($data['warehouse-shipment']['items'][$ordered->getParentItemId()]) && $data['warehouse-shipment']['items'][$ordered->getParentItemId()] != '')
                                $inventoryShipmentData[$ordered->getItemId()]['warehouse'] = $data['warehouse-shipment']['items'][$ordered->getParentItemId()];
                            else
                                $inventoryShipmentData[$ordered->getItemId()]['warehouse'] = $data['warehouse-shipment']['items'][$ordered->getItemId()];
                            $inventoryShipmentData[$ordered->getItemId()]['product_id'] = $ordered->getProductId();
                            $inventoryShipmentData[$ordered->getItemId()]['price_incl_tax'] = $ordered->getPriceInclTax();
                        }else {
                            
                            $inventoryShipmentData[$ordered->getItemId()]['qty'] = $data['shipment']['items'][$ordered->getParentItemId()];
                            $inventoryShipmentData[$ordered->getItemId()]['price'] = $item_parrent->getBasePrice();
                            if (isset($data['warehouse-shipment']['items'][$ordered->getParentItemId()]) && $data['warehouse-shipment']['items'][$ordered->getParentItemId()] != '')
                                $inventoryShipmentData[$ordered->getItemId()]['warehouse'] = $data['warehouse-shipment']['items'][$ordered->getParentItemId()];
                            else
                                $inventoryShipmentData[$ordered->getItemId()]['warehouse'] = $data['warehouse-shipment']['items'][$ordered->getItemId()];
                            $inventoryShipmentData[$ordered->getItemId()]['product_id'] = $ordered->getProductId();
                            $inventoryShipmentData[$ordered->getItemId()]['price_incl_tax'] = $ordered->getPriceInclTax();
                        }
                } else {
                    $inventoryShipmentData[$ordered->getItemId()]['qty'] = $data['shipment']['items'][$ordered->getItemId()];
                    $inventoryShipmentData[$ordered->getItemId()]['price'] = $basePrice;
                    $inventoryShipmentData[$ordered->getItemId()]['warehouse'] = $warehouseId;
                    $inventoryShipmentData[$ordered->getItemId()]['product_id'] = $ordered->getProductId();
                    $inventoryShipmentData[$ordered->getItemId()]['price_incl_tax'] = $ordered->getPriceInclTax();
                }
            } else {//neu no ko co cha
                if (!$ordered->getHasChildren()) { // va no khong co con
                    $inventoryShipmentData[$ordered->getItemId()]['qty'] = $data['shipment']['items'][$ordered->getItemId()];
                    $inventoryShipmentData[$ordered->getItemId()]['price'] = $basePrice;
                    $inventoryShipmentData[$ordered->getItemId()]['warehouse'] = $warehouseId;
                    $inventoryShipmentData[$ordered->getItemId()]['product_id'] = $ordered->getProductId();
                    $inventoryShipmentData[$ordered->getItemId()]['price_incl_tax'] = $ordered->getPriceInclTax();
                } else { //neu no co con
                    $warehouseName = $warehouse->getWarehouseName();
                    $inventoryShipmentModel = Mage::getModel('inventoryplus/warehouse_shipment');
                    $inventoryShipmentModel->setItemId($ordered->getItemId())
                            ->setProductId($ordered->getProductId())
                            ->setOrderId($orderId)
                            ->setWarehouseId($warehouseId)
                            ->setWarehouseName($warehouseName)
                            ->setShipmentId($shipmentId)
                            ->setQtyShipped(0)
                            ->save();
                }
            }
            if ($inventoryShipmentData[$ordered->getItemId()]['qty'] > ($ordered->getQtyOrdered() - $ordered->getQtyRefunded())) {
                $inventoryShipmentData[$ordered->getItemId()]['qty'] = ($ordered->getQtyOrdered() - $ordered->getQtyRefunded());
                $inventoryShipmentData[$ordered->getItemId()]['price'] = $basePrice;
            }
        }

        try {
            foreach ($inventoryShipmentData as $key => $dataArray) {
                if ($dataArray['qty'] == 0)
                    continue;
                $warehouseName = $warehouse->getWarehouseName();
                $inventoryShipmentModel = Mage::getModel('inventoryplus/warehouse_shipment');
                $inventoryShipmentModel->setItemId($key)
                        ->setProductId($dataArray['product_id'])
                        ->setOrderId($orderId)
                        ->setWarehouseId($warehouseId)
                        ->setWarehouseName($warehouseName)
                        ->setShipmentId($shipmentId)
                        ->setQtyShipped($dataArray['qty'])
                        ->setSubtotalShipped($dataArray['price'] * $dataArray['qty'])
                        ->save();

                $warehouseOrder = Mage::getModel('inventoryplus/warehouse_order')
                        ->getCollection()
                        ->addFieldToFilter('order_id', $orderId)
                        ->addFieldToFilter('item_id', $key)
                        ->addFieldToFilter('product_id', $dataArray['product_id'])
                        ->getFirstItem();
                if ($warehouseOrder->getId()) {
                    $wOQty = $warehouseOrder->getQty();
                    $warehouseOrder->setQty($wOQty - $dataArray['qty'])
                            ->save();
                }

                $warehouseProduct = Mage::getModel('inventoryplus/warehouse_product')
                        ->getCollection()
                        ->addFieldToFilter('warehouse_id', $warehouseId)
                        ->addFieldToFilter('product_id', $dataArray['product_id'])
                        ->getFirstItem();
                $oldQty = $warehouseProduct->getTotalQty();
                $newQty = $oldQty - $dataArray['qty'];
                if ($dataArray['qty'] != 0 && ($newQty < $oldQty)) {
                    $warehouseProduct->setTotalQty($newQty);
//                    $warehouseProduct->setUpdatedAt(now());
                    $warehouseProduct->save();
                }
            }
            return;
        } catch (Exception $e) {
            Mage::log($e->getMessage(), null, 'inventory_management.log');
        }
    }

    //order cancel => return qty to qty available
    public function orderCancelAfter($observer) {
        if (Mage::registry('INVENTORY_CORE_ORDER_CANCEL'))
            return;
        Mage::register('INVENTORY_CORE_ORDER_CANCEL', true);
        try {
            $order = $observer->getOrder();
            $items = $order->getAllItems();

            $parentQtyCanceled = array();
            foreach ($items as $item) {
                
                $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($item->getProductId());  
                
                $manageStock = $stockItem->getManageStock();
                if($stockItem->getUseConfigManageStock()){
                    $manageStock = Mage::getStoreConfig('cataloginventory/item_options/manage_stock',Mage::app()->getStore()->getStoreId());                                        
                }
                if(!$manageStock){
                    continue;
                }
                
                $qtyCanceled = 0;
                if (in_array($item->getProductType(), array('configurable', 'bundle', 'grouped'))) {
                    $parentQtyCanceled[$item->getId()] = $item->getQtyCanceled();
                    continue;
                }
                if ($item->getQtyCanceled() > 0) {
                    $qtyCanceled = $item->getQtyCanceled();
                } else {
                    if ($item->getParentItemId()) {
                        if ($parentQtyCanceled[$item->getParentItemId()])
                            $qtyCanceled = $parentQtyCanceled[$item->getParentItemId()];
                    }
                }
                if ($qtyCanceled > 0) {
                    $warehouseOrder = Mage::getModel('inventoryplus/warehouse_order')
                            ->getCollection()
                            ->addFieldToFilter('order_id', $order->getId())
                            ->addFieldToFilter('item_id', $item->getId())
                            ->addFieldToFilter('product_id', $item->getProductId())
                            ->getFirstItem();
                    if ($warehouseOrder->getId()) {
                        $wOQty = $warehouseOrder->getQty();
                        $warehouseOrder->setQty($wOQty - $qtyCanceled)
                                ->save();
                    }
                    $warehouseProduct = Mage::getModel('inventoryplus/warehouse_product')
                            ->getCollection()
                            ->addFieldToFilter('product_id', $item->getProductId())
                            ->addFieldToFilter('warehouse_id', $warehouseOrder->getWarehouseId())
                            ->getFirstItem();
                    if ($warehouseProduct->getId()) {
                        $availableQty = $warehouseProduct->getAvailableQty();
                        $warehouseProduct->setAvailableQty($availableQty + $qtyCanceled)
                                ->save();
                    }
                }
            }
        } catch (Exception $e) {
            Mage::log($e->getMessage(), null, 'inventory_management.log');
        }
    }

    public function salesOrderCreditmemoSaveAfter($observer) {

        if (Mage::registry('INVENTORY_WAREHOUSE_ORDER_CREDITMEMO'))
            return;
        Mage::register('INVENTORY_WAREHOUSE_ORDER_CREDITMEMO', true);

        $data = Mage::app()->getRequest()->getParams();
        $creditmemo = $observer->getCreditmemo();
        $order = $creditmemo->getOrder();
        $inventoryCreditmemoData = array();

        $order_id = $order->getId();
        $creditmemo_id = $creditmemo->getId();

        foreach ($creditmemo->getAllItems() as $creditmemo_item) {
            
            $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($creditmemo_item->getProductId());   
            $manageStock = $stockItem->getManageStock();
            if($stockItem->getUseConfigManageStock()){
               $manageStock = Mage::getStoreConfig('cataloginventory/item_options/manage_stock',Mage::app()->getStore()->getStoreId());                                        
            }
            if(!$manageStock){
                continue;
            }
            
            if (isset($data['creditmemo']['select-warehouse-supplier'][$creditmemo_item->getOrderItemId()]) && $data['creditmemo']['select-warehouse-supplier'][$creditmemo_item->getOrderItemId()] == 2) {
                continue;
            }

            $item = Mage::getModel('sales/order_item')->load($creditmemo_item->getOrderItemId());
            if (in_array($item->getProductType(), array('configurable', 'bundle', 'grouped')))
                continue;


            //row_total_incl_tax  

            if ($item->getParentItemId()) {

                if (isset($data['creditmemo']['items'][$item->getParentItemId()])) {

                    if (isset($data['creditmemo']['select-warehouse-supplier'][$item->getParentItemId()]) && $data['creditmemo']['select-warehouse-supplier'][$item->getParentItemId()] == 2) {
                        continue;
                    }

                    $item_parrent = Mage::getModel('sales/order_item')->load($item->getParentItemId());
                    $options = $item->getProductOptions();
                    if (isset($options['bundle_selection_attributes'])) {
                        $option = unserialize($options['bundle_selection_attributes']);

                        $parentQty = $data['creditmemo']['items'][$item->getParentItemId()]['qty'];
                        $inventoryCreditmemoData[$item->getItemId()]['price'] = $basePrice;
                        $qtyRefund = (int) $option['qty'] * (int) $parentQty;
                        $qtyShipped = $item->getQtyShipped();
                        $qtyRefunded = $item->getQtyRefunded();
                        $qtyOrdered = $item->getQtyOrdered();

                        $inventoryCreditmemoData[$item->getItemId()]['product'] = $item->getProductId();

                        //////////
                        //if return to stock
                        /*
                         * total qty will be updated if (qtyShipped + qtyRefunded + qtyRefund) > qtyOrdered and will be returned = (qtyShipped + qtyRefunded + qtyRefund) > qtyOrdered
                         * available qty will be returned = qtyRefund
                         */
                        $inventoryCreditmemoData[$item->getItemId()]['qty_avaiable'] = 0;
                        $inventoryCreditmemoData[$item->getItemId()]['qty_total'] = 0;
                        $inventoryCreditmemoData[$item->getItemId()]['qty_product'] = 0;
                        if (isset($data['creditmemo']['items'][$item->getParentItemId()]['back_to_stock'])) {

                            $inventoryCreditmemoData[$item->getItemId()]['warehouse'] = $data['creditmemo']['warehouse-select'][$item->getParentItemId()];
                            $inventoryCreditmemoData[$item->getItemId()]['qty_avaiable'] = $qtyRefund;
                            $qtyChecked = $qtyShipped + $qtyRefunded + $qtyRefund - $qtyOrdered;
                            if ($qtyShipped > 0) {
                                if ($qtyChecked >= 0) {
                                    $inventoryCreditmemoData[$item->getItemId()]['qty_total'] = $qtyChecked;
                                } else {
                                    $inventoryCreditmemoData[$item->getItemId()]['qty_total'] = $qtyOrdered - $qtyShipped + $qtyRefunded;
                                }
                            }

                            if ($qtyChecked >= 0) {
                                $inventoryCreditmemoData[$item->getItemId()]['qty_product'] = $qtyRefund;
                            } else {
                                $inventoryCreditmemoData[$item->getItemId()]['qty_product'] = $qtyOrdered - $qtyShipped + $qtyRefunded;
                            }
                        } else {
                            //if not return to stock
                            /*
                             * total qty will be updated = qtyShipped - min[(qtyShipped + qtyRefunded + qtyRefund),qtyOrdered]
                             * available qty not change
                             */

                            $inventoryShipmentModel = Mage::getModel('inventoryplus/warehouse_shipment')
                                    ->getCollection()
                                    ->addFieldToFilter('order_id', $order_id)
                                    ->addFieldToFilter('item_id', $item->getItemId())
                                    ->addFieldToFilter('product_id', $item->getProductId())
                                    ->getFirstItem();

                            $warehouseId = $inventoryShipmentModel->getWarehouseId();
                            if (!$warehouseId) {
                                $inventoryOrderModel = Mage::getModel('inventoryplus/warehouse_order')->getCollection()
                                        ->addFieldToFilter('order_id', $order_id)
                                        ->addFieldToFilter('item_id', $item->getItemId())
                                        ->addFieldToFilter('product_id', $item->getProductId())
                                        ->getFirstItem();
                                $warehouseId = $inventoryOrderModel->getWarehouseId();
                            }
                            $qtycheckNotShip = $qtyShipped + $qtyRefunded + $qtyRefund - $qtyOrdered;
                            if ($qtycheckNotShip <= 0) {
                                $inventoryCreditmemoData[$item->getItemId()]['qty_total'] = $qtycheckNotShip;
                            } else {
                                $inventoryCreditmemoData[$item->getItemId()]['qty_total'] = $qtyShipped + $qtyRefunded - $qtyOrdered;
                            }
//                            $inventoryCreditmemoData[$item->getItemId()]['qty_avaiable'] = $qtyRefund;
                            $inventoryCreditmemoData[$item->getItemId()]['warehouse'] = $warehouseId;
                        }

                        //////////
                    } else {

                        $qtyRefund = $data['creditmemo']['items'][$item->getParentItemId()]['qty'];
                        $parentItem = Mage::getModel('sales/order_item')->load($item->getParentItemId());
                        $qtyShipped = $parentItem->getQtyShipped();
                        $qtyRefunded = $parentItem->getQtyRefunded();
                        $qtyOrdered = $parentItem->getQtyOrdered();

                        //////////
                        //if return to stock
                        /*
                         * total qty will be updated if (qtyShipped + qtyRefunded + qtyRefund) > qtyOrdered and will be returned = (qtyShipped + qtyRefunded + qtyRefund) > qtyOrdered
                         * available qty will be returned = qtyRefund
                         */
                        $inventoryCreditmemoData[$item->getItemId()]['qty_avaiable'] = 0;
                        $inventoryCreditmemoData[$item->getItemId()]['qty_total'] = 0;
                        $inventoryCreditmemoData[$item->getItemId()]['qty_product'] = 0;

                        if (isset($data['creditmemo']['items'][$item->getParentItemId()]['back_to_stock'])) {

                            $inventoryCreditmemoData[$item->getItemId()]['qty_avaiable'] = $qtyRefund;
                            $qtyChecked = $qtyShipped + $qtyRefunded + $qtyRefund - $qtyOrdered;
                            $inventoryCreditmemoData[$item->getItemId()]['warehouse'] = $data['creditmemo']['warehouse-select'][$item->getParentItemId()];
                            if ($qtyShipped > 0) {
                                if ($qtyChecked >= 0) {
                                    $inventoryCreditmemoData[$item->getItemId()]['qty_total'] = $qtyChecked;
                                } else {
                                    $inventoryCreditmemoData[$item->getItemId()]['qty_total'] = $qtyOrdered - $qtyShipped + $qtyRefunded;
                                }
                            }

                            if ($qtyChecked >= 0) {
                                $inventoryCreditmemoData[$item->getItemId()]['qty_product'] = $qtyRefund;
                            } else {
                                $inventoryCreditmemoData[$item->getItemId()]['qty_product'] = $qtyOrdered - $qtyShipped + $qtyRefunded;
                            }
                        } else {
                            //if not return to stock
                            /*
                             * total qty will be updated = qtyShipped - min[(qtyShipped + qtyRefunded + qtyRefund),qtyOrdered]
                             * available qty not change
                             */

                            $inventoryShipmentModel = Mage::getModel('inventoryplus/warehouse_shipment')
                                    ->getCollection()
                                    ->addFieldToFilter('order_id', $order_id)
                                    ->addFieldToFilter('item_id', $item->getItemId())
                                    ->addFieldToFilter('product_id', $item->getProductId())
                                    ->getFirstItem();

                            $warehouseId = $inventoryShipmentModel->getWarehouseId();
                            if (!$warehouseId) {
                                $inventoryOrderModel = Mage::getModel('inventoryplus/warehouse_order')->getCollection()
                                        ->addFieldToFilter('order_id', $order_id)
                                        ->addFieldToFilter('item_id', $item->getItemId())
                                        ->addFieldToFilter('product_id', $item->getProductId())
                                        ->getFirstItem();
                                $warehouseId = $inventoryOrderModel->getWarehouseId();
                            }

                            $qtycheckNotShip = $qtyShipped + $qtyRefunded + $qtyRefund - $qtyOrdered;
                            if ($qtycheckNotShip <= 0) {
                                $inventoryCreditmemoData[$item->getItemId()]['qty_total'] = $qtycheckNotShip;
                            } else {
                                $inventoryCreditmemoData[$item->getItemId()]['qty_total'] = $qtyShipped + $qtyRefunded - $qtyOrdered;
                            }
//                            $inventoryCreditmemoData[$item->getItemId()]['qty_avaiable'] = $qtyRefund;
                            $inventoryCreditmemoData[$item->getItemId()]['warehouse'] = $warehouseId;
                        }


                        $inventoryCreditmemoData[$item->getItemId()]['product'] = $item->getProductId();
                    }
                } else {

                    $qtyRefund = $data['creditmemo']['items'][$item->getItemId()]['qty'];
                    $qtyShipped = $item->getQtyShipped();
                    $qtyRefunded = $item->getQtyRefunded();
                    $qtyOrdered = $item->getQtyOrdered();

                    //////////
                    //if return to stock
                    /*
                     * total qty will be updated if (qtyShipped + qtyRefunded + qtyRefund) > qtyOrdered and will be returned = (qtyShipped + qtyRefunded + qtyRefund) > qtyOrdered
                     * available qty will be returned = qtyRefund
                     */

                    $inventoryCreditmemoData[$item->getItemId()]['qty_avaiable'] = 0;
                    $inventoryCreditmemoData[$item->getItemId()]['qty_total'] = 0;
                    $inventoryCreditmemoData[$item->getItemId()]['qty_product'] = 0;
                    if (isset($data['creditmemo']['items'][$item->getItemId()]['back_to_stock'])) {
                        $inventoryCreditmemoData[$item->getItemId()]['qty_avaiable'] = $qtyRefund;

                        $qtyChecked = $qtyShipped + $qtyRefunded + $qtyRefund - $qtyOrdered;
                        if ($qtyShipped > 0) {
                            if ($qtyChecked >= 0) {
                                $inventoryCreditmemoData[$item->getItemId()]['qty_total'] = $qtyChecked;
                            } else {
                                $inventoryCreditmemoData[$item->getItemId()]['qty_total'] = $qtyOrdered - $qtyShipped + $qtyRefunded;
                            }
                        }

                        if ($qtyChecked >= 0) {
                            $inventoryCreditmemoData[$item->getItemId()]['qty_product'] = $qtyRefund;
                        } else {
                            $inventoryCreditmemoData[$item->getItemId()]['qty_product'] = $qtyOrdered - $qtyShipped + $qtyRefunded;
                        }

                        $inventoryCreditmemoData[$item->getItemId()]['warehouse'] = $data['creditmemo']['warehouse-select'][$item->getItemId()];
                    } else {
                        //if not return to stock
                        /*
                         * total qty will be updated = qtyShipped - min[(qtyShipped + qtyRefunded + qtyRefund),qtyOrdered]
                         * available qty not change
                         */
                        $inventoryShipmentModel = Mage::getModel('inventoryplus/warehouse_shipment')
                                ->getCollection()
                                ->addFieldToFilter('order_id', $order_id)
                                ->addFieldToFilter('item_id', $item->getItemId())
                                ->addFieldToFilter('product_id', $item->getProductId())
                                ->getFirstItem();

                        $warehouseId = $inventoryShipmentModel->getWarehouseId();
                        if (!$warehouseId) {
                            $inventoryOrderModel = Mage::getModel('inventoryplus/warehouse_order')->getCollection()
                                    ->addFieldToFilter('order_id', $order_id)
                                    ->addFieldToFilter('item_id', $item->getItemId())
                                    ->addFieldToFilter('product_id', $item->getProductId())
                                    ->getFirstItem();
                            $warehouseId = $inventoryOrderModel->getWarehouseId();
                        }

                        $qtycheckNotShip = $qtyShipped + $qtyRefunded + $qtyRefund - $qtyOrdered;
                        if ($qtycheckNotShip <= 0) {
                            $inventoryCreditmemoData[$item->getItemId()]['qty_total'] = $qtycheckNotShip;
                        } else {
                            $inventoryCreditmemoData[$item->getItemId()]['qty_total'] = $qtyShipped + $qtyRefunded - $qtyOrdered;
                        }
//                        $inventoryCreditmemoData[$item->getItemId()]['qty_avaiable'] = $qtyRefund;
                        $inventoryCreditmemoData[$item->getItemId()]['warehouse'] = $warehouseId;
                    }
                    $inventoryCreditmemoData[$item->getItemId()]['product'] = $item->getProductId();
                }
            } else {
                $qtyRefund = $data['creditmemo']['items'][$item->getItemId()]['qty'];
                $qtyShipped = $item->getQtyShipped();
                $qtyRefunded = $item->getQtyRefunded();
                $qtyOrdered = $item->getQtyOrdered();


                //////////
                //if return to stock
                /*
                 * total qty will be updated if (qtyShipped + qtyRefunded + qtyRefund) > qtyOrdered and will be returned = (qtyShipped + qtyRefunded + qtyRefund) > qtyOrdered
                 * available qty will be returned = qtyRefund
                 */
                $inventoryCreditmemoData[$item->getItemId()]['qty_avaiable'] = 0;
                $inventoryCreditmemoData[$item->getItemId()]['qty_total'] = 0;
                $inventoryCreditmemoData[$item->getItemId()]['qty_product'] = 0;
                if (isset($data['creditmemo']['items'][$item->getItemId()]['back_to_stock'])) {
                    $inventoryCreditmemoData[$item->getItemId()]['qty_avaiable'] = $qtyRefund;
                    $qtyChecked = $qtyShipped + $qtyRefunded + $qtyRefund - $qtyOrdered;
                    if ($qtyShipped > 0) {
                        if ($qtyChecked >= 0) {
                            $inventoryCreditmemoData[$item->getItemId()]['qty_total'] = $qtyChecked;
                        } else {
                            $inventoryCreditmemoData[$item->getItemId()]['qty_total'] = $qtyOrdered - $qtyShipped + $qtyRefunded;
                        }
                    }

                    if ($qtyChecked >= 0) {
                        $inventoryCreditmemoData[$item->getItemId()]['qty_product'] = $qtyRefund;
                    } else {
                        $inventoryCreditmemoData[$item->getItemId()]['qty_product'] = $qtyOrdered - $qtyShipped + $qtyRefunded;
                    }


                    $inventoryCreditmemoData[$item->getItemId()]['warehouse'] = $data['creditmemo']['warehouse-select'][$item->getItemId()];
                } else {
                    //if not return to stock
                    /*
                     * total qty will be updated = qtyShipped - min[(qtyShipped + qtyRefunded + qtyRefund),qtyOrdered]
                     * available qty not change
                     */
                    $inventoryShipmentModel = Mage::getModel('inventoryplus/warehouse_shipment')
                            ->getCollection()
                            ->addFieldToFilter('order_id', $order_id)
                            ->addFieldToFilter('item_id', $item->getItemId())
                            ->addFieldToFilter('product_id', $item->getProductId())
                            ->getFirstItem();
                    $warehouseId = $inventoryShipmentModel->getWarehouseId();
                    if (!$warehouseId) {
                        $inventoryOrderModel = Mage::getModel('inventoryplus/warehouse_order')->getCollection()
                                ->addFieldToFilter('order_id', $order_id)
                                ->addFieldToFilter('item_id', $item->getItemId())
                                ->addFieldToFilter('product_id', $item->getProductId())
                                ->getFirstItem();
                        $warehouseId = $inventoryOrderModel->getWarehouseId();
                    }

                    $qtycheckNotShip = $qtyShipped + $qtyRefunded + $qtyRefund - $qtyOrdered;
                    if ($qtycheckNotShip <= 0) {
                        $inventoryCreditmemoData[$item->getItemId()]['qty_total'] = $qtycheckNotShip;
                    } else {
                        $inventoryCreditmemoData[$item->getItemId()]['qty_total'] = $qtyShipped + $qtyRefunded - $qtyOrdered;
                    }


//                    $inventoryCreditmemoData[$item->getItemId()]['qty_avaiable'] = $qtyRefund;
                    $inventoryCreditmemoData[$item->getItemId()]['warehouse'] = $warehouseId;
                }

                $inventoryCreditmemoData[$item->getItemId()]['product'] = $item->getProductId();
            }
        }

        foreach ($inventoryCreditmemoData as $id => $value) {
            $product = Mage::getModel('catalog/product')->load($value['product']);
            $stockItem = $product->getStockItem();


            $warehouseProduct = Mage::getModel('inventoryplus/warehouse_product')
                    ->getCollection()
                    ->addFieldToFilter('product_id', $value['product'])
                    ->addFieldToFilter('warehouse_id', $value['warehouse'])
                    ->getFirstItem();
            if ($warehouseProduct->getId()) {
                try {
                    $stockItem->setQty((int) $stockItem->getQty() + (int) $value['qty_product'])->save();
                    $warehouseProduct->setData('total_qty', $warehouseProduct->getTotalQty() + $value['qty_total'])
                            ->setData('available_qty', $warehouseProduct->getAvailableQty() + $value['qty_avaiable'])
//                            ->setData('updated_at', now())
                            ->save();
                } catch (Exception $e) {
                    Mage::log($e->getTraceAsString(), null, 'inventory_management.log');
                }
            }


            if (Mage::helper('core')->isModuleEnabled('Magestore_Inventorywarehouse')) {
                $warehouse_name = Mage::helper('inventorywarehouse/warehouse')->getWarehouseNameByWarehouseId($value['warehouse']);
                try {
                    Mage::getModel('inventorywarehouse/warehouse_refund')
                            ->setData('warehouse_id', $value['warehouse'])
                            ->setData('creditmemo_id', $creditmemo_id)
                            ->setData('order_id', $order_id)
                            ->setData('item_id', $id)
                            ->setData('product_id', $value['product'])
                            ->setData('qty_refunded', $value['qty_avaiable'])
                            ->setData('warehouse_name', $warehouse_name)
                            ->save();
                } catch (Exception $e) {
                    Mage::log($e->getTraceAsString(), null, 'inventory_management.log');
                }
            }
        }
    }

    //order creditmemo => qty return to warehouse
    /* qtyRefund - current qty refund
     * qtyRefunded - old qty refund
     */
    public function salesOrderCreditmemoSaveAfter_backup($observer) {
        if (Mage::helper('core')->isModuleEnabled('Magestore_Inventorywarehouse'))
            return;
        if (Mage::registry('INVENTORY_CORE_ORDER_CREDITMEMO'))
            return;
        Mage::register('INVENTORY_CORE_ORDER_CREDITMEMO', true);
        try {
            $warehouse = Mage::getModel('inventoryplus/warehouse')->getCollection()->getFirstItem();
            $warehouseId = $warehouse->getId();
            $creditmemo = $observer->getCreditmemo();
            $order = $creditmemo->getOrder();
            $supplierReturn = array();
            $transactionData = array();

            $parents = array();

            
            foreach ($creditmemo->getAllItems() as $item) {
                $orderItemId = $item->getOrderItemId();
                $orderItem = Mage::getModel('sales/order_item')->load($orderItemId);
                if (in_array($orderItem->getProductType(), array('configurable', 'bundle', 'grouped'))) {
                    $parents[$orderItemId]['qtyRefund'] = $item->getQty();
                    $parents[$orderItemId]['qtyRefunded'] = $orderItem->getQtyRefunded();
                    $parents[$orderItemId]['qtyShipped'] = $orderItem->getQtyShipped();
                    continue;
                }
                if ($orderItem->getParentItemId()) {
                    $qtyRefund = $item->getQty();
                    $qtyShipped = $orderItem->getQtyShipped();
                    $creditmemoParentItem = Mage::getModel('sales/order_creditmemo_item')->getCollection()
                            ->addFieldToFilter('parent_id', $item->getParentId())
                            ->addFieldToFilter('order_item_id', $orderItem->getParentItemId())
                            ->getFirstItem();
                    if ($parents && $parents[$orderItem->getParentItemId()]['qtyRefund']) {
                        $qtyRefund = max($qtyRefund, $parents[$orderItem->getParentItemId()]['qtyRefund']);
                    }
                    if ($qtyShipped == 0 && $parents && $parents[$orderItem->getParentItemId()]['qtyShipped']) {
                        $qtyShipped = $parents[$orderItem->getParentItemId()]['qtyShipped'];
                    }
                    $qtyOrdered = $orderItem->getQtyOrdered();
                    $qtyRefunded = $orderItem->getQtyRefunded();
                    $qtyRefunded = max($qtyRefunded, $parents[$orderItem->getParentItemId()]['qtyRefunded']);
                } else {
                    $qtyRefund = $item->getQty();
                    $qtyShipped = $orderItem->getQtyShipped();
                    $qtyOrdered = $orderItem->getQtyOrdered();
                    $qtyRefunded = $orderItem->getQtyRefunded();
                }
                //if return to stock
                /*
                 * total qty will be updated if (qtyShipped + qtyRefunded + qtyRefund) > qtyOrdered and will be returned = (qtyShipped + qtyRefunded + qtyRefund) > qtyOrdered
                 * available qty will be returned = qtyRefund
                 */
                $qtyReturnAvailableQty = 0;
                $qtyReturnTotalQty = 0;
                if ($item->getBackToStock()) {
                    $qtyReturnAvailableQty = $qtyRefund;
                    $qtyChecked = $qtyShipped + $qtyRefunded + $qtyRefund - $qtyOrdered;
                    if ($qtyChecked > 0)
                        $qtyReturnTotalQty = $qtyChecked;
                }else {
                    //if not return to stock
                    /*
                     * total qty will be updated = qtyShipped - min[(qtyShipped + qtyRefunded + qtyRefund),qtyOrdered]
                     * available qty not change
                     */
                    $totalShipAndRefund = $qtyShipped + $qtyRefunded + $qtyRefund;
                    $qtyReturnTotalQty = min($totalShipAndRefund, $qtyOrdered);
                }
                $warehouseProduct = Mage::getModel('inventoryplus/warehouse_product')
                        ->getCollection()
                        ->addFieldToFilter('product_id', $item->getProductId())
                        ->addFieldToFilter('warehouse_id', $warehouseId)
                        ->getFirstItem();
                if ($warehouseProduct->getId()) {
                    $warehouseProduct->setData('total_qty', $warehouseProduct->getTotalQty() + $qtyReturnTotalQty)
                            ->setData('available_qty', $warehouseProduct->getAvailableQty() + $qtyReturnAvailableQty)
                            ->save();
                }

                $warehouseShipment = Mage::getModel('inventoryplus/warehouse_shipment')
                        ->getCollection()
                        ->addFieldToFilter('item_id', $orderItemId)
                        ->addFieldToFilter('product_id', $item->getProductId())
                        ->addFieldToFilter('warehouse_id', $warehouseId)
                        ->getFirstItem();
                if ($warehouseShipment->getId()) {
                    $warehouseShipment->setData('qty_refunded', $warehouseShipment->getQtyRefunded() + $qtyRefund)
                            ->save();
                }
            }
        } catch (Exception $e) {
            Mage::log($e->getMessage(), null, 'inventory_management.log');
        }
    }

}
