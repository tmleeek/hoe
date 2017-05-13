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
 * @package     Magestore_Inventorysupplyneeds
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventoryreports Observer Model
 * 
 * @category    Magestore
 * @package     Magestore_Inventoryreports
 * @author      Magestore Developer
 */
class Magestore_Inventoryreceipt_Model_Observer
{
   
    
    /**
     * process inventory_menu_list event
     *
     * @return Magestore_Inventoryreceipt_Model_Observer
     */
    public function inventoryMenu($observer) {
        
        $menu = $observer->getEvent()->getMenus()->getMenu();        
        $menu['receipt'] = array('label' => Mage::helper('inventoryreceipt')->__('Receive Stock'),
            'sort_order' => 20,
            'class' => '',
            'url' => Mage::helper("adminhtml")->getUrl("inventoryreceiptadmin/adminhtml_receiptlog", array("_secure" => Mage::app()->getStore()->isCurrentlySecure())),
            'active' => (in_array(Mage::app()->getRequest()->getRouteName(),array('inventoryreceipt'))) ? true : false,
            'level' => 0           
        );

        $observer->getEvent()->getMenus()->setData('menu', $menu);
    }
}