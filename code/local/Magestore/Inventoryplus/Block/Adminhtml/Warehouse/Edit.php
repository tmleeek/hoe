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
 * Warehouse Edit Block
 * 
 * @category     Magestore
 * @package     Magestore_Inventory
 * @author      Magestore Developer
 */
class Magestore_Inventoryplus_Block_Adminhtml_Warehouse_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

    public function __construct() {
        parent::__construct();
        $source = $this->getRequest()->getParam('id');       
        
        $this->_objectId = 'id';
        $this->_blockGroup = 'inventoryplus';
        $this->_controller = 'adminhtml_warehouse';        
        
        $warehouseId = $this->getRequest()->getParam('id');
        $admin = Mage::getSingleton('admin/session')->getUser();
        
        if ($warehouseId && Mage::helper('inventoryplus/warehouse')->canAdjust($admin->getId(), $warehouseId)) {
            $this->_addButton('adjuststock', array(
                'label' => Mage::helper('adminhtml')->__('Adjust Stock'),
                'onclick' => 'addAdjustStock()',
                'class' => 'add',
                    ), 0);
        }
        $this->_updateButton('save', 'label', Mage::helper('inventoryplus')->__('Save'));
        $this->_updateButton('save', 'onclick', 'saveAction()');
        $this->_updateButton('reset', 'onclick', 'changeReset()');
        $Inventoryurl = $this->getUrl('inventoryplusadmin/adminhtml_inventory/index');
        if ($this->getRequest()->getParam('inventoryplus')) {
            $this->_updateButton('back', 'onclick', 'backInventoryCheck()');
        }
        $this->_updateButton('delete', 'label', Mage::helper('inventoryplus')->__('Delete'));
        
        if ($warehouseId && !Mage::helper('inventoryplus/warehouse')->canEdit($admin->getId(), $warehouseId)) {
            $this->_removeButton('save');
            $this->_removeButton('delete');
            $this->_removeButton('reset');
        } elseif (Mage::helper('core')->isModuleEnabled('Magestore_Inventorywarehouse')) {
            $this->_addButton('saveandcontinue', array(
                'label' => Mage::helper('adminhtml')->__('Save And Continue Edit'),
                'onclick' => 'saveAndContinueEdit()',
                'class' => 'save',
                    ), -100);
        }                
        
        if (!Mage::helper('core')->isModuleEnabled('Magestore_Inventorywarehouse')) {
            $this->_removeButton('delete');
        }
 $url = $this->getUrl('inventoryplusadmin/adminhtml_warehouse/showhistory');
        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('inventory_content') == null)
                    tinyMCE.execCommand('mceAddControl', false, 'inventory_content');
                else
                    tinyMCE.execCommand('mceRemoveControl', false, 'inventory_content');
            }
            
            function backInventoryCheck(){
                if($('loading-mask')){
                    $('loading-mask').style.display = 'block';
                    $('loading-mask').style.height = '900px';                    
                    $('loading-mask').style.width = '1500px';                    
                    $('loading-mask').style.top = '0';                    
                    $('loading-mask').style.left = '-2';                    
                }
                window.location.href = '" . $Inventoryurl . "';
            }
            
            function changeReset(){
                if($('loading-mask')){
                    $('loading-mask').style.display = 'block';
                    $('loading-mask').style.height = '900px';                    
                    $('loading-mask').style.width = '1500px';                    
                    $('loading-mask').style.top = '0';                    
                    $('loading-mask').style.left = '-2';                    
                }
                setLocation(window.location.href);
            }

            function saveAction(){
//                if($('loading-mask')){
//                    $('loading-mask').style.display = 'block';
//                    $('loading-mask').style.height = '900px';                    
//                    $('loading-mask').style.width = '1500px';                    
//                    $('loading-mask').style.top = '0';                    
//                    $('loading-mask').style.left = '-2';                    
//                }
                editForm.submit();
            }
            
            function saveAndContinueEdit(){
//                if($('loading-mask')){
//                    $('loading-mask').style.display = 'block';
//                    $('loading-mask').style.height = '900px';                    
//                    $('loading-mask').style.width = '1500px';                    
//                    $('loading-mask').style.top = '0';                    
//                    $('loading-mask').style.left = '-2';                    
//                }
                editForm.submit($('edit_form').action+'back/edit/');
            }
            
            function addAdjustStock(){
                if($('loading-mask')){
                    $('loading-mask').style.display = 'block';
                    $('loading-mask').style.height = '900px';                    
                    $('loading-mask').style.width = '1500px';                    
                    $('loading-mask').style.top = '0';                    
                    $('loading-mask').style.left = '-2';                    
                }
                var url = '" . $this->getUrl('inventoryplusadmin/adminhtml_adjuststock/prepare/', array('warehouse_id' => $this->getRequest()->getParam('id'))) . "';
                window.location.href = url;
            }
                        
            function createSendstock(){
                if($('loading-mask')){
                    $('loading-mask').style.display = 'block';
                    $('loading-mask').style.height = '900px';                    
                    $('loading-mask').style.width = '1500px';                    
                    $('loading-mask').style.top = '0';                    
                    $('loading-mask').style.left = '-2';                    
                }
                var url = '" . $this->getUrl('inventoryplusadmin/adminhtml_sendstock/new', array('warehouse_id' => $this->getRequest()->getParam('id'))) . "';
                window.location.href = url;
            }
            
            function createRequeststock(){
                if($('loading-mask')){
                    $('loading-mask').style.display = 'block';
                    $('loading-mask').style.height = '900px';                    
                    $('loading-mask').style.width = '1500px';                    
                    $('loading-mask').style.top = '0';                    
                    $('loading-mask').style.left = '-2';                    
                }
                var url = '" . $this->getUrl('inventoryplusadmin/adminhtml_requeststock/new') . 'warehouse_id/' . $this->getRequest()->getParam('id') . "';
                window.location.href = url;
            }
            
            function fileSelected() {
                var file = document.getElementById('fileToUpload').files[0];
                if (file) {
                    var fileSize = 0;
                    if (file.size > 1024 * 1024)
                        fileSize = (Math.round(file.size * 100 / (1024 * 1024)) / 100).toString() + 'MB';
                    else
                        fileSize = (Math.round(file.size * 100 / 1024) / 100).toString() + 'KB';
                    document.getElementById('fileName').innerHTML = 'Name: ' + file.name;
                    document.getElementById('fileSize').innerHTML = 'Size: ' + fileSize;
                    document.getElementById('fileType').innerHTML = 'Type: ' + file.type;
                }
            }
			
            function uploadFile() {
                if(!$('fileToUpload') || !$('fileToUpload').value){
                    alert('Please choose CSV file to import!');return false;
                }
                if($('loading-mask')){
                    $('loading-mask').style.display = 'block';
                    $('loading-mask').style.height = '900px';                    
                    $('loading-mask').style.width = '1500px';                    
                    $('loading-mask').style.top = '0';                    
                    $('loading-mask').style.left = '-2';                    
                }
                var fd = new FormData();
                fd.append('fileToUpload', document.getElementById('fileToUpload').files[0]);
                fd.append('form_key', document.getElementById('form_key').value);
                var xhr = new XMLHttpRequest();
                xhr.upload.addEventListener('progress', uploadProgress, false);
                xhr.addEventListener('load', uploadComplete, false);
                xhr.addEventListener('error', uploadFailed, false);
                xhr.addEventListener('abort', uploadCanceled, false);
                xhr.open('POST', '" . $this->getUrl('inventorywarehouseadmin/adminhtml_warehouse/getImportCsv', array('source' => $source)) . "');
                xhr.send(fd);
           }

           function uploadProgress(evt) {
                if (evt.lengthComputable) {
                    var percentComplete = Math.round(evt.loaded * 100 / evt.total);
                    //document.getElementById('progressNumber').innerHTML = percentComplete.toString() + '%';
                   // document.getElementById('prog').value = percentComplete;
                }
                else {
                   // document.getElementById('progressNumber').innerHTML = 'unable to compute';
                }
           }
          
           function uploadComplete(evt) {
                var import_data = '" . Mage::getModel('admin/session')->getData('null_warehouseaddmore_product_import') . "';    
                    
                if(import_data == '1'){
                     alert('No product was added');
                }
                
                $('warehouse_tabs_addnewproduct_section').addClassName('notloaded');
                warehouse_tabsJsTabs.showTabContent($('warehouse_tabs_addnewproduct_section'));
                //varienGlobalEvents.attachEventHandler('showTab',function(){ sendstockproductGridJsObject.doFilter(); });
           }

           function uploadFailed(evt) {
                alert('There was an error attempting to upload the file.');
           }

           function uploadCanceled(evt) {
                alert('The upload has been cancelled by the user or the browser dropped the connection.');
           }        
           function showhistory(warehouseHistoryId){
                var url = '" . $this->getUrl('inventoryplusadmin/adminhtml_warehouse/showhistory') . "';               
                var warehouseHistoryId = warehouseHistoryId;                
                var url = url+'warehouseHistoryId/'+warehouseHistoryId;
                TINY.box.show(url,1, 800, 400, 1);
            }        
            
        ";

        if ($this->getRequest()->getParam('loadptab')) {
            $this->_formScripts[] = "
                warehouse_tabsJsTabs.showTabContent($('warehouse_tabs_product_section'));
            ";
        }
    }

    /**
     * get text to show in header when edit an item
     *
     * @return string
     */
    public function getHeaderText() {
        if (Mage::registry('warehouse_data') && Mage::registry('warehouse_data')->getId()
        ) {
            return Mage::helper('inventoryplus')->__("Edit Warehouse '%s'", $this->htmlEscape(Mage::registry('warehouse_data')->getWarehouseName())
            );
        }
        return Mage::helper('inventoryplus')->__('Add Warehouse');
    }

}
