<?php

	class Webkul_NewsTicker_Model_Mysql4_Newsticker_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {

	    public function _construct() {
	        parent::_construct();
	        $this->_init("newsticker/newsticker");
	    }

	}
