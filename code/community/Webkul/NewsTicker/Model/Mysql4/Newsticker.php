<?php

	class Webkul_NewsTicker_Model_Mysql4_Newsticker extends Mage_Core_Model_Mysql4_Abstract {

	    public function _construct() {
	        $this->_init("newsticker/newsticker","news_id");
	    }

	}
