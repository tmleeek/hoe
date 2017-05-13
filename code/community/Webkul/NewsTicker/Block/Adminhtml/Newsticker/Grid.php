<?php

    class Webkul_NewsTicker_Block_Adminhtml_Newsticker_Grid extends Webkul_NewsTicker_Block_Adminhtml_Widget_Grid {

        public function __construct() {
            parent::__construct();
            $this->setId("newsGrid");
            $this->setDefaultSort("news_id");
            $this->setDefaultDir("ASC");
            $this->setSaveParametersInSession(true);
        }

        protected function _prepareCollection() {
            $collection = Mage::getModel("newsticker/newsticker")->getCollection();
            $this->setCollection($collection);
            return parent::_prepareCollection();
        }

        protected function _prepareColumns() {
            $this->addColumn("news_id", array(
                "header" => Mage::helper("newsticker")->__("ID"),
                "align"  => "center",
                "width"  => "30px",
                "index"  => "news_id"
            ));
    		
    		$this->addColumn("newsticker", array(
                "header" => Mage::helper("newsticker")->__("News"),
                "index"  => "newsticker",
    			"align"  => "center"
            ));
    		$this->addColumn("sort_order", array(
                "header" => Mage::helper("newsticker")->__("Sort Order"),
                "index"  => "sort_order",
    			"width"  => "100px",
                "align"  => "center"
            ));

            $this->addColumn("status", array(
                "header" => Mage::helper("newsticker")->__("Status"),
                "align"  => "left",
                "width"  => "80px",
                "index"  => "status",
                "type"   => "options",
                "options" => array(1 => "Enabled",2 => "Disabled")
            ));

            $this->addColumn("action",array(
                "header" => Mage::helper("newsticker")->__("Action"),
                "width"  => "80",
                "type"   => "action",
                "getter" => "getId",
                "actions" => array(
                    array(
                        "caption" => Mage::helper("newsticker")->__("Edit"),
                        "url"     => array("base" => "*/*/edit"),
                        "field"   => "id"
                    )
                ),
                "filter" => false,
                "sortable"  => false,
                "index"  => "stores",
                "is_system" => true,
            ));
            $this->addExportType("*/*/exportCsv", Mage::helper("newsticker")->__("CSV"));
            $this->addExportType("*/*/exportXml", Mage::helper("newsticker")->__("XML"));
            return parent::_prepareColumns();
        }

        protected function _prepareMassaction() {
            $this->setMassactionIdField("news_id");
            $this->getMassactionBlock()->setFormFieldName("newsticker");
            $this->getMassactionBlock()->addItem("delete", array(
                "label" => Mage::helper("newsticker")->__("Delete"),
                "url" => $this->getUrl("*/*/massDelete"),
                "confirm" => Mage::helper("newsticker")->__("Are you sure?")
            ));
            $statuses = Mage::getSingleton("newsticker/status")->getOptionArray();
            array_unshift($statuses, array("label" => "", "value" => ""));
            $this->getMassactionBlock()->addItem("status", array(
                "label" => Mage::helper("newsticker")->__("Change status"),
                "url" => $this->getUrl("*/*/massStatus", array("_current" => true)),
                "additional" => array(
                    "visibility" => array(
                        "name" => "status",
                        "type" => "select",
                        "class" => "required-entry",
                        "label" => Mage::helper("newsticker")->__("Status"),
                        "values" => $statuses
                    )
                )
            ));
            return $this;
        }

        public function getRowUrl($row) {
            return $this->getUrl("*/*/edit", array("id" => $row->getId()));
        }

    }
