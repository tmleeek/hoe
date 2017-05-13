<?php

class Magestore_Inventoryplus_Block_Adminhtml_Warehouse_Edit_Tab_Products extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('productGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        
        $this->setUseAjax(true);    
        if($this->getWarehouse()->getId())
            $this->setDefaultFilter(array('in_products' => 1));
    }

    protected function _prepareLayout()
    {        
        Mage::dispatchEvent('warehouse_product_prepare_layout', array('block' => $this)); 
        return parent::_prepareLayout();
    }
    
    public function getMainButtonsHtml()
    {
        $html = '';
        if($this->getFilterVisibility()){
            $html.= $this->getResetFilterButtonHtml();
            $html.= $this->getSearchButtonHtml();
            if($this->getChildHtml('add_product_button'))
                $html.= $this->getChildHtml('add_product_button');
        }
        return $html;
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
        $warehouseId = $this->getWarehouse()->getId();
        $collection = Mage::getModel('catalog/product')->getCollection()
                            ->addAttributeToSelect('type_id')
                            ->addAttributeToFilter('type_id', array('nin' => array('configurable', 'bundle', 'grouped')))
                            ->addAttributeToSelect('sku')                            
                            ->addAttributeToSelect('name')
                            ->addAttributeToSelect('status')
                            ->addAttributeToSelect('price')
                            ->addAttributeToSelect('attribute_set_id')
                            ;
        $collection->joinField('total_qty', 'inventoryplus/warehouse_product', 'total_qty', 'product_id=entity_id', "{{table}}.warehouse_id=$warehouseId", 'left');
        $collection->joinField('available_qty', 'inventoryplus/warehouse_product', 'available_qty', 'product_id=entity_id', "{{table}}.warehouse_id=$warehouseId", 'left');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $adminId = Mage::getSingleton('admin/session')->getUser()->getId();
        $warehouse = $this->getRequest()->getParam('id');
        $check = Mage::helper('inventoryplus/warehouse')->canEdit($adminId, $warehouse);
        if($check == true)
            $this->addColumn('in_products', array(
                'header_css_class' => 'a-center',
                'type' => 'checkbox',
                'name' => 'in_products',
                'values' => $this->_getSelectedProducts(),
                'align' => 'center',
                'index' => 'entity_id',
                'disabled_values'=> $this->_getDisabledProducts()                
            ));


        $this->addColumn('entity_id', array(
            'header' => Mage::helper('inventoryplus')->__('ID'),
            'sortable' => true,
            'width' => '60',
            'type'  => 'number',
            'index' => 'entity_id',
        ));

        $this->addColumn('product_name', array(
            'header' => Mage::helper('inventoryplus')->__('Name'),
            'align' => 'left',
            'index' => 'name',
        ));

        $this->addColumn('product_sku', array(
            'header' => Mage::helper('inventoryplus')->__('SKU'),
            'width' => '80px',
            'index' => 'sku'
        ));

        $this->addColumn('product_image', array(
            'header' => Mage::helper('inventoryplus')->__('Image'),
            'width' => '90px',
            'renderer' => 'inventoryplus/adminhtml_renderer_productimage',
            'index' => 'product_image',
            'filter' => false
        ));

        $this->addColumn('product_status', array(
            'header' => Mage::helper('inventoryplus')->__('Status'),
            'width' => '90px',
            'index' => 'status',
            'type' => 'options',
            'options' => Mage::getSingleton('catalog/product_status')->getOptionArray(),
        ));

        $this->addColumn('product_price', array(
            'header' => Mage::helper('inventoryplus')->__('Price'),
            'type' => 'currency',
            'currency_code' => (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
            'index' => 'price'
        ));

        if ($check == true) {
            $this->addColumn('total_qty', array(
                'header' => Mage::helper('catalog')->__('Total Qty'),
                'width' => '80px',
                'index' => 'total_qty',
                'type' => 'number',
                'inline_css' => 'validate-zero-or-greater',
                'editable' => true,
                'default' => 0,
                'renderer' => 'inventoryplus/adminhtml_renderer_input'
            ));
        } else {
            $this->addColumn('total_qty', array(
                'header' => Mage::helper('catalog')->__('Total Qty'),
                'width' => '80px',
                'index' => 'total_qty',
                'type' => 'number',
                'default' => 0
            ));
        }
        $this->addColumn('available_qty', array(
            'header' => Mage::helper('catalog')->__('Avail. Qty'),
            'width' => '80px',
            'type' => 'number',
            'index' => 'available_qty',
        ));
        $this->addExportType('*/*/exportWarehouseProductCsv', Mage::helper('inventoryplus')->__('CSV'));
        $this->addExportType('*/*/exportWarehouseProductXml', Mage::helper('inventoryplus')->__('XML'));
        return parent::_prepareColumns();
    }
    
    
    public function _getDisabledProducts() {
        $warehouse = $this->getWarehouse();
        $products = array();
        $productCollection = Mage::getResourceModel('inventoryplus/warehouse_product_collection')
                ->addFieldToFilter('warehouse_id', $warehouse->getId());
        if (count($productCollection)) {
            foreach ($productCollection as $product) {                
                if($product->getTotalQty()>0)
                    $products[$product->getProductId()] = array('total_qty' => $product->getQty());
            }
        }
        
        return array_keys($products);
    }
    
    public function _getSelectedProducts() {
        $productArrays = $this->getProducts();
        $products = '';
        $warehouseProducts = array();
        if ($productArrays) {
            $products = array();
            foreach ($productArrays as $productArray) {
                parse_str(urldecode($productArray), $warehouseProducts);
                if (count($warehouseProducts)) {
                    foreach ($warehouseProducts as $pId => $enCoded) {
                        $products[] = $pId;
                    }
                }
            }
        }
        if (!is_array($products)) {
            $products = array_keys($this->getSelectedProducts());
        }
        return $products;
    }

    public function getSelectedProducts() {
        $warehouse = $this->getWarehouse();
        $products = array();
        $productCollection = Mage::getResourceModel('inventoryplus/warehouse_product_collection')
                ->addFieldToFilter('warehouse_id', $warehouse->getId());
        if (count($productCollection)) {
            foreach ($productCollection as $product) {
                $products[$product->getProductId()] = array('total_qty' => $product->getQty());
            }
        }
        return $products;
    }

    public function getWarehouse() {
        return Mage::getModel('inventoryplus/warehouse')
                        ->load($this->getRequest()->getParam('id'));
    }

    public function getGridUrl() {
        return $this->getUrl('*/*/productsGrid', array(
                    '_current' => true
                ));
    }
    
    public function getRowUrl($row)
    {
        return false;
    }

    public function _filterAvailCallback($collection, $column) {
        $filter = $column->getFilter()->getValue();
        foreach ($collection as $item) {
            $avail = Mage::helper('inventoryplus')->getAvailQty($item);
            $pass = TRUE;
            if (isset($filter['from']) && $filter['from'] >= 0) {
                if (floatval($avail) < floatval($filter['from'])) {
                    $pass = FALSE;
                }
            }
            if ($pass) {
                if (isset($filter['to']) && $filter['to'] >= 0) {
                    if (floatval($avail) > floatval($filter['to'])) {
                        $pass = FALSE;
                    }
                }
            }
            if ($pass) {
                $item->setAvailQty($avail);
                $arr[] = $item;
            }
        }
        $temp = Mage::helper('inventoryplus')->_tempCollection(); // A blank collection 
        for ($i = 0; $i < count($arr); $i++) {
            $temp->addItem($arr[$i]);
        }
        $this->setCollection($temp);
    }

}

