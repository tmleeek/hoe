<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2010-2012 Amasty (http://www.amasty.com)
* @package Amasty_Stockstatus
*/
class Amasty_Stockstatus_Block_Rewrite_Checkout_Cart_Item_Renderer_Configurable extends Mage_Checkout_Block_Cart_Item_Renderer_Configurable 
{
   /**
    * Rewrite because of incorrect array_unique use in default magento class
    */
    public function getMessages()
    {
        $textUsed = array();
        $messages = array();
        if ($this->getItem()->getMessage(false)) {
            foreach ($this->getItem()->getMessage(false) as $message) 
            {
                if (!in_array($message, $textUsed))
                {
                    $messages[] = array(
                        'text'  => $message,
                        'type'  => $this->getItem()->getHasError() ? 'error' : 'notice'
                    );
			$textUsed[] = $message;
                }
            }
        }
        return $messages;
    }  
}
