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
 * Inventory Helper
 * 
 * @category    Magestore
 * @package     Magestore_Inventory
 * @author      Magestore Developer
 */
class Magestore_Inventoryplus_Helper_Data extends Mage_Core_Helper_Abstract {

    /**
     * get list of country
     * @return type
     */
    public function getCountryList() {
        $result = array();
        $collection = Mage::getModel('directory/country')->getCollection();
        foreach ($collection as $country) {
            $cid = $country->getId();
            $cname = $country->getName();
            $result[$cid] = $cname;
        }
        return $result;
    }
    
    /**
     * get list of warehouse
     * @return type
     */
    public function getWarehouseList() {
        $options = array();
        $warehouses = Mage::getModel('inventoryplus/warehouse')->getCollection();
            foreach($warehouses as $warehouse){                
                $options[$warehouse->getId()] = $warehouse->getWarehouseName();
            }
       
        return $options;    
    }

    /**
     * Check is Warehouse plugin exists and enabled in global config.
     * 
     * @return boolean
     */
    public function isWarehouseEnabled() {
        return Mage::helper('core')->isModuleEnabled('Magestore_Inventorywarehouse');
    }

    /**
     * get label status
     * @return string
     */
    public function getStatusLabel($status) {
        $return = $this->__('Pending');
        if ($status == 1) {
            $return = $this->__('Completed');
        }
        if($status == 2) {
            $return = $this->__('Cancelled');
        }
        return $return;
    }

    public function filterDates($array, $dateFields) {
        if (empty($dateFields)) {
            return $array;
        }
        $filterInput = new Zend_Filter_LocalizedToNormalized(array(
            'date_format' => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT)
        ));
        $filterInternal = new Zend_Filter_NormalizedToLocalized(array(
            'date_format' => Varien_Date::DATE_INTERNAL_FORMAT
        ));

        foreach ($dateFields as $dateField) {
            if (array_key_exists($dateField, $array) && !empty($dateField)) {
                $array[$dateField] = $filterInput->filter($array[$dateField]);
                $array[$dateField] = $filterInternal->filter($array[$dateField]);
            }
        }
        return $array;
    }

    /**
     * get menu
     * 
     * @return HTML 
     */
    public function getMenu($menu, $level = 0, $showbutton = true) {
        if ((Mage::app()->getRequest()->getParam('section') == 'inventoryplus' && Mage::app()->getRequest()->getParam('section')) || !Mage::app()->getRequest()->getParam('section'))
            $html = '<ul ' . (!$level ? 'id="inventory-nav" style="display:block; width:100%; float:left"' : '') . '>' . PHP_EOL;
        else
            $html = '<ul ' . (!$level ? 'id="inventory-nav" style="display:none; width:100%; float:left"' : '') . '>' . PHP_EOL;


        
        foreach ($menu as $id => $item) {
          
            $html .= '<li ' . (!empty($item['children']) ? 'onmouseover="Element.addClassName(this,\'over\')" '
                            . 'onmouseout="Element.removeClassName(this,\'over\')"' : '') . ' class="'
                    . (!$level && !empty($item['active']) ? ' active' : $item['class']) . ' '
                    . (!empty($item['children']) ? ' parent' : '')
                    . (!empty($level) && !empty($item['last']) ? ' last' : '')
                    . ' level' . $level . '"> <a href="' .  (($item['url']!='') ? $item['url']:'javascript:void(0)') . '" '
                    . (!empty($item['title']) ? 'title="' . $item['title'] . '"' : '') . ' '
                    . (!empty($item['click']) ? 'onclick="' . $item['click'] . '"' : '') . ' class="'
                    . ($level === 0 && !empty($item['active']) ? 'active' : '') . '"><span>'
                    . $this->escapeHtml($item['label']) . '</span></a>' . PHP_EOL;

            if (isset($item['children'])) {
                //$children = new Varien_Object(array('menu'=>$item['children']));
                $html .= $this->getMenu($item['children'], $level + 1, false);
            }
            $html .= '</li>' . PHP_EOL;
        }
        $html .= '</ul>' . PHP_EOL;

        return $html;
    }

    // protected function _checkAcl($resource)
    // {
    //     try {
    //         $res =  Mage::getSingleton('admin/session')->isAllowed($resource);
    //     } catch (Exception $e) {
    //         return false;
    //     }
    //     return $res;
    // }

    // protected function _checkMenuAcl($menu,$parentKey = ''){
    //     foreach($menu as $key => $value) {
    //         $aclResource = 'admin/inventoryplus/'.$parentKey;
    //         if(is_array($value) && !array_key_exists('children', $value['children'])){
    //             $aclResource = $aclResource.$key;
    //             if(!$this->_checkAcl($aclResource)){
    //                 unset($menu[$key]);
    //             }
    //         } else {
    //             $nextLevel = $value['children'];
    //             $value['children'] = $this->_checkMenuAcl($nextLevel,$key);
    //         }
    //     }
    //     if(count($menu) <= 0){
    //         return '';
    //     }
    //     return $menu;
    // }

    /**
     * get list menu
     * 
     * @return Object 
     */
    public function getInventoryMenu() {
        $parentArr = array();
        $array = array(
            'managestock' => array('label' => $this->__('Manage Stock'),
                'sort_order' => 0,
                'class' => '',
                'url' => Mage::helper("adminhtml")->getUrl("inventoryplusadmin/adminhtml_stock/", array("_secure" => Mage::app()->getStore()->isCurrentlySecure())),
                'active' => (Mage::app()->getRequest()->getControllerName() == 'adminhtml_stock') ? true : false,
                'level' => 0),
            'adjuststock' => array('label' => $this->__('Adjust Stock'),
                'sort_order' => 40,
                'class' => '',
                'url' => Mage::helper("adminhtml")->getUrl("inventoryplusadmin/adminhtml_adjuststock/", array("_secure" => Mage::app()->getStore()->isCurrentlySecure())),
                'active' => (Mage::app()->getRequest()->getControllerName() == 'adminhtml_adjuststock') ? true : false,
                'level' => 0),
        );
        $menus = new Varien_Object(array('menu'=>$array));
      
        Mage::dispatchEvent('inventory_menu_list', array('menus' => $menus));
        
        $parentArr = $this->sortMenu($menus);          

        //$parentArr = $this->_addFullMenu($parentArr);

        uasort($parentArr, array($this, '_sortMenu'));

        return $parentArr;
    }
    

    public function sortMenu($menus){
        $parentArr = array();
       
        foreach ($menus->getMenu() as $id => $item) {
            $parentArr[$id]= $item;
            if (isset($item['children'])) {
                $childMenu = new Varien_Object(array('menu'=>$item['children']));
               
                $parentArr[$id]['children'] = $this->sortMenu($childMenu);
                uasort( $parentArr[$id]['children'], array($this, '_sortMenu'));
                
            }
        }
      
        return $parentArr;
    }
    
    protected function _sortMenu($a, $b)
    {
        return $a['sort_order']<$b['sort_order'] ? -1 : ($a['sort_order']>$b['sort_order'] ? 1 : 0);
    }

    protected function _addFullMenu($parentArr){
        //dashboard
        $parentArr['dashboard'] = array('label' => Mage::helper('inventoryplus')->__('Dashboards'),
            'sort_order' => 50,
            'class' => 'inactive',
            'click' => 'showDisableTips()',
            'url' => '',
            'active' => false,
            'level' => 0,           
        );
        //barcode
        $parentArr['barcode'] = array('label' => Mage::helper('inventoryplus')->__('Barcodes'),
            'sort_order' => 700,
            'url' => '',
            'class' => 'inactive',
            'active' => false,
            'level' => 0,
            'children' => array(
                'search_barcode' => array('label' => Mage::helper('inventoryplus')->__('Scan Barcodes'),
                    'sort_order' => 100,
                    'click' => 'showDisableTips()',
                    'url' => '',
                    'active' => false,
                    'level' => 0),
                'manage_barcode' => array('label' => Mage::helper('inventoryplus')->__('Manage Barcodes'),
                    'sort_order' => 120,
                    'click' => 'showDisableTips()',
                    'url' => '',
                    'active' => false,
                    'level' => 0)
            )
        );
        //low stock
        $parentArr['lowstock'] = array('label' => Mage::helper('inventoryplus')->__('Low Stock Notifications'),
            'sort_order' => 600,
            'url' => '',
            'class' => 'inactive',
            'active' => (in_array(Mage::app()->getRequest()->getControllerName(), array('adminhtml_notificationlog'))) ? true : false,
            'level' => 0,
            'children' => array(
                'notificationlog' => array('label' => Mage::helper('inventoryplus')->__('Notification Logs'),
                    'sort_order' => 100,
                    'click' => 'showDisableTips()',
                    'url' => '',
                    'active' => false,
                    'level' => 1),
                'settings' => array('label' => Mage::helper('inventoryplus')->__('Settings'),
                    'sort_order' => 110,
                    'click' => 'showDisableTips()',
                    'url' => '',
                    'active' => false,
                    'level' => 0)
            )
        );
        //warehouse
        $parentArr['warehouses'] = array('label' => Mage::helper('inventoryplus')->__('Warehouses'),
            'sort_order' => 100,
            'url' => '',
            'class' => 'inactive',
            'active' => (Mage::app()->getRequest()->getControllerName() == 'adminhtml_warehouse' || Mage::app()->getRequest()->getControllerName() == 'adminhtml_sendstock' || Mage::app()->getRequest()->getControllerName() == 'adminhtml_requeststock') ? true : false,
            'level' => 0,
            'children' => array(
                'warehouse' => array('label' => Mage::helper('inventoryplus')->__('Manage Warehouses'),
                    'sort_order' => 100,
                    'click' => 'showDisableTips()',
                    'url' => '',
                    'active' => false,
                    'level' => 1),
                'sendstock' => array('label' => Mage::helper('inventoryplus')->__('Send Stock'),
                    'sort_order' => 110,
                    'click' => 'showDisableTips()',
                    'url' => '',
                    'active' => false,
                    'level' => 1
                ),
                'requeststock' => array('label' => Mage::helper('inventoryplus')->__('Request Stock'),
                    'sort_order' => 120,
                    'click' => 'showDisableTips()',
                    'url' => '',
                    'active' => false,
                    'level' => 1)
            )
        );
        //purchasing
        $parentArr['supplier'] = array('label' => Mage::helper('inventoryplus')->__('Suppliers'),
            'sort_order' => 200,
            'url' => '',
            'click' => 'showDisableTips()',
            'class' => 'inactive',
            'active' => false,
            'level' => 0            
        );
        $parentArr['purchasing'] = array('label' => Mage::helper('inventoryplus')->__('Stock Purchase'),
            'sort_order' => 500,
            'url' => '',
            'class' => 'inactive',
            'active' => false,
            'level' => 0,
            'children' => array(
                'purchaseorder' => array('label' => Mage::helper('inventoryplus')->__('Purchase Order'),
                    'sort_order' => 100,
                    'click' => 'showDisableTips()',
                    'url' => '',
                    'active' => false,
                    'level' => 1),
                'shippingmethod' => array('label' => Mage::helper('inventoryplus')->__('Shipping Methods'),
                    'sort_order' => 110,
                    'click' => 'showDisableTips()',
                    'url' => '',
                    'active' => false,
                    'level' => 1
                ),
                'paymentterms' => array('label' => Mage::helper('inventoryplus')->__('Payment Terms'),
                    'sort_order' => 120,
                    'click' => 'showDisableTips()',
                    'url' => '',
                    'active' => false,
                    'level' => 1)
            )
        );
        //supply needs
        $parentArr['supplyneeds'] = array('label' => Mage::helper('inventoryplus')->__('Supply Needs'),
            'sort_order' => 600,
            'click' => 'showDisableTips()',
            'url' => '',
            'class' => 'inactive',
            'active' => false,
            'level' => 0,           
        );
        //shipment
        $parentArr['shiment'] = array('label' => Mage::helper('inventoryplus')->__('Shipments'),
            'sort_order' => 300,
            'click' => 'showDisableTips()',
            'url' => '',
            'class' => 'inactive',
            'active' => false,
            'level' => 0,           
        );
        //report
        $parentArr['reports'] = array('label' => Mage::helper('inventoryplus')->__('Reports'),
            'sort_order' => 1000,
            'url' => '',
            'class' => 'inactive',
            'active' => false,
            'level' => 0,
            'children' => array(
                'sales' => array('label' => Mage::helper('inventoryplus')->__('Sales'),
                    'sort_order' => 10,
                    'url' => '',
                    'active' => false,
                    'level' => 1,
                    'children' => array(
                        'sales_warehouse' => array('label' => Mage::helper('inventoryplus')->__('Warehouses'),
                        'sort_order' => 10,
                        'click' => 'showDisableTips()',
                        'url' => '',
                        'active' => false,
                        'level' => 2,
                        ),
                        'sales_history' => array('label' => Mage::helper('inventoryplus')->__('Products'),
                        'sort_order' => 20,
                        'click' => 'showDisableTips()',
                        'url' =>'',
                        'active' => false,
                        'level' => 2,
                        )
                    )
                ),
                'supplier' => array('label' => Mage::helper('inventoryplus')->__('Stock Purchase'),
                    'sort_order' => 20,
                    'url' => '',
                    'active' => false,
                    'level' => 1,
                    'children' => array(
                        'supplier_product' => array('label' => Mage::helper('inventoryplus')->__('Warehousing Time'),
                        'sort_order' => 10,
                        'click' => 'showDisableTips()',
                        'url' => '',
                        'active' => false,
                        'level' => 2,
                        ),
                        'inventory_by_supplier' => array('label' => Mage::helper('inventoryplus')->__('Suppliers'),
                        'sort_order' => 20,
                        'click' => 'showDisableTips()',
                        'url' => '',
                        'active' => false,
                        'level' => 2,
                        )
                    )
                )   
            )
        );
        return $parentArr;
    }
}
