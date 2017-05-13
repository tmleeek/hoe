<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2010-2012 Amasty (http://www.amasty.com)
* @package Amasty_Stockstatus
*/
class Amasty_Stockstatus_Block_Bundle_Checkout_Cart_Item_Renderer extends Mage_Bundle_Block_Checkout_Cart_Item_Renderer
{
   
    /**
     * Return cart backorder messages
     *
     * @return array
     */
    public function getMessages()
    {
        $messages = $this->getData('messages');
        if (is_null($messages)) {
            $messages = array();
        }
        $options = $this->getItem()->getQtyOptions();

        foreach ($options as $option) {
            if ($option->getMessage()) {
                $messages[] = array(
                    'text' => $option->getMessage(),
                    'type' => ($this->getItem()->getHasError()) ? 'error' : 'notice'
                );
            }
        }
	
	$product = Mage::getModel('catalog/product')->load($this->getProduct()->getId());
 	if (Mage::helper('amstockstatus')->getCustomStockStatusText($product))
                    {
		  $messages[] = array(
                    'text' => Mage::helper('amstockstatus')->getCustomStockStatusText($product),
                    'type' => 'notice'
                );                     
                    }
	

        return $messages;
    }
}
