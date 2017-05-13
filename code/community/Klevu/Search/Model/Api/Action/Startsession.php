<?php

class Klevu_Search_Model_Api_Action_Startsession extends Klevu_Search_Model_Api_Action {

    const ENDPOINT = "https://box.klevu.com/rest/service/startSession";
    const METHOD   = "POST";

    const DEFAULT_REQUEST_MODEL  = "klevu_search/api_request_xml";
    const DEFAULT_RESPONSE_MODEL = "klevu_search/api_response_message";

    public function execute($parameters = array()) {
        $validation_result = $this->validate($parameters);
        if ($validation_result !== true) {
            return Mage::getModel('klevu_search/api_response_invalid')->setErrors($validation_result);
        }

        $request = $this->getRequest();

        $request
            ->setResponseModel($this->getResponse())
            ->setEndpoint(static::ENDPOINT)
            ->setMethod(static::METHOD)
            ->setHeader("Authorization", $parameters['api_key']);

        return $request->send();
    }

    protected function validate($parameters) {
        if (!isset($parameters['api_key']) || empty($parameters['api_key'])) {
            return array("Missing API key.");
        } else {
            return true;
        }
    }

}
