<?php

    class Webkul_NewsTicker_Adminhtml_NewstickergroupController extends Mage_Adminhtml_Controller_Action {

        protected function _initAction() {
            $this->loadLayout()->_setActiveMenu("newsticker");
            $this->getLayout()->getBlock("head")->setTitle($this->__("Add/Manage News Group"));
            return $this;
        }

        public function indexAction() {
            $this->_initAction()->renderLayout();
        }

        public function bannergridAction() {
            $this->_initAction();
            $this->getResponse()->setBody(
                    $this->getLayout()->createBlock("newsticker/adminhtml_newstickergroup_edit_tab_newsticker")->toHtml()
            );
        }

        public function editAction() {
            $id = $this->getRequest()->getParam("id");
            $model = Mage::getModel("newsticker/newstickergroup")->load($id);
            if ($model->getId() || $id == 0) {
                $data = Mage::getSingleton("adminhtml/session")->getFormData(true);
                if (!empty($data))
                    $model->setData($data);
                Mage::register("newstickergroup_data", $model);
                $this->loadLayout();
                $this->_setActiveMenu("newsticker");
                $this->_addContent($this->getLayout()->createBlock("newsticker/adminhtml_newstickergroup_edit"))
                        ->_addLeft($this->getLayout()->createBlock("newsticker/adminhtml_newstickergroup_edit_tabs"));
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
            $group_id = $this->getRequest()->getParam("id");
            if($data = $this->getRequest()->getPost()) {
                $news = array();
                $availNewsIds = Mage::getModel("newsticker/newsticker")->getAllAvailNewsIds();
                parse_str($data["newstickergroup_newstickers"], $news);
                foreach ($news as $k => $v) {
                    if (preg_match("/[^0-9]+/", $k) || preg_match("/[^0-9]+/", $v)) {
                        unset($news[$k]);
                    }
                }
                $newsIds = array_intersect($availNewsIds, $news);
                $data["news_ids"] = implode(",", $newsIds);
                $data["news_effects"] = (($data["animation_type"] == 0) ? "" : $data["news_effects"]);
                $data["pre_news_effects"] = (($data["animation_type"] == 0) ? $data["pre_news_effects"] : "");
                $model = Mage::getModel("newsticker/newstickergroup");
    			$group_name = $data["group_name"];
    			$number = count($data["cms_pages"]);
    			$aaa = "";
    			for($j=0; $j<count($data["cms_pages"]); $j++)
    			    $aaa = $aaa.$data["cms_pages"][$j].",";
    			$data["cms_pages"] = $aaa;
    			if($group_id != "" || $group_id != NULL){
    				$collection = Mage::getModel("newsticker/newstickergroup")->load($group_id);
    				$newcms = explode(",",$data["cms_pages"]);
    				$oldcms = explode(",",$collection["cms_pages"]);
    				$removecmspageids = array_diff($oldcms,$newcms);
    				if(count($removecmspageids)){
    					foreach($removecmspageids as $removecmspageid)
    					$removecmspage = Mage::getModel("cms/page")->load($removecmspageid);
    					$removepagecon = $removecmspage->getContent();
    					$removeblockcon = "{{block type='newsticker/newsticker' name='".$collection["group_name"]."' newsticker_group_code='".$collection["group_code"]."' template='newsticker/news.phtml'}}";
    					$removepos = strpos($removepagecon, "newsticker_group_code='".$collection["group_code"]."' template='newsticker/news.phtml'");
    					if ($removepos != "" || $removepos != NULL) {
    						str_replace($removeblockcon,"",$removepagecon);
    						$savedata = str_replace($removeblockcon,"",$removepagecon);
    						$removecmspage->setContent($savedata);
    						$removecmspage->save();
    					}
    				}
    				$group_code = $collection->getGroupCode();
    			}
    			else{
    			$group_code = $data["group_code"];
    			}
                $model->setData($data)
                        ->setId($this->getRequest()->getParam("id"));
    		$str="{{block type='newsticker/newsticker' name='".$group_name."' newsticker_group_code='".$group_code."' template='newsticker/news.phtml'}}";	
    			$cms_pages = explode(",",$aaa);
    			for($k=0; $k<$number; $k++){
    				$pagecms = $cms_pages[$k];	
    				$page = Mage::getModel("cms/page")->load($pagecms);
    				$pagecon = $page->getContent();
    				$pos = strpos($pagecon, "template='newsticker/news.phtml'");
					if ($pos == "" || $pos == NULL) {
						$savedata = $str."</br>".$pagecon;
						$page->setStores(array(0)); 
						$page->setContent($savedata);
						$page->save();
					} 
					else{
					    $strsearch = "block type='newsticker/newsticker' name='".$group_name."'";
						$pos1 = strpos($pagecon, $strsearch);
						if($pos1 == "" || $pos1 == NULL) {
							$p = explode("block type='newsticker/newsticker' name=",$pagecon);
							$p1 = explode("newsticker_group_code=",$p[1]);
							$groupname = $p1[0];
							$p2 = explode("template='newsticker/news.phtml'",$p1[1]);
							$groupcode = $p2[0];
							$groupname1 = "'".$group_name."' ";
							$groupcode1 = "'".$group_code."' ";
							$newphrase = $p[0]."block type='newsticker/newsticker' name='".$groupname1."' newsticker_group_code='".$groupcode1."' template='newsticker/news.phtml'".$p2[1] ;
							$page->setStores(array(0)); 
							$page->setContent($newphrase);
							$page->save();
						}
					}
    			}
    		    try {
                    if ($model->getCreatedTime == NULL || $model->getUpdateTime() == NULL)
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
                    Mage::getModel("newsticker/newstickergroup")->load($this->getRequest()->getParam("id"))->delete();
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
            $Ids = $this->getRequest()->getParam("newsticker");
            if (!is_array($Ids))
                Mage::getSingleton("adminhtml/session")->addError(Mage::helper("adminhtml")->__("Please select item(s)"));
            else {
                try {
                    foreach($Ids as $Id)
                        Mage::getModel("newsticker/newstickergroup")->load($Id)->delete();
                    Mage::getSingleton("adminhtml/session")->addSuccess(
                            Mage::helper("adminhtml")->__(
                                    "Total of %d record(s) were successfully deleted", count($Ids)
                            )
                    );
                }
                catch (Exception $e) {
                    Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
                }
            }
            $this->_redirect("*/*/index");
        }

        public function massStatusAction() {
            $Ids = $this->getRequest()->getParam("newsticker");
            if (!is_array($Ids)) {
                Mage::getSingleton("adminhtml/session")->addError($this->__("Please select item(s)"));
            } else {
                try {
                    foreach ($Ids as $Id)
                        $banner = Mage::getSingleton("newsticker/newstickergroup")->load($Id)->setStatus($this->getRequest()->getParam("status"))->save();
                    $this->_getSession()->addSuccess(
                            $this->__("Total of %d record(s) were successfully updated", count($Ids))
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

        protected function _sendUploadResponse($fileName, $content, $contentType = "application/octet-stream") {
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
