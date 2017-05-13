<?php

class Magestore_Inventoryreceipt_Block_Adminhtml_Inventoryreceipt_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct() {
        parent::__construct();
              
        
        $this->_objectId = 'id';
        $this->_blockGroup = 'inventoryreceipt';
        $this->_controller = 'adminhtml_inventoryreceipt';
                
        $admin = Mage::getSingleton('admin/session')->getUser();
        $this->_updateButton('save', 'onclick','saveAction()');
        $this->_removeButton('delete');
        $this->_updateButton('back', 'onclick','backReceiptAction()');
        $this->_addButton('confirm', array('label' => Mage::helper('inventoryplus')->__('Confirm'), 'class'=>'save', 'onclick' => 'confirmAction()') );
        // $this->_removeButton('back');        
        $warehouseId = Mage::getModel('inventoryplus/warehouse')->getCollection()->getFirstItem()->getId();
        if($warehouseId){
                if(!Mage::helper('inventoryplus/warehouse')->canEdit($admin->getId(), $this->getRequest()->getParam('id')))
                    $this->_removeButton('save');
        }
        $this->_addButton('saveandcontinue', array(
            'label' => Mage::helper('adminhtml')->__('Save & Edit'),
            'onclick' => 'changeSave()',
            'class' => 'save',
                ), -100);
        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('inventory_content') == null)
                    tinyMCE.execCommand('mceAddControl', false, 'inventory_content');
                else
                    tinyMCE.execCommand('mceRemoveControl', false, 'inventory_content');
            }        
            
            function backReceiptAction(){
                setLocation('". Mage::helper("adminhtml")->getUrl("inventoryreceiptadmin/adminhtml_receiptlog", array("_secure" => Mage::app()->getStore()->isCurrentlySecure()))."');
            }



            function validateQty(){
                var adjustFields = document.getElementsByName('add_qty');
                for(var i = 0;i<adjustFields.length;i++){
                    var el = adjustFields[i];
                    if(!el.disabled && el.tagName == 'INPUT'){
                        if(!el.value || el.value < 0){
                            var messageDiv = document.getElementById('messages');
                            messageDiv.innerHTML = '';
                            var ulMessage = document.createElement('UL');
                            ulMessage.className = 'messages';
                            var liMessage = document.createElement('LI');
                            liMessage.className = 'error-msg';
                            var textnode = document.createTextNode('".Mage::helper('inventoryplus/adjuststock')->__("Receive Qty. does not accept negative values or blank. Please enter a valid value.")."');
                            liMessage.appendChild(textnode);
                            ulMessage.appendChild(liMessage);
                            messageDiv.appendChild(ulMessage);
                            return false;
                        } 
                    } 
                }
                return true;
            }

            function saveAction(){
                var validate = validateQty();
                if(validate){
                    editForm.submit($('edit_form').action);
                }
            }

            function changeSave(){
                var validate = validateQty();
                if(validate){
                    editForm.submit($('edit_form').action+'back/edit/');
                }  
            }
            
            function confirmAction(){
                var validate = validateQty();
                if(validate){
                    if(editForm.validate()) {
                        var r=confirm('".Mage::helper('inventoryplus')->__('Are you sure you want to confirm this stock receiving? Qty. of products in the system will be increased by the Qty. received instantly.')."');  
                        if (r==true){
                            editForm.submit($('edit_form').action+'confirm/1/');
                        }
                    }
                }
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
                }
                var fd = new FormData();
                fd.append('fileToUpload', document.getElementById('fileToUpload').files[0]);
                fd.append('form_key', document.getElementById('form_key').value);
                var xhr = new XMLHttpRequest();
                xhr.onreadystatechange=function()
                {
                    if (xhr.readyState==4 && xhr.status==200)
                    {
                        if(xhr.responseText != ''){
                            alert(xhr.responseText);
                        }
                    }
                }
                xhr.upload.addEventListener('progress', uploadProgress, false);
                xhr.addEventListener('load', uploadComplete, false);
                xhr.addEventListener('error', uploadFailed, false);
                xhr.addEventListener('abort', uploadCanceled, false);
                xhr.open('POST', '" . $this->getUrl('inventoryreceiptadmin/adminhtml_receipt/importproduct') . "');
                xhr.send(fd);
            }

            function uploadProgress(evt) {

            }

            function uploadComplete(evt) {
                reason = $('reason').value;
                $('inventoryreceipt_tabs_product_section').addClassName('notloaded');
                  
               inventoryreceipt_tabsJsTabs.showTabContent($('inventoryreceipt_tabs_product_section'));
                setTimeout(function(){
                     $('reason').value = reason;
                },1500);
               
            }

            function uploadFailed(evt) {
                alert('There was an error attempting to upload the file.');
            }

            function uploadCanceled(evt) {
                alert('The upload has been cancelled by the user or the browser dropped the connection.');
            }
        ";        
    }

    /**
     * get text to show in header when edit an item
     *
     * @return string
     */
    public function getHeaderText() {        
        return Mage::helper('inventoryplus')->__('Add Stock Receiving');
    }
}