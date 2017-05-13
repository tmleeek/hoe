<?php
    class Bc_Feedback_IndexController extends Mage_Core_Controller_Front_Action
    {
        const XML_PATH_EMAIL_RECIPIENT  = 'feedback/feedback/feedback_from_mail';
        const XML_PATH_EMAIL_SENDER     = 'contacts/email/sender_email_identity';
        const XML_PATH_EMAIL_CONTACTS     = 'contacts/email/recipient_email';
        public function indexAction()
        {

            $this->loadLayout();     
            $this->getLayout()->getBlock('head')->setTitle(Mage::helper('customer')->__('Feedback Form'));
            $this->renderLayout();

        }

        public function postAction(){
            {  
                $post = $this->getRequest()->getPost();
                if ($post) {
                    $translate = Mage::getSingleton('core/translate');
                    /* @var $translate Mage_Core_Model_Translate */
                    $translate->setTranslateInline(false);
                    try {
                        $postObject = new Varien_Object();

                        $post['feedbackdetails'] = nl2br($post['feedbackdetails']);
                        if($post['feedbackheard'] == null || $post['feedbackheard'] == '')
                        {
                            $post['feedbackheard'] = 'N/A';
                        }

                        $postObject->setData($post);

                        $error = false;
                        if(!empty($post['feedbackbuname'])){
                            if (!Zend_Validate::is(trim($post['feedbackbuname']) , 'NotEmpty')) {
                                $error = true;
                            }    
                        }
                        if (!Zend_Validate::is(trim($post['feedbackmail']), 'EmailAddress')) {
                            $error = true;
                        }
                        if(!empty($post['feedbackdetails'])){
                            if (!Zend_Validate::is(trim($post['feedbackdetails']) , 'NotEmpty')) {
                                $error = true;
                            }    
                        }

                        if ($error) {
                            throw new Exception();
                        }
                        $privatekey = Mage::getStoreConfig("feedback/recaptcha_settings/private_key");
                        // check response
                        $resp = true;
                        $var1["recaptcha"]=Mage::getStoreConfig("feedback/recaptcha_settings/enable_recaptcha");
                        if(Mage::getStoreConfig("feedback/recaptcha_settings/enable_recaptcha"))
                        {
                            $resp = Mage::helper("feedback")->recaptcha_check_answer(  $privatekey,
                                $_SERVER["REMOTE_ADDR"],
                                $post["recaptcha_challenge_field"],
                                $post["recaptcha_response_field"]
                            );

                        }

                        if ($resp == true)
                        { 
                            Mage::getSingleton('core/session')->unsetData('contactForm');

                            $recipient="";
                            if(Mage::getStoreConfig(self::XML_PATH_EMAIL_RECIPIENT)==""){
                                $recipient=Mage::getStoreConfig(self::XML_PATH_EMAIL_CONTACTS);
                            }else{
                                $recipient=Mage::getStoreConfig(self::XML_PATH_EMAIL_RECIPIENT);
                            }
                            $store=Mage::app()->getStore();
                            $mailTemplate = Mage::getModel('core/email_template');
                            /* @var $mailTemplate Mage_Core_Model_Email_Template */
                            $mailTemplate->setDesignConfig(array('area' => 'frontend'))
                            ->setReplyTo($post['feedbackmail'])
                            ->sendTransactional(
                                'feedback_email_template',
                                Mage::getStoreConfig(self::XML_PATH_EMAIL_SENDER),
                                $recipient,
                                null,
                                array(
                                    'data' => $postObject,
                                    'store'=> $store
                                )
                            );

                            if (!$mailTemplate->getSentSuccess()) {
                                throw new Exception();
                            }
                            $translate->setTranslateInline(true);
                            $var1["result"]="success";
                            $var1["message"]='Your request has been sent';
                            $data=json_encode($var1);
                            $this->getResponse()->setBody($data);
                            return;
                        }
                        else
                        { 
                            $_SESSION['feedbackbuname'] = $_POST['feedbackbuname'];
                            $_SESSION['feedbackmail'] = $_POST['feedbackmail'];
                            $_SESSION['feedbackdetails'] = $_POST['feedbackdetails'];

                            $var1["result"]="error";
                            $message= "Your reCAPTCHA entry is incorrect. Please try again.";

                            $var1["message"]=$message;
                            $data=json_encode($var1);
                            $this->getResponse()->setBody($data);
                            return;
                        }




                    } catch (Exception $e) {
                        $var1["result"]="error";
                        $message=$e->getMessage();
                        if($message==""){
                            $var1["message"]="Unable to submit your request. Please, try again later";    
                        }else{
                            $var1["message"]=$message;
                        }
                        $data=json_encode($var1);
                        $this->getResponse()->setBody($data);
                        return;
                    }

                } 
                else {
                    $var1["result"]="error";
                    $var1["message"]="Unable to submit your request. Please, try again later";
                    $data=json_encode($var1);
                    $this->getResponse()->setBody($data);
                    return;
                }
            }
        }

    }
