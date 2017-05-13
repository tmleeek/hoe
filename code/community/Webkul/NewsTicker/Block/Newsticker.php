<?php
 
    class Webkul_NewsTicker_Block_Newsticker extends Mage_Core_Block_Template {

        public function _prepareLayout() {
            return parent::_prepareLayout();
        }

        public function getnewsticker() {
            if (!$this->hasData("newsticker"))
                $this->setData("newsticker", Mage::registry("newsticker"));
            return $this->getData("newsticker");
        }

        public function getDataByGroupCode($groupCode){
            return Mage::getModel("newsticker/newstickergroup")->getDataByGroupCode($groupCode);
        }

    }
