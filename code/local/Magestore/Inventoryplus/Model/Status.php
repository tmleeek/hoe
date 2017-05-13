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
 * Inventory Status Model
 * 
 * @category    Magestore
 * @package     Magestore_Inventory
 * @author      Magestore Developer
 */
class Magestore_Inventoryplus_Model_Status extends Varien_Object
{
    const STATUS_PENDING    = 1;
    const STATUS_COMPLETE    = 2;
    const STATUS_CANCEL    = 3;
    /**
     * get model option as array
     *
     * @return array
     */
    static public function getOptionArray()
    {
        return array(
            self::STATUS_PENDING    => Mage::helper('inventoryplus')->__('Pending'),
            self::STATUS_COMPLETE   => Mage::helper('inventoryplus')->__('Complete'),
            self::STATUS_CANCEL     => Mage::helper('inventoryplus')->__('Cancel'),
        );
    }
    
    /**
     * get model option hash as array
     *
     * @return array
     */
    static public function getOptionHash()
    {
        $options = array();
        foreach (self::getOptionArray() as $value => $label) {
            $options[] = array(
                'value'    => $value,
                'label'    => $label
            );
        }
        return $options;
    }
    
    // static public function getOptionDisplayArray(){
    //     return array(
    //         self::STATUS_ENABLED    => Mage::helper('inventoryplus')->__('Yes'),
    //         self::STATUS_DISABLED   => Mage::helper('inventoryplus')->__('No')
    //     );
    // }
}