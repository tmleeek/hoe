<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2012 Amasty (http://www.amasty.com)
* @package Amasty_Xnotif
*/   
require_once 'AbstractController.php';

class Amasty_Xnotif_PriceController extends Amasty_Xnotif_AbstractController
{
     public function preDispatch()
    {
        parent::preDispatch();
        
        $this->_title= $this->__('My Price Subscriptions');
        $this->_type= "price";
    }
} 