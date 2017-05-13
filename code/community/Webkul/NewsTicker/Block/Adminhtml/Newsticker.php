<?php

	class Webkul_NewsTicker_Block_Adminhtml_Newsticker extends Mage_Adminhtml_Block_Widget_Grid_Container {

	    public function __construct() {
	        $this->_controller = "adminhtml_newsticker";
	        $this->_blockGroup = "newsticker";
	        $this->_headerText = Mage::helper("newsticker")->__("News Manager");
	        $this->_addButtonLabel = Mage::helper("newsticker")->__("Add News");
	        parent::__construct();
	    }

	}
