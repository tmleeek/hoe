<?php

class Magestore_Inventoryreceipt_Adminhtml_ReceiptController extends Mage_Adminhtml_Controller_Action
{

    protected function _initAction() {
        $this->loadLayout()
            ->_setActiveMenu('inventoryplus')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Add Stock Receiving'), Mage::helper('adminhtml')->__('Add Stock Receiving'));
        
        return $this;
    }   
 
    public function indexAction() {
        $this->_title($this->__('Inventory'))
                    ->_title($this->__('Add Stock Receiving'));

       $warehouseId = Mage::getModel('inventoryplus/warehouse')->getCollection()->getFirstItem()->getId();
       
       $model = Mage::getModel('inventoryplus/warehouse')->load($warehouseId);
      
       if ($model->getId() || $warehouseId == 0) {
           $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
           if (!empty($data)) {
               $model->setData($data);
           }
           Mage::register('receipt_warehouse_data', $model);

           $this->loadLayout();
           $this->_setActiveMenu('inventoryplus');
           
           $this->_addBreadcrumb(
                   Mage::helper('adminhtml')->__('Manage receipt'), Mage::helper('adminhtml')->__('Create Stock Receiving')
           );
           

           $this->getLayout()->getBlock('head')->setCanLoadExtJs(true)
                   ->removeItem('js', 'mage/adminhtml/grid.js')
                   ->addItem('js', 'magestore/adminhtml/inventory/grid.js');
           
           $this->_addContent($this->getLayout()->createBlock('inventoryreceipt/adminhtml_inventoryreceipt_edit'))
                   ->_addLeft($this->getLayout()->createBlock('inventoryreceipt/adminhtml_inventoryreceipt_edit_tabs'));           
           $this->renderLayout();
       } else {
           Mage::getSingleton('adminhtml/session')->addError(
                   Mage::helper('inventoryplus')->__('receipt does not exist!')
           );
           $this->_redirect('*/*/');
       }
    }
        
        public function importproductAction() {
         
        if (isset($_FILES['fileToUpload']['name']) && $_FILES['fileToUpload']['name'] != '') {
            try {
                $types = explode('.',$_FILES['fileToUpload']['name']);
                $fileType = $types[count($types)-1];
                if($fileType != 'csv'){             
                    echo Mage::helper('inventoryplus')->__("Wrong file's format");
                    return;    
                }
                $fileName = $_FILES['fileToUpload']['tmp_name'];
                $Object = new Varien_File_Csv();
                $dataFile = $Object->getData($fileName);
                $receiptstockProduct = array();
                $receiptstockProducts = array();
                $fields = array();
                $count = 0;
                $receiptstockHelper = Mage::helper('inventoryreceipt');
                $url = Mage::getUrl('inventoryreceiptadmin/adminhtml_receipt/index');
                if (count($dataFile)){
                    foreach ($dataFile as $col => $row) {                    
                        if ($col == 0) {
                            if (count($row))
                                foreach ($row as $index => $cell)
                                    $fields[$index] = (string) $cell;
                        }elseif ($col > 0) {
                            if (count($row))
                                foreach ($row as $index => $cell) {
                                    if (isset($fields[$index])) {
                                        $receiptstockProduct[$fields[$index]] = $cell;
                                    }
                                }
                            $receiptstockProducts[] = $receiptstockProduct;
                        }
                    }
                } else if(count($dataFile) == 0) {                
                    echo Mage::helper('inventoryreceipt')->__("File is empty");
                    return;
                }
                if(count($receiptstockProducts) > 0){   
                    $receiptstockHelper->importProduct($receiptstockProducts);
                } else {         
                    echo Mage::helper('inventoryreceipt')->__("Wrong information's structure");
                    return;
                }
            } catch (Exception $e) {
                
            }
        }
    }
        
        public function productsAction() {
            $this->loadLayout();
            $this->getLayout()->getBlock('receipt.edit.tab.products')
                    ->setProducts($this->getRequest()->getPost('receipt_products', null));
            $this->renderLayout();
            if (Mage::getModel('admin/session')->getData('receipt_product_import')) {
                Mage::getModel('admin/session')->setData('receipt_product_import', null);
            }
        }

        public function productsGridAction() {
            $this->loadLayout();
            $this->getLayout()->getBlock('receipt.edit.tab.products')
                    ->setProducts($this->getRequest()->getPost('receipt_products', null));
            $this->renderLayout();
        }

    public function saveAction() {
        
        $admin = Mage::getSingleton('admin/session')->getUser();
        
        if ($data = $this->getRequest()->getPost()) {   
            
            $model = Mage::getModel('inventoryplus/warehouse')->getCollection()->getFirstItem();
       
            try {
                $resource = Mage::getSingleton('core/resource');
                $writeConnection = $resource->getConnection('core_write');
                $sqlNews = array();
                $sqlOlds = '';
                $countSqlOlds = 0;
                $productsHistory = array();
                $warehouseProductDeleteds = '';
                $changeProductQtys = array();
                $warehouseId = Mage::getModel('inventoryplus/warehouse')->getCollection()->getFirstItem()->getId();
                //save products
                if (isset($data['receipt_products']) && !empty($data['receipt_products'])) {
                    $confirm = $this->getRequest()->getParam('confirm');
                    $status = 1;
                    if($confirm)
                        $status = 2;
                    $receiptLog = Mage::getModel('inventoryreceipt/receiptlog')
                        ->setData('created_by',$admin->getUsername())
                        ->setData('created_at',now())
                        ->setData('content',$data['reason'])
                        ->setData('status',$status)
                        ->save();
                    $warehouseProducts = array();
                    $warehouseProductsExplodes = explode('&', urldecode($data['receipt_products']));
                    
                    if (count($warehouseProductsExplodes) <= 900) {
                        parse_str(urldecode($data['receipt_products']), $warehouseProducts);
                    } else {
                        foreach ($warehouseProductsExplodes as $warehouseProductsExplode) {
                            $warehouseProduct = '';
                            parse_str($warehouseProductsExplode, $warehouseProduct);
                            $warehouseProducts = $warehouseProducts + $warehouseProduct;
                        }
                    }

                    if (count($warehouseProducts) > 0) {                        
                        foreach ($warehouseProducts as $pId => $enCoded) {
                            $product = Mage::getModel('catalog/product')->load($pId);
                            $qtyStockObject = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
                            $isDecimal = $qtyStockObject->getIsQtyDecimal();
                            $codeArr = array();
                            parse_str(base64_decode($enCoded), $codeArr);
                            if(!$isDecimal){
                                $codeArr['add_qty'] = (int) $codeArr['add_qty'];
                            } else {
                                $codeArr['add_qty'] = (float) $codeArr['add_qty'];
                            }           
                            Mage::getModel('inventoryreceipt/receiptlog_product')
                                ->setData('warehouse_id',$warehouseId)
                                ->setData('product_id',$pId)
                                ->setData('receipt_qty',$codeArr['add_qty'])
                                ->setData('receipt_log_id',$receiptLog->getId())
                                ->save();

                            if($this->getRequest()->getParam('confirm')){
                                $productIds = '';
                                //save qty warehouse       
                                $warehouseProductItem = Mage::getModel('inventoryplus/warehouse_product')
                                    ->getCollection()
                                    ->addFieldToFilter('warehouse_id', $warehouseId)
                                    ->addFieldToFilter('product_id', $pId)
                                    ->getFirstItem(); 
                                $warehouseProductItem
                                    ->setTotalQty($warehouseProductItem->getTotalQty()+$codeArr['add_qty'])
                                    ->setAvailableQty($warehouseProductItem->getAvailableQty()+$codeArr['add_qty'])
                                    ->save();
                                //save system qty
                                $currentQty =  $qtyStockObject->getQty();
                                $newQty = $currentQty + $codeArr['add_qty'];
                                $qtyStockObject->setQty($newQty)->save();
                            }
                        }
                    } else {
                        $deleteSql = 'DELETE FROM '.$resource->getTableName('inventoryreceipt/receiptlog_product'). " WHERE (receipt_log_id = " . $receiptLog->getId() . ")";
                        $writeConnection->query($deleteSql);
                    } 
                } else {
                    Mage::getSingleton('adminhtml/session')->addError(                      
                        Mage::helper('inventoryplus')->__('Cannot create stock receiving with no product')
                    );
                    $this->_redirect('*/*/', array('id' => $this->getRequest()->getParam('id')));
                    return;    
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(                      
                        Mage::helper('inventoryplus')->__('The Stock Receiving has been successfully submitted. It will be pending for review before the system updates Qty. received.')
                );
                if($confirm){
                    Mage::getSingleton('adminhtml/session')->addSuccess(                      
                            Mage::helper('inventoryplus')->__('Stock has been successfully adjusted.',$model->getWarehouseName())
                    );
                }                
                Mage::getSingleton('adminhtml/session')->setFormData(false);
                Mage::getModel('admin/session')->setData('receipt_product_import',null);
                if($this->getRequest()->getParam('back')){
                    $url = Mage::helper("adminhtml")->getUrl("inventoryreceiptadmin/adminhtml_receiptlog/view",array('id'=>$receiptLog->getId()));
                } else {
                    $url = Mage::helper("adminhtml")->getUrl("inventoryreceiptadmin/adminhtml_receiptlog/index");
                }
                
//                $this->_redirect('*/*/');
                header('Location: '.$url);
                exit;
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
 
    public function deleteAction() {
        if( $this->getRequest()->getParam('id') > 0 ) {
            try {
                $model = Mage::getModel('inventoryreceipt/inventoryreceipt');
                 
                $model->setId($this->getRequest()->getParam('id'))
                    ->delete();
                     
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    public function massDeleteAction() {
        $inventoryreceiptIds = $this->getRequest()->getParam('inventoryreceipt');
        if(!is_array($inventoryreceiptIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($inventoryreceiptIds as $inventoryreceiptId) {
                    $inventoryreceipt = Mage::getModel('inventoryreceipt/inventoryreceipt')->load($inventoryreceiptId);
                    $inventoryreceipt->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($inventoryreceiptIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
    
    public function massStatusAction()
    {
        $inventoryreceiptIds = $this->getRequest()->getParam('inventoryreceipt');
        if(!is_array($inventoryreceiptIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($inventoryreceiptIds as $inventoryreceiptId) {
                    $inventoryreceipt = Mage::getSingleton('inventoryreceipt/inventoryreceipt')
                        ->load($inventoryreceiptId)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($inventoryreceiptIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
  
    public function exportCsvAction()
    {
        $fileName   = 'inventoryreceipt.csv';
        $content    = $this->getLayout()->createBlock('inventoryreceipt/adminhtml_inventoryreceipt_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName   = 'inventoryreceipt.xml';
        $content    = $this->getLayout()->createBlock('inventoryreceipt/adminhtml_inventoryreceipt_grid')
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
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('inventoryplus');
    }
}