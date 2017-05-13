<?php
class Magestore_Inventoryreceipt_Block_Adminhtml_Receiptlog extends Mage_Adminhtml_Block_Widget_Grid_Container
{
   public function __construct()
    {
        $this->_controller = 'adminhtml_receiptlog';
        $this->_blockGroup = 'inventoryreceipt';
        $this->_headerText = Mage::helper('inventoryreceipt')->__('Manage Stock Receiving');
        
        parent::__construct();
        
        $admin = Mage::getSingleton('admin/session')->getUser();
        $adminId = $admin->getId();
        $warehouseId = Mage::getModel('inventoryplus/warehouse')->getCollection()->getFirstItem()->getId();
        if($warehouseId){
           $canEdit = Mage::helper('inventoryplus/warehouse')->canEdit($adminId,$warehouseId);
        }
        if($canEdit){
            $this->_addButton('add', array(
                'label'     => Mage::helper('inventoryplus')->__('Add Stock Receiving'),
                'onclick'   => 'setLocation(\'' .Mage::helper("adminhtml")->getUrl("inventoryreceiptadmin/adminhtml_receipt", array("_secure" => Mage::app()->getStore()->isCurrentlySecure())) .'\')',
                'class'     => 'add',
            ));
        }
    }
}