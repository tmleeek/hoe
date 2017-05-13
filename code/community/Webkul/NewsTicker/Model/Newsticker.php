<?php

    class Webkul_NewsTicker_Model_Newsticker extends Mage_Core_Model_Abstract {

        public function _construct() {
            parent::_construct();
            $this->_init("newsticker/newsticker");
        }

        public function getAllAvailNewsIds(){
            return Mage::getResourceModel("newsticker/newsticker_collection")->getAllIds();
        }

        public function getAllNewss() {
            $collection = Mage::getResourceModel("newsticker/newsticker_collection");
            $collection->getSelect()->where("status = ?", 1);
            $data = array();
            foreach ($collection as $record)
                $data[$record->getId()] = array("value" => $record->getId(), "label" => $record->getfilename());
            return $data;
        }

        public function getDataByNewsIds($newsIds) {
            $data = array();
            if($newsIds != "") {
                $collection = Mage::getResourceModel("newsticker/newsticker_collection");
                $collection->getSelect()->where("news_id IN (" . $newsIds . ")")->order("sort_order");
                foreach ($collection as $record) {
                    $status = $record->getStatus();
                    if ($status == 1)
                        $data[] = $record;
                }
            }
            return $data;
        }

    }
