<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2012 Amasty (http://www.amasty.com)
* @package Amasty_Xnotif
*/ 
class Amasty_Xnotif_Block_Adminhtml_Catalog_Product_Edit_Tab_Alerts_Stock extends  Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Alerts_Stock
{
    protected function _prepareColumns()
    {    
        $this->addColumn('firstname', array(
            'header'    => Mage::helper('catalog')->__('First Name'),
            'index'     => 'firstname',
            'renderer'  => 'amxnotif/adminhtml_catalog_product_edit_tab_alerts_renderer_firstName',    
        ));

        $this->addColumn('lastname', array(
            'header'    => Mage::helper('catalog')->__('Last Name'),
            'index'     => 'lastname',
            'renderer'  => 'amxnotif/adminhtml_catalog_product_edit_tab_alerts_renderer_lastName',
        ));

        $this->addColumn('email', array(
            'header'    => Mage::helper('catalog')->__('Email'),
            'index'     => 'email',
            'renderer'  => 'amxnotif/adminhtml_catalog_product_edit_tab_alerts_renderer_email',
        ));

        $this->addColumn('add_date', array(
            'header'    => Mage::helper('catalog')->__('Date Subscribed'),
            'index'     => 'add_date',
            'type'      => 'date'
        ));

        $this->addColumn('send_date', array(
            'header'    => Mage::helper('catalog')->__('Last Notification'),
            'index'     => 'send_date',
            'type'      => 'date'
        ));

        $this->addColumn('send_count', array(
            'header'    => Mage::helper('catalog')->__('Send Count'),
            'index'     => 'send_count',
        ));
        
        return Mage_Adminhtml_Block_Widget_Grid::_prepareColumns();
    }
}
  
