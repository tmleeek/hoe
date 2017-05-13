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
 * Inventory Adjust Stock Edit Block
 * 
 * @category     Magestore
 * @package     Magestore_Inventory
 * @author      Magestore Developer
 */
class Magestore_Inventoryreceipt_Block_Adminhtml_Receiptlog_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

    public function __construct() {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'inventoryreceipt';
        $this->_controller = 'adminhtml_receiptlog';
        $this->removeButton('delete'); 
        $this->_updateButton('save', 'onclick','saveAction()');
         $this->_addButton('saveandcontinue', array(
            'label' => Mage::helper('adminhtml')->__('Save & Edit'),
            'onclick' => 'changeSave()',
            'class' => 'save',
                ), -100);
        $warehouse = Mage::getModel('inventoryplus/warehouse')->getCollection()->getFirstItem();
        $warehouseId = $warehouse->getId();
        $id = $this->getRequest()->getParam('id');
        $receiptLog = Mage::getModel('inventoryreceipt/receiptlog')->load($id);
        $adminId = Mage::getSingleton('admin/session')->getUser()->getId();
        $warehouse = $this->getRequest()->getParam('id');
        $check = Mage::helper('inventoryplus/warehouse')->canEdit($adminId, $warehouseId);
        $canConfirm = Mage::helper('inventoryplus/warehouse')->canAdjust($adminId, $warehouseId);
        if ($check == true && $receiptLog->getStatus()==1){    
            $this->_addButton('cancel', array('label' => Mage::helper('inventoryplus')->__('Cancel'), 'class'=>'cancel', 'onclick' => 'cancelAction()') );
            if($canConfirm){    
                $this->_addButton('confirm', array('label' => Mage::helper('inventoryplus')->__('Confirm'), 'class'=>'save', 'onclick' => 'confirmAction()') );
            }
        }else{
            $this->removeButton('save'); 
            $this->removeButton('saveandcontinue'); 
        }
        $this->removeButton('reset'); 
       
        $this->_formScripts[] = "
            
            function validateQty(){
                var adjustFields = document.getElementsByName('receipt_qty');
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
            
             function cancelAction(){
                var r=confirm('".Mage::helper('inventoryplus')->__('Are you sure you want to cancel this stock receiving?')."');  
                if (r==true){
                    editForm.submit($('edit_form').action+'cancel/1/');
                }
            }
            

        ";
    }

    /**
     * get text to show in header when edit an item
     *
     * @return string
     */
    public function getHeaderText() {
        if (Mage::registry('receiptlog_data')
                && Mage::registry('receiptlog_data')->getId()
        ) {
            return Mage::helper('inventoryplus')->__("View Stock Receiving No. '%s'", $this->htmlEscape(Mage::registry('receiptlog_data')->getId())
            );
        }
        return Mage::helper('inventoryplus')->__('Add Stock Receiving');
    }

}