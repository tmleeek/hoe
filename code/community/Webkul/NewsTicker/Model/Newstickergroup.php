<?php

    class Webkul_NewsTicker_Model_Newstickergroup extends Mage_Core_Model_Abstract {

        public function _construct() {
            parent::_construct();
            $this->_init("newsticker/newstickergroup");
        }

        public function getDataByGroupCode($groupCode) {        
            $groupData = array();
            $newsData = array();
            $result = array("group_data"=>$groupData,"newsticker_data"=>$newsData);
            $collection = Mage::getResourceModel("newsticker/newstickergroup_collection");
            $collection->getSelect()->where("group_code = ?", $groupCode)->where("status = 1");
            foreach ($collection as $record) {
                $newsIds = $record->getNewsIds();
                $newsModel = Mage::getModel("newsticker/newsticker");
                $newsData = $newsModel->getDataByNewsIds($newsIds);
                $result = array("group_data" => $record, "newsticker_data" => $newsData);
            }
            return $result;
        }

    }
