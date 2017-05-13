<?php

    class Webkul_NewsTicker_Block_Adminhtml_Newstickergroup_Edit_Tab_Gridnewsticker extends Mage_Adminhtml_Block_Widget_Container {

        public function __construct() {
            parent::__construct();
            $this->setTemplate("newsticker/newsticker.phtml");
        }

        public function getTabsHtml() {
            return $this->getChildHtml("newstickers");
        }

        protected function _prepareLayout() {
            $this->setChild("newstickers", $this->getLayout()->createBlock("newsticker/adminhtml_newstickergroup_edit_tab_newsticker", "newstickergroup.grid.newsticker"));
            return parent::_prepareLayout();
        }

        public function getNewsgroupData() {
            return Mage::registry("newstickergroup_data");
        }

        public function getNewssJson() {
            $newss = explode(",", $this->getNewsgroupData()->getNewsIds());
            if(!empty($newss) && isset($newss[0]) && $newss[0] != "") {
                $data = array();
                foreach ($newss as $element)
                    $data[$element] = $element;
                return Zend_Json::encode($data);
            }
            return "{}";
        }

    }
