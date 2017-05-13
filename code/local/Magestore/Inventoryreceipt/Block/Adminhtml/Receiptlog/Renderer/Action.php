<?php
    class Magestore_Inventoryreceipt_Block_Adminhtml_Receiptlog_Renderer_Action
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row) 
    {
        $html = '';
        $status = $row->getStatus();
        $receipt_log_id = $row->getReceiptLogId();
        if($status == 1){
            $html = '<a href="'.$this->getUrl('inventoryreceiptadmin/adminhtml_receiptlog/view',array('id'=>$receipt_log_id)).'">'.Mage::helper('inventoryplus')->__('Edit').'</a>';
        }else{
            $html = '<a href="'.$this->getUrl('inventoryreceiptadmin/adminhtml_receiptlog/view',array('id'=>$receipt_log_id)).'">'.Mage::helper('inventoryplus')->__('View').'</a>';
        }
        return $html;
    }
}
?>
