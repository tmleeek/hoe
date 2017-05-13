<?php

class Klevu_Search_Model_Api_Action_Producttracking extends Klevu_Search_Model_Api_Action {

    const ENDPOINT = "https://box.klevu.com/analytics/productTracking";
    const METHOD   = "POST";

    const DEFAULT_REQUEST_MODEL  = "klevu_search/api_request_post";
    const DEFAULT_RESPONSE_MODEL = "klevu_search/api_response_data";

    protected function validate($parameters) {
        $errors = array();

        if (!isset($parameters["klevu_apiKey"]) || empty($parameters["klevu_apiKey"])) {
            $errors["klevu_apiKey"] = "Missing JS API key.";
        }

        if (!isset($parameters["klevu_type"]) || empty($parameters["klevu_type"])) {
            $errors["klevu_type"] = "Missing type.";
        }

        if (!isset($parameters["klevu_productId"]) || empty($parameters["klevu_productId"])) {
            $errors["klevu_productId"] = "Missing product ID.";
        }

        if (!isset($parameters["klevu_unit"]) || empty($parameters["klevu_unit"])) {
            $errors["klevu_unit"] = "Missing unit.";
        }

        if (!isset($parameters["klevu_salePrice"]) || empty($parameters["klevu_salePrice"])) {
            $errors["klevu_salePrice"] = "Missing sale price.";
        }

        if (!isset($parameters["klevu_currency"]) || empty($parameters["klevu_currency"])) {
            $errors["klevu_currency"] = "Missing currency.";
        }

        if (!isset($parameters["klevu_shopperIP"]) || empty($parameters["klevu_shopperIP"])) {
            $errors["klevu_shopperIP"] = "Missing shopper IP.";
        }

        if (count($errors) == 0) {
            return true;
        }
        return $errors;
    }
}
