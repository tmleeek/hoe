<?php
/**
* @author Amasty Team
* @copyright Amasty
* @package Amasty_Xnotif
*/
class Amasty_Xnotif_Block_Adminhtml_Catalog_Product_Edit_Tab_Alerts_Renderer_Email extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    
   public function render(Varien_Object $row)
   {
       if(!$row->getEntityId()) {
             $row->setEmail($row->getGuestEmail());
       }
       echo $row->getEmail();
   }
}