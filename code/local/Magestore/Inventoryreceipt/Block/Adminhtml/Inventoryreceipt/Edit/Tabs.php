<?php

class Magestore_Inventoryreceipt_Block_Adminhtml_Inventoryreceipt_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {      
      parent::__construct();
      $this->setId('inventoryreceipt_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('inventoryreceipt')->__('Stock Receiving Information'));
  }

  protected function _beforeToHtml()
  {
        $this->addTab('product_section', array(
            'label' => Mage::helper('inventoryreceipt')->__('Stock Receiving'),
            'title' => Mage::helper('inventoryreceipt')->__('Stock Receiving'),
            'url' => $this->getUrl('*/*/products', array(
                '_current' => true,
                'id' => $this->getRequest()->getParam('id'),
                'store' => $this->getRequest()->getParam('store')
            )),            
            'class' => 'ajax',
        ));  
     
      return parent::_beforeToHtml();
  }
}