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
 * Inventory Shipping Method Grid Block
 * 
 * @category    Magestore
 * @package     Magestore_Inventory
 * @author      Magestore Developer
 */
class Magestore_Inventoryreceipt_Block_Adminhtml_Receiptlog_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('receiptlogGrid');
        $this->setDefaultSort('receipt_log_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setVarNameFilter('filter');
    }

    /**
     * prepare collection for block to display
     *
     * @return Magestore_Inventorylowstock_Block_Adminhtml_Notificationlog_Grid
     */
    protected function _prepareCollection() {
        $collection = Mage::getModel('inventoryreceipt/receiptlog')->getCollection();
        $this->setCollection($collection);        
        return parent::_prepareCollection();
    }

    /**
     * prepare columns for this grid
     *
     * @return Magestore_Inventorylowstock_Block_Adminhtml_Notificationlog_Grid
     */
    protected function _prepareColumns() {
        $this->addColumn('receipt_log_id', array(
            'header' => Mage::helper('inventoryplus')->__('ID'),
            'align' => 'right',
            'width' => '80px',
            'type' => 'number',
            'index' => 'receipt_log_id',
        ));

        $this->addColumn('created_by', array(
            'header' => Mage::helper('inventoryreceipt')->__('Created By'),
            'align' => 'left',
            'index' => 'created_by',
            'type' => 'text',
        ));

        $this->addColumn('created_at', array(
            'header' => Mage::helper('inventoryreceipt')->__('Created At'),
            'align' => 'left',
            'width' => '150px',
            'index' => 'created_at',
            'type' => 'date',
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('inventoryreceipt')->__('Status'),
            'index' => 'status',
            'width' => '80px',
            'type' => 'options',
            'options' => Mage::getSingleton('inventoryplus/status')->getOptionArray(),
        ));
        
      
        
        $this->addColumn('action',
            array(
                'header'    =>    Mage::helper('inventoryplus')->__('Action'),
                'width'        => '100px',
                'type'        => 'action',
                'getter'    => 'getId',
                'actions'    => array(
                    array(
                        'caption'    => Mage::helper('inventoryplus')->__('View'),
                        'url'        => array('base'=> '*/*/view'),
                        'field'        => 'id'
                    )),
                'filter'    => false,
                'sortable'    => false,
                'renderer' =>   'inventoryreceipt/adminhtml_receiptlog_renderer_action',
                'index'        => 'stores',
                'is_system'    => true,
        ));

        $this->addExportType('*/*/exportCsv', Mage::helper('inventoryplus')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('inventoryplus')->__('XML'));

        return parent::_prepareColumns();
    }

    /**
     * get url for each row in grid
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/view', array('id' => $row->getId()));
    }

}