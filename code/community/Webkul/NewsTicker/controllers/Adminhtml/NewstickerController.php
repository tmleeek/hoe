<?php

    class Webkul_NewsTicker_Adminhtml_NewstickerController extends Mage_Adminhtml_Controller_Action {

        protected function _initAction() {
            $this->loadLayout()->_setActiveMenu("newsticker");
            $this->getLayout()->getBlock("head")->setTitle($this->__("Add/Manage News"));
            return $this;
        }

        public function indexAction() {
            $this->_initAction()->renderLayout();
        }

        public function editAction() {
            $id = $this->getRequest()->getParam("id");
            $model = Mage::getModel("newsticker/newsticker")->load($id);
            if ($model->getId() || $id == 0) {
                $data = Mage::getSingleton("adminhtml/session")->getFormData(true);
                if (!empty($data))
                    $model->setData($data);
                Mage::register("newsticker_data", $model);
                $this->loadLayout();
    			if (Mage::getSingleton("cms/wysiwyg_config")->isEnabled())
    				$this->getLayout()->getBlock("head")->setCanLoadTinyMce(true);
                $this->_setActiveMenu("newsticker");
                $this->_addContent($this->getLayout()->createBlock("newsticker/adminhtml_newsticker_edit"))
                        ->_addLeft($this->getLayout()->createBlock("newsticker/adminhtml_newsticker_edit_tabs"));
                $this->renderLayout();
            }
            else {
                Mage::getSingleton("adminhtml/session")->addError(Mage::helper("newsticker")->__("Item does not exist"));
                $this->_redirect("*/*/");
            }
        }

        public function newAction() {
            $this->_forward("edit");
        }

        public function saveAction() {
            $imagedata = array();
            if ($data = $this->getRequest()->getPost()) {
                $model = Mage::getModel("newsticker/newsticker");
                $model->setData($data)->setId($this->getRequest()->getParam("id"));
                try {
                    if($model->getCreatedTime == NULL || $model->getUpdateTime() == NULL)
                        $model->setCreatedTime(now())->setUpdateTime(now());
                    else
                        $model->setUpdateTime(now());
                    $model->save();
                    Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("newsticker")->__("Item was successfully saved"));
                    Mage::getSingleton("adminhtml/session")->setFormData(false);
                    if ($this->getRequest()->getParam("back")) {
                        $this->_redirect("*/*/edit", array("id" => $model->getId()));
                        return;
                    }
                    $this->_redirect("*/*/");
                    return;
                }
                catch (Exception $e) {
                    Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
                    Mage::getSingleton("adminhtml/session")->setFormData($data);
                    $this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
                    return;
                }
            }
            Mage::getSingleton("adminhtml/session")->addError(Mage::helper("newsticker")->__("Unable to find item to save"));
            $this->_redirect("*/*/");
        }

        public function deleteAction() {
            if ($this->getRequest()->getParam("id") > 0) {
                try {
                    Mage::getModel("newsticker/newsticker")->load($this->getRequest()->getParam("id"))->delete();
                    Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Item was successfully deleted"));
                    $this->_redirect("*/*/");
                }
                catch (Exception $e) {
                    Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
                    $this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
                }
            }
            $this->_redirect("*/*/");
        }

        public function massDeleteAction() {
            $bannerIds = $this->getRequest()->getParam("newsticker");
            if(!is_array($bannerIds))
                Mage::getSingleton("adminhtml/session")->addError(Mage::helper("adminhtml")->__("Please select item(s)"));
            else {
                try {
                    foreach ($bannerIds as $bannerId)
                        Mage::getModel("newsticker/newsticker")->load($bannerId)->delete();
                    Mage::getSingleton("adminhtml/session")->addSuccess(
                            Mage::helper("adminhtml")->__("Total of %d record(s) were successfully deleted", count($bannerIds))
                    );
                }
                catch (Exception $e) {
                    Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
                }
            }
            $this->_redirect("*/*/index");
        }

        public function massStatusAction() {
            $bannerIds = $this->getRequest()->getParam("newsticker");
            if (!is_array($bannerIds))
                Mage::getSingleton("adminhtml/session")->addError($this->__("Please select item(s)"));
            else {
                try {
                    foreach ($bannerIds as $bannerId)
                        $banner = Mage::getSingleton("newsticker/newsticker")->load($bannerId)->setStatus($this->getRequest()->getParam("status"))->save();
                    $this->_getSession()->addSuccess(
                            $this->__("Total of %d record(s) were successfully updated", count($bannerIds))
                    );
                }
                catch (Exception $e) {
                    $this->_getSession()->addError($e->getMessage());
                }
            }
            $this->_redirect("*/*/index");
        }

        public function exportCsvAction() {
            $fileName = "newsticker.csv";
            $content = $this->getLayout()->createBlock("newsticker/adminhtml_newsticker_grid")->getCsv();
            $this->_sendUploadResponse($fileName, $content);
        }

        public function exportXmlAction() {
            $fileName = "newsticker.xml";
            $content = $this->getLayout()->createBlock("newsticker/adminhtml_newsticker_grid")->getXml();
            $this->_sendUploadResponse($fileName, $content);
        }

        protected function _sendUploadResponse($fileName, $content, $contentType="application/octet-stream") {
            $response = $this->getResponse();
            $response->setHeader("HTTP/1.1 200 OK", "");
            $response->setHeader("Pragma", "public", true);
            $response->setHeader("Cache-Control", "must-revalidate, post-check=0, pre-check=0", true);
            $response->setHeader("Content-Disposition", "attachment; filename=" . $fileName);
            $response->setHeader("Last-Modified", date("r"));
            $response->setHeader("Accept-Ranges", "bytes");
            $response->setHeader("Content-Length", strlen($content));
            $response->setHeader("Content-type", $contentType);
            $response->setBody($content);
            $response->sendResponse();
            die;
        }

    }
