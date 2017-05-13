<?php

class Magestore_Inventoryreceipt_Block_Adminhtml_Inventoryreceipt_Edit_Tab_Products extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('receiveproductGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setVarNameFilter('filter');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        if($receiptProducts = Mage::getModel('admin/session')->getData('receipt_product_import'))
            $this->setDefaultFilter(array('in_products' => 1));
    }

    protected function _prepareLayout() {
        return parent::_prepareLayout();
    }

    public function getMainButtonsHtml() {
        $html = '';
        if ($this->getFilterVisibility()) {
            $html.= $this->getResetFilterButtonHtml();
            $html.= $this->getSearchButtonHtml();
            if ($this->getChildHtml('add_product_button'))
                $html.= $this->getChildHtml('add_product_button');
        }
        return $html;
    }

    protected function _addColumnFilterToCollection($column) {
        if ($column->getId() == 'in_products') {
            $productIds = $this->getSelectedProducts();
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
        $warehouse = Mage::getModel('inventoryplus/warehouse')->getCollection()->getFirstItem();
        if (!$warehouse)
            return parent::_prepareCollection();
        $warehouseId = $warehouse->getId();
        if (!$warehouse->getId())
            $warehouseId = 0;
        $collection = Mage::getModel('catalog/product')->getCollection()
                ->addAttributeToSelect('sku')
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('status')
                ->addAttributeToSelect('price')
                ->addAttributeToSelect('attribute_set_id')
                ->addAttributeToSelect('type_id')
                ->addAttributeToFilter('type_id', array('nin' => array('configurable', 'bundle', 'grouped')));
        $collection->joinField('total_qty', 'inventoryplus/warehouse_product', 'total_qty', 'product_id=entity_id', "{{table}}.warehouse_id=$warehouseId", 'left');
        $collection->joinField('available_qty', 'inventoryplus/warehouse_product', 'available_qty', 'product_id=entity_id', "{{table}}.warehouse_id=$warehouseId", 'left');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $warehouse = Mage::getModel('inventoryplus/warehouse')->getCollection()->getFirstItem();
        if (!$warehouse)
            return parent::_prepareColumns();
        $warehouseId = $warehouse->getId();
        if (!$warehouse->getId())
            return parent::_prepareColumns();
        $adminId = Mage::getSingleton('admin/session')->getUser()->getId();
        
        $check = Mage::helper('inventoryplus/warehouse')->canEdit($adminId, $warehouseId);
        if ($check == true)
            $this->addColumn('in_products', array(
                'header_css_class' => 'a-center',
                'type' => 'checkbox',
                'name' => 'in_products',
                'values' => $this->getSelectedProducts(),
                'align' => 'center',
                'index' => 'entity_id'
            ));


        $this->addColumn('entity_id', array(
            'header' => Mage::helper('catalog')->__('ID'),
            'sortable' => true,
            'width' => '60',
            'type' => 'number',
            'index' => 'entity_id',
        ));

        
        $this->addColumn('product_name', array(
            'header' => Mage::helper('catalog')->__('Name'),
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
        
        $this->addColumn('product_sku', array(
            'header' => Mage::helper('catalog')->__('SKU'),
            'width' => '80px',
            'index' => 'sku'
        ));

        $this->addColumn('product_image', array(
            'header' => Mage::helper('catalog')->__('Image'),
            'width' => '90px',
            'renderer' => 'inventoryplus/adminhtml_renderer_productimage',
            'index' => 'product_image',
            'filter' => false
        ));

        $this->addColumn('product_status', array(
            'header' => Mage::helper('catalog')->__('Status'),
            'width' => '90px',
            'index' => 'status',
            'type' => 'options',
            'options' => Mage::getSingleton('catalog/product_status')->getOptionArray(),
        ));

        $this->addColumn('product_price', array(
            'header' => Mage::helper('catalog')->__('Price'),
            'type' => 'currency',
            'currency_code' => (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
            'index' => 'price'
        ));



        $this->addColumn('total_qty', array(
            'header' => Mage::helper('catalog')->__('Total Qty'),
            'width' => '80px',
            'index' => 'total_qty',
            'type' => 'number',
            'default' => 0
        ));

        $this->addColumn('available_qty', array(
            'header' => Mage::helper('catalog')->__('Avail. Qty'),
            'width' => '80px',
            'type' => 'number',
            'index' => 'available_qty',
        ));

        $this->addColumn('add_qty', array(
            'header' => Mage::helper('catalog')->__('Qty Received'),
            'width' => '80px',
            'type' => 'number',
            'filter' => false,            
            'editable' => true,  
        ));

        return parent::_prepareColumns();
    }

    public function getSelectedProducts() {
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
        if ((!is_array($products) || Mage::getModel('admin/session')->getData('receipt_product_import'))) {
            $products = array_keys($this->getProductSelect());
        }
        
        return $products;
    }

   
    public function getProductSelect(){          
        $products = array();
        if ($adjustStockProducts = Mage::getModel('admin/session')->getData('receipt_product_import')) {
            $productModel = Mage::getModel('catalog/product');
            foreach ($adjustStockProducts as $adjustStockProduct) {
                $productId = $productModel->getIdBySku($adjustStockProduct['SKU']);
                if ($productId){
                    if($adjustStockProduct['QTY']){
                        $products[$productId] = array('add_qty' => $adjustStockProduct['QTY']);
                    } else if($adjustStockProduct['Qty Received']){
                        $products[$productId] = array('add_qty' => $adjustStockProduct['Qty Received']);
                    }
                }      
            }            
        }
       
        return $products;
    }
    

    public function getGridUrl() {
        return $this->getUrl('*/*/productsGrid', array(
                    '_current' => true
        ));
    }

    public function getRowUrl($row) {
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
