<?php

    class Webkul_NewsTicker_Block_Adminhtml_Newstickergroup_Edit_Tab_Newsticker extends Webkul_NewsTicker_Block_Adminhtml_Widget_Grid {

        public function __construct() {
            parent::__construct();
            $this->setId("newstickerLeftGrid");
            $this->setDefaultSort("news_id");
            $this->setUseAjax(true);
        }

        public function getNewsgroupData() {
            return Mage::registry("newstickergroup_data");
        }

        protected function _prepareCollection() {
            $collection = Mage::getModel("newsticker/newsticker")->getCollection();
            $collection->getSelect()->order("news_id");
            $this->setCollection($collection);
            return parent::_prepareCollection();
        }

        protected function _addColumnFilterToCollection($column) {
            if ($this->getCollection()) {
                if ($column->getId() == "newsticker_triggers") {
                    $newsIds = $this->_getSelectedNewss();
                    if (empty($newsIds)) {
                        $newsIds = 0;
                    }
                    if ($column->getFilter()->getValue()) {
                        $this->getCollection()->addFieldToFilter("news_id", array("in" => $newsIds));
                    } else {
                        if ($newsIds) {
                            $this->getCollection()->addFieldToFilter("news_id", array("in" => $newsIds));
                        }
                    }
                } else {
                    parent::_addColumnFilterToCollection($column);
                }
            }
            return $this;;
        }

        protected function _prepareColumns() {
            $this->addColumn("newsticker_triggers", array(
                "header_css_class" => "a-center",
                "type"      => "checkbox",
                "values"    => $this->_getSelectedNewss(),
                "align"     => "center",
                "index"     => "news_id"
            ));

            $this->addColumn("news_id", array(
                "header"    => Mage::helper("catalog")->__("ID"),
                "sortable"  => true,
                "width"     => "50px",
                "align"     => "center",
                "index"     => "news_id"
            ));

    		$this->addColumn("newsticker", array(
                "header"    => Mage::helper("newsticker")->__("News"),
                "index"     => "newsticker",
    			"align"     => "center"
            ));

            $this->addColumn("sort_order", array(
                "header"    => Mage::helper("newsticker")->__("Sort Order"),
                "width"     => "80px",
                "index"     => "sort_order",
                "align"     => "center"
            ));
            return parent::_prepareColumns();
        }

        public function getGridUrl() {
            return $this->getUrl("*/*/newsgrid", array("_current" => true));
        }

        protected function _getSelectedNewss() {
            $newss = $this->getRequest()->getPost("selected_newstickers");
            if (is_null($newss)) {
                $newss = explode(",", $this->getNewsgroupData()->getNewsIds());
                return (sizeof($newss) > 0 ? $newss : 0);
            }
            return $newss;
        }

    }
