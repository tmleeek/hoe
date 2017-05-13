<?php

class Magestore_Inventoryreceipt_Block_Adminhtml_Receiptlog_Edit_Tab_Products extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('receivelogproductsGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setVarNameFilter('filter');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        if (($this->getRequest()->getParam('id')) || Mage::getModel('admin/session')->getData('receipt_product_import')) {
            $this->setDefaultFilter(array('in_products' => 1));
        }        
    }

    protected function _addColumnFilterToCollection($column) {
        if ($column->getId() == 'in_products') {
            $productIds = $this->_getSelectedProducts();
            if (empty($productIds))
                $productIds = 0;
            if ($column->getFilter()->getValue())
                $this->getCollection()->addFieldToFilter('entity_id', array('in' => $productIds));
            elseif ($productIds)
                $this->getCollection()->addFieldToFilter('entity_id', array('nin' => $productIds));
            return $this;
        }
        return parent::_addColumnFilterToCollection($column);
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel('catalog/product')->getCollection()
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('sku')                
                ->addAttributeToSelect('image');                
        $id = $this->getRequest()->getParam('id');
        $receiptLog = Mage::getModel('inventoryreceipt/receiptlog')->load($id);
        $productIds = array();
            $receiptProducts = Mage::getModel('inventoryreceipt/receiptlog_product')
                    ->getCollection()
                    ->addFieldToFilter('receipt_log_id', $id);
            foreach ($receiptProducts as $receiptProduct) {
                $productIds[] = $receiptProduct->getProductId();
            }
        if ($receiptLog->getStatus() == 2 || $receiptLog->getStatus() == 3) {
             $collection->addFieldToFilter('entity_id', array('in' => $productIds));
        }
        $collection->joinField('receipt_qty', 'inventoryreceipt/receiptlog_product', 'receipt_qty', 'product_id=entity_id', '{{table}}.receipt_log_id=' . $this->getRequest()->getParam('id'), 'left');
        $collection->getSelect()->group('entity_id');
        
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $warehouse = Mage::getModel('inventoryplus/warehouse')->getCollection()->getFirstItem();
        $warehouseId = $warehouse->getId();
        $id = $this->getRequest()->getParam('id');
        $receiptLog = Mage::getModel('inventoryreceipt/receiptlog')->load($id);
        $adminId = Mage::getSingleton('admin/session')->getUser()->getId();
        $warehouse = $this->getRequest()->getParam('id');
        $check = Mage::helper('inventoryplus/warehouse')->canEdit($adminId, $warehouseId);
        if ($check == true && $receiptLog->getStatus()==1)
            $this->addColumn('in_products', array(
                'header_css_class' => 'a-center',
                'type' => 'checkbox',
                'name' => 'in_products',
                'values' => $this->_getSelectedProducts(),
                'align' => 'center',
                'index' => 'entity_id'
            ));
        
        
        $this->addColumn('entity_id', array(
            'header' => Mage::helper('inventoryreceipt')->__('ID'),
            'sortable' => true,
            'width' => '60',
            'index' => 'entity_id'
        ));
        
        $this->addColumn('name', array(
            'header' => Mage::helper('inventoryreceipt')->__('Name'),
            'align' => 'left',
            'index' => 'name',
        ));

        $sets = Mage::getResourceModel('eav/entity_attribute_set_collection')
            ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
            ->load()
            ->toOptionHash();

        $this->addColumn('set_name',
            array(
                'header'=> Mage::helper('catalog')->__('Attrib. Set Name'),
                'width' => '100px',
                'index' => 'attribute_set_id',
                'type'  => 'options',
                'options' => $sets,
        ));
        
        $this->addColumn('sku', array(
            'header' => Mage::helper('inventoryreceipt')->__('SKU'),
            'width' => '80px',
            'index' => 'sku'
        ));

        $this->addColumn('image', array(
            'header' => Mage::helper('inventoryreceipt')->__('Image'),
            'width' => '90px',
            'filter' => false,
            'renderer' => 'inventoryplus/adminhtml_renderer_productimage'
        ));
        
        
        if($check == true && $receiptLog->getStatus()!=1){
            $this->addColumn('receipt_qty', array(
                'header' => Mage::helper('inventoryreceipt')->__('Qty Received'),
                'align' => 'left',
                'name' => 'receipt_qty',
                'index' => 'receipt_qty',
                'type' => 'number',
            ));        
        }else{
            $this->addColumn('receipt_qty', array(
                'header' => Mage::helper('inventoryreceipt')->__('Qty Received'),
                'align' => 'left',
                'index' => 'receipt_qty',
                'name' => 'receipt_qty',
                'type' => 'number',
                'editable' => true,
                'filter' => false
                //'renderer' => 'inventoryreceipt/adminhtml_receiptlog_renderer_input'
            ));  
        }
        
        if ($receiptLog->getStatus() == 2 || $receiptLog->getStatus() == 3) {
            $this->addExportType('*/*/exportProductCsv', Mage::helper('inventoryreceipt')->__('CSV'));
            $this->addExportType('*/*/exportProductXml', Mage::helper('inventoryreceipt')->__('XML'));
        }
      
    }

    public function _getSelectedProducts() {
        $productArrays = $this->getProducts();
         
 
        $products = '';
        $adjustProducts = array();
        if ($productArrays) {
            $products = array();
            foreach ($productArrays as $productArray) {
                parse_str(urldecode($productArray), $adjustProducts);
                if (count($adjustProducts)) {
                    foreach ($adjustProducts as $pId => $enCoded) {
                        $products[] = $pId;
                    }
                }
            }
        }
        if ((!is_array($products))) {
            $products = array_keys($this->getProductSelect());
        }
        
        return $products;
    }
    
    public function getProductSelect(){          
        $products = array();
        $adjustStockProducts = Mage::getModel('inventoryreceipt/receiptlog_product')->getCollection()
                                                ->addFieldToFilter('receipt_log_id',$this->getRequest()->getParam('id'));
        if ($adjustStockProducts->getSize()) {
            
            foreach ($adjustStockProducts as $adjustStockProduct) {                
                if ($adjustStockProduct->getId())
                    $products[$adjustStockProduct->getProductId()] = array('receipt_qty' => $adjustStockProduct->getReceiptQty());      
            }            
        }
       
        return $products;
    }
    
    public function getGridUrl() {
        return $this->getUrl('*/*/productsGrid', array(
                    '_current' => true,
                    'id' => $this->getRequest()->getParam('id'),
                    'store' => $this->getRequest()->getParam('store')
                ));
    }

    public function getRowUrl($row) {
        return false;
    }

}
