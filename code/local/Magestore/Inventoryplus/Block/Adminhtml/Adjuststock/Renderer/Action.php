<?php
    class Magestore_Inventoryplus_Block_Adminhtml_Adjuststock_Renderer_Action
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row) 
    {
        $html = '';
        $status = $row->getStatus();
        $adjuststockId = $row->getAdjuststockId();
        if($status == 0){
            $html = '<a href="'.$this->getUrl('inventoryplusadmin/adminhtml_adjuststock/edit',array('id'=>$adjuststockId)).'">'.Mage::helper('inventoryplus')->__('Edit').'</a>';
        }else{
            $html = '<a href="'.$this->getUrl('inventoryplusadmin/adminhtml_adjuststock/edit',array('id'=>$adjuststockId)).'">'.Mage::helper('inventoryplus')->__('View').'</a>';
        }
        return $html;
    }
}
?>
