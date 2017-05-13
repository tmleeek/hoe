<?php

	class Webkul_NewsTicker_Block_Adminhtml_Newstickergroup extends Mage_Adminhtml_Block_Widget_Grid_Container {

	    public function __construct() {
	        $this->_controller = "adminhtml_newstickergroup";
	        $this->_blockGroup = "newsticker";
	        $this->_headerText = Mage::helper("newsticker")->__("Add/Manage News Group");
	        $this->_addButtonLabel = Mage::helper("newsticker")->__("Add News Group");
	        parent::__construct();
	    }

	}
