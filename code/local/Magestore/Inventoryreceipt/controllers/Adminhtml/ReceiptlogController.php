<?php

class Magestore_Inventoryreceipt_Adminhtml_ReceiptlogController extends Mage_Adminhtml_Controller_Action
{

    protected function _initAction() {
        $this->loadLayout()
            ->_setActiveMenu('inventoryplus')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Create Stock Receiving'), Mage::helper('adminhtml')->__('Create Stock Receiving'));
            $this->_title($this->__('Inventory'))
                    ->_title($this->__('Create Stock Receiving'));      
        return $this;
    }   
 
    public function indexAction() {
        $this->loadLayout()
            ->_setActiveMenu('inventoryplus')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Manage Stock Receiving'), Mage::helper('adminhtml')->__('Manage Stock Receiving'));
            $this->_title($this->__('Inventory'))
                    ->_title($this->__('Manage Stock Receiving'));     
        $this->renderLayout();
    }
        
        public function viewAction(){
                $receiptLogId = $this->getRequest()->getParam('id');
                $model  = Mage::getModel('inventoryreceipt/receiptlog')->load($receiptLogId);
                
                    $this->_title($this->__('Inventory'))
                            ->_title($this->__('View Stock Receiving'));
                
                if ($model->getId()) {
                    $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
                    if (!empty($data)) {
                        $model->setData($data);
                    }
                    Mage::register('receiptlog_data', $model);

                    $this->loadLayout();
                    $this->_setActiveMenu('inventoryreceipt');

                    $this->_addBreadcrumb(
                        Mage::helper('adminhtml')->__('Item Manager'),
                        Mage::helper('adminhtml')->__('Item Manager')
                    );
                    $this->_addBreadcrumb(
                        Mage::helper('adminhtml')->__('Item News'),
                        Mage::helper('adminhtml')->__('Item News')
                    );

                    $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
                    $this->_addContent($this->getLayout()->createBlock('inventoryreceipt/adminhtml_receiptlog_edit'))
                        ->_addLeft($this->getLayout()->createBlock('inventoryreceipt/adminhtml_receiptlog_edit_tabs'));

                    $this->renderLayout();
                } else {
                    Mage::getSingleton('adminhtml/session')->addError(
                        Mage::helper('inventoryreceipt')->__('Item does not exist')
                    );
                    $this->_redirect('*/*/');
                }
        }
        
        public function productsAction() {
            $this->loadLayout();
            $this->getLayout()->getBlock('receiptlog.edit.tab.products')
                    ->setProducts($this->getRequest()->getPost('receiptlog_products', null));
            
            $this->renderLayout();
            if (Mage::getModel('admin/session')->getData('receipt_product_import')) {
                Mage::getModel('admin/session')->setData('receipt_product_import', null);
            }
        }

