<?php

    class Webkul_NewsTicker_Model_Mysql4_Newstickergroup_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {

        public function _construct() {
            parent::_construct();
            $this->_init("newsticker/newstickergroup");
        }

        public function getDuplicateGroupCode($groupCode) {
            $this->setConnection($this->getResource()->getReadConnection());
            $table = $this->getTable("newsticker/newstickergroup");
            $select = $this->getSelect();
            $select->from(array("main_table" => $table), "group_id")
                    ->where("group_code = ?", $groupCode);
            return $this;
        }

    }
