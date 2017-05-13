<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2010-2012 Amasty (http://www.amasty.com)
* @package Amasty_Stockstatus
*/
class Amasty_Stockstatus_Model_Observer
{
    public function onModelSaveBefore($observer)
    {
        $model = $observer->getObject();
        if ($model instanceof Mage_Catalog_Model_Resource_Eav_Attribute)
        {
            if ('custom_stock_status' == $model->getAttributeCode())
            {
                Mage::getModel('amstockstatus/range')->clear(); // deleting all old values
                $ranges = Mage::app()->getRequest()->getPost('amstockstatus_range');
                // saving quantity ranges
                if ($ranges && is_array($ranges) && !empty($ranges))
                {
                    foreach ($ranges as $range)
                    {
                        $data = array(
                            'qty_from'   => $range['from'],
                            'qty_to'     => $range['to'],
                            'status_id'  => $range['status'],
                        );
                        if(Mage::getStoreConfig('amstockstatus/general/use_range_rules')) {
                            $data['rule'] = $range['rule'];    
                        }
                        $rangeModel = Mage::getModel('amstockstatus/range');
                        $rangeModel->setData($data);
                        $rangeModel->save();
                    }
                }
            }
        }
    }
    
    /**
    * Used to show configurable product attributes in case when all elements are out-of-stock
    * 
    * "$_product->isSaleable() &&" should be commented out at line #100 (where "container2" block is outputted) in catalog/product/view.phtml
    * to make this work
    * 
    * @see Mage_Catalog_Model_Product::isSalable
    * @param object $observer
    */
    public function onCatalogProductIsSalableAfter($observer)
    {
        if (Mage::getStoreConfig('amstockstatus/general/outofstock'))
        {
            $salable = $observer->getSalable();
            $stack = debug_backtrace();
            foreach ($stack as $object)
            {
                if (isset($object['file']))
                {
                    if ($object['file'])
                    {
                        if ( isset($object['file']) && false !== strpos($object['file'], 'options' . DIRECTORY_SEPARATOR . 'configurable'))
                        {
                            $salable->setData('is_salable', true);
                        }
                    }
                }
            }
        }
    }
    
    public function onProductBlockHtmlBefore($observer)
    {
        if (($observer->getBlock() instanceof Mage_Catalog_Block_Product_View)) {
          $html = $observer->getTransport()->getHtml();
          $product = Mage::registry('product');
          if($product)
              $product = Mage::getModel('catalog/product')->load($product->getId());
          if($product && Mage::helper('amstockstatus')->getCustomStockStatusText($product))
                    $html = Mage::helper('amstockstatus')->processViewStockStatus($product, $html);
          $observer->getTransport()->setHtml($html);
       }    
    }
    
    public function onListBlockHtmlBefore($observer)//core_block_abstract_to_html_after    
    {        
      if (($observer->getBlock() instanceof Mage_Catalog_Block_Product_List) && Mage::getStoreConfig('amstockstatus/general/display_at_categoty')) {
          $html = $observer->getTransport()->getHtml();
          preg_match_all("/product.*?-price-([0-9]+)/", $html, $productsId) ;
          if(!$productsId[0]){
               preg_match_all("/price-including-tax-([0-9]+)/", $html, $productsId) ;
          }
          foreach ($productsId[1] as $key => $productId){  
              $_product = Mage::getModel('catalog/product')->load($productId);
              if($_product) {
                  $template = '@(product.*?-price-'.$productId.'">(.*?)div>)@s';
                  preg_match_all($template, $html, $res);
                  if(!$res[0]){
                        $template = '@(price-including-tax-'.$productId.'">(.*?)div>)@s';
                         preg_match_all($template, $html, $res);
                         if(!$res[0]){
                             $template = '@(price-excluding-tax-'.$productId.'">(.*?)div>)@s';
                             preg_match_all($template, $html, $res);
                        }
                  }
                  if($res[0]){
                       $replace = $res[1][0] . Mage::helper('amstockstatus')->showStockStatus($_product, false, true);
                       $html= str_replace($res[0][0], $replace, $html);
                  }
              }
          
          }
          $observer->getTransport()->setHtml($html);
      }
    }
    
    public function onCoreBlockAbstractToHtmlBefore($observer) 
    {
        
        $block = $observer->getBlock();
        $catalogProductEditTabsClass = Mage::getConfig()->getBlockClassName('adminhtml/catalog_product_attribute_edit_tabs');
        if ($catalogProductEditTabsClass == get_class($block) && 'custom_stock_status' == Mage::registry('entity_attribute')->getData('attribute_code')) {
            if(method_exists ($block, 'addTabAfter' )){
                $block->addTabAfter('icons', array(
                    'label'     => Mage::helper('amstockstatus')->__('Manage Icons'),
                    'title'     => Mage::helper('amstockstatus')->__('Manage Icons'),
                    'content'   => $block->getLayout()->createBlock('amstockstatus/icons')->toHtml(),
                ), "labels");
                $block->addTabAfter('ranges', array(
                    'label'     => Mage::helper('amstockstatus')->__('Quantity Range Statuses'),
                    'title'     => Mage::helper('amstockstatus')->__('Quantity Range Statuses'),
                    'content'   => $block->getLayout()->createBlock('amstockstatus/ranges')->toHtml(),
                ), "labels");        
            }
            else{
                $block->addTab('icons', array(
                    'label'     => Mage::helper('amstockstatus')->__('Manage Icons'),
                    'title'     => Mage::helper('amstockstatus')->__('Manage Icons'),
                    'content'   => $block->getLayout()->createBlock('amstockstatus/icons')->toHtml(),
                ));
                $block->addTab('ranges', array(
                    'label'     => Mage::helper('amstockstatus')->__('Quantity Range Statuses'),
                    'title'     => Mage::helper('amstockstatus')->__('Quantity Range Statuses'),
                    'content'   => $block->getLayout()->createBlock('amstockstatus/ranges')->toHtml(),
                ));    
            }
        }
        
        return $this;
    }  
}