        public function productsGridAction() {
            $this->loadLayout();
            $this->getLayout()->getBlock('receiptlog.edit.tab.products')
                    ->setProducts($this->getRequest()->getPost('receiptlog_products', null));
            $this->renderLayout();
        }

    
    public function exportCsvAction()
    {
        $fileName   = 'inventory_receipt_log.csv';
        $content    = $this->getLayout()->createBlock('inventoryreceipt/adminhtml_receiptlog_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName   = 'inventory_receipt_log.xml';
        $content    = $this->getLayout()->createBlock('inventoryreceipt/adminhtml_receiptlog_grid')
            ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }
    
    public function exportProductCsvAction()
    {
        $fileName   = 'inventory_receipt_product_log.csv';
        $content    = $this->getLayout()->createBlock('inventoryreceipt/adminhtml_receiptlog_edit_tab_products')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportProductXmlAction()
    {
        $fileName   = 'inventory_receipt_product_log.xml';
        $content    = $this->getLayout()->createBlock('inventoryreceipt/adminhtml_receiptlog_edit_tab_products')
            ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream')
    {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK','');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename='.$fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }
    
    public function saveAction() {
        
        $admin = Mage::getSingleton('admin/session')->getUser();
        
        if ($data = $this->getRequest()->getPost()) {   
            
            $model = Mage::getModel('inventoryplus/warehouse')->getCollection()->getFirstItem();
            
            try {
                $confirm = $this->getRequest()->getParam('confirm');
                $cancel = $this->getRequest()->getParam('cancel');
                
                $resource = Mage::getSingleton('core/resource');
                $writeConnection = $resource->getConnection('core_write');
    //            $installer = Mage::getModel('core/resource_setup');
                $sqlNews = array();
                $sqlOlds = '';
                $countSqlOlds = 0;
                $productsHistory = array();
                $warehouseProductDeleteds = '';
                $changeProductQtys = array();
                $warehouseId = Mage::getModel('inventoryplus/warehouse')->getCollection()->getFirstItem()->getId();
                //save products
                if (isset($data['receiptlog_products']) && !empty($data['receiptlog_products'])) {
                    $status = 1;
                    if($confirm)
                        $status = 2;
                    if($cancel)
                        $status = 3;
                    $receiptLog = Mage::getModel('inventoryreceipt/receiptlog')->load($this->getRequest()->getParam('id'));                          
                                $receiptLog->setContent($data['reason'])
                                            ->setStatus($status)
                                            ->save();
                    if($cancel){                    
                        Mage::getSingleton('adminhtml/session')->addSuccess(                      
                                    Mage::helper('inventoryplus')->__('Stock Receiving has been successfully cancelled.'));
                        
                        Mage::getSingleton('adminhtml/session')->setFormData(false);                            
                        $this->_redirect('*/*/');
                        return;                      
                    }
                
                    if($confirm){
                        $model->setUpdatedBy($admin->getUserName());
                        $model->setUpdatedAt(now());
                        $model->save();
                    }
                    $warehouseProducts = array();
                    $warehouseProductsExplodes = explode('&', urldecode($data['receiptlog_products']));
                    
                    if (count($warehouseProductsExplodes) <= 900) {
                        parse_str(urldecode($data['receiptlog_products']), $warehouseProducts);
                    } else {
                        foreach ($warehouseProductsExplodes as $warehouseProductsExplode) {
                            $warehouseProduct = '';
                            parse_str($warehouseProductsExplode, $warehouseProduct);
                            $warehouseProducts = $warehouseProducts + $warehouseProduct;
                        }
                    }
                    
                    if (count($warehouseProducts) > 0) {  
                        $deletes = array_keys($warehouseProducts);
                        $deleteModel = Mage::getModel('inventoryreceipt/receiptlog_product')->getCollection()
                                            ->addFieldToFilter('receipt_log_id',$receiptLog->getId())
                                            ->addFieldToFilter('product_id',array('nin'=>$deletes));
                        if($deleteModel->getSize()){
                            foreach($deleteModel as $k){
                                $k->delete();
                            }
                        }
                             
                        foreach ($warehouseProducts as $pId => $enCoded) {
                            $product = Mage::getModel('catalog/product')->load($pId);
                            $qtyStockObject = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
                            $isDecimal = $qtyStockObject->getIsQtyDecimal();
                            $codeArr = array();
                            parse_str(base64_decode($enCoded), $codeArr);

                            if(!$isDecimal){
                                $codeArr['receipt_qty'] = (int) $codeArr['receipt_qty'];
                            } else {
                                $codeArr['receipt_qty'] = (float) $codeArr['receipt_qty'];
                            }
                            //Zend_Debug::Dump($codeArr['receipt_qty']);die();           
                            $receiveProduct = Mage::getModel('inventoryreceipt/receiptlog_product')
                                ->getCollection()
                                ->addFieldToFilter('product_id',$pId)
                                ->addFieldToFilter('receipt_log_id',$receiptLog->getId())
                                ->getFirstItem();
                            if($receiveProduct->getId()){
                                $receiveProduct
                                ->setReceiptQty($codeArr['receipt_qty'])
                                ->save();
                            } else {
                                Mage::getModel('inventoryreceipt/receiptlog_product')
                                    ->setData('receipt_log_id',$receiptLog->getId())
                                    ->setData('warehouse_id',$warehouseId)
                                    ->setData('product_id',$pId)
                                    ->setReceiptQty($codeArr['receipt_qty'])
                                    ->save();
                            }
                            //Zend_Debug::Dump($codeArr['receipt_qty']);die();
                            if($this->getRequest()->getParam('confirm')){
                                //save qty warehouse       
                                $warehouseProductItem = Mage::getModel('inventoryplus/warehouse_product')
                                    ->getCollection()
                                    ->addFieldToFilter('warehouse_id', $warehouseId)
                                    ->addFieldToFilter('product_id', $pId)
                                    ->getFirstItem();
                                $warehouseProductItem
                                    ->setTotalQty($warehouseProductItem->getTotalQty()+$codeArr['receipt_qty'])
                                    ->setAvailableQty($warehouseProductItem->getAvailableQty()+$codeArr['receipt_qty'])
                                    ->save();
                                //save system qty
                                $currentQty =  $qtyStockObject->getQty();
                                $newQty = $currentQty + $codeArr['receipt_qty'];
                                $qtyStockObject->setQty($newQty)->save();
                            }
                        }
                    } else {
                            $deleteSql = 'DELETE FROM '.$resource->getTableName('inventoryreceipt/receiptlog_product'). " WHERE (receipt_log_id = " . $receiptLog->getId() . ")";
                            $writeConnection->query($deleteSql);
                    }
                } else {
                    Mage::getSingleton('adminhtml/session')->addError(                      
                        Mage::helper('inventoryplus')->__('Cannot save stock receiving with no product')
                    );
                    if($this->getRequest()->getParam('back')){                           
                        $this->_redirect('*/*/view', array('id' => $this->getRequest()->getParam('id')));
                    } else {
                        $this->_redirect("inventoryreceiptadmin/adminhtml_receiptlog/index");
                    }
                    return;  
                }
                if($confirm){
                    Mage::getSingleton('adminhtml/session')->addSuccess(                      
                            Mage::helper('inventoryplus')->__('Stock has been successfully adjusted.',$model->getWarehouseName())
                    );        
                }else{
                    Mage::getSingleton('adminhtml/session')->addSuccess(                      
                            Mage::helper('inventoryplus')->__('Stock Receiving has been successfully saved.'));
                }
                Mage::getSingleton('adminhtml/session')->setFormData(false); 
                if($this->getRequest()->getParam('back')){                           
                    $this->_redirect('*/*/view', array('id' => $this->getRequest()->getParam('id')));
                } else {
                    $this->_redirect("inventoryreceiptadmin/adminhtml_receiptlog/index");
                }
                return;                               
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('inventoryplus')->__('Unable to find warehouse to save!')
        );
        $this->_redirect('*/*/');
    }
        
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('inventoryplus');
    }
}