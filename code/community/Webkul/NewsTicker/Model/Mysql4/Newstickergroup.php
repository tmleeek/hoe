<?php

    class Webkul_NewsTicker_Model_Mysql4_Newstickergroup extends Mage_Core_Model_Mysql4_Abstract {

        public function _construct() {
            $this->_init("newsticker/newstickergroup", "group_id");
        }

        public function _beforeSave(Mage_Core_Model_Abstract $object) {
            $isDataValid = true;
            $id = $object->getId();
            $groupCode = $object->getGroupCode();
            $groupCollection = Mage::getModel("newsticker/newstickergroup")->getCollection();
            if ($id == "" || $id == 0) {
                if ($groupCode == "")
                    throw new Exception("News Group code can't be blank !!");
                else {
                    $groupInfo = $groupCollection->getDuplicateGroupCode($groupCode);
                    if ($groupInfo->count() > 0)
                        $isDataValid = false;
                    if ($isDataValid === false)
                        throw new Exception("News Group Code already exists !!");
                }
            }
        }

    }
