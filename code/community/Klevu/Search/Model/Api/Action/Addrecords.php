<?php

class Klevu_Search_Model_Api_Action_Addrecords extends Klevu_Search_Model_Api_Action {

    const ENDPOINT = "https://box.klevu.com/rest/service/addRecords";
    const METHOD   = "POST";

    const DEFAULT_REQUEST_MODEL  = "klevu_search/api_request_xml";
    const DEFAULT_RESPONSE_MODEL = "klevu_search/api_response_message";

    // mandatory_field_name => allowed_empty
    protected $mandatory_fields = array(
        "id"               => false,
        "name"             => false,
        "url"              => false,
        "salePrice"        => false,
        "currency"         => false,
        "category"         => true,
        "listCategory"     => true
    );

    public function execute($parameters = array()) {
        $response = $this->getResponse();

        $validation_result = $this->validate($parameters);
        if ($validation_result !== true) {
            if (count($validation_result) == 1 && isset($validation_result["skipped_records"])) {
                // Validation has removed some of the records due to errors, but the rest
                // can still be submitted, so just log and proceed
                $response->setData("skipped_records", $validation_result["skipped_records"]);
            } else {
                if (isset($validation_result["skipped_records"])) {
                    // Remove all the extra info on skipped records leaving only the error messages
                    for ($i = 1; $i < count($validation_result["skipped_records"]["messages"]); $i++) {
                        $validation_result["skipped_records_" . $i] = $validation_result["skipped_records"]["messages"][$i];
                    }
                    unset($validation_result["skipped_records"]);
                }
                return Mage::getModel('klevu_search/api_response_invalid')->setErrors($validation_result);
            }
        }

        $this->prepareParameters($parameters);

        $request = $this->getRequest();

        $request
            ->setResponseModel($response)
            ->setEndpoint(static::ENDPOINT)
            ->setMethod(static::METHOD)
            ->setData($parameters);

        return $request->send();
    }

    protected function validate(&$parameters) {
        $errors = array();

        if (!isset($parameters['sessionId']) || empty($parameters['sessionId'])) {
            $errors['sessionId'] = "Missing session ID";
        }

        if (isset($parameters['records']) && is_array($parameters['records']) && count($parameters['records']) > 0) {
            $total_records = count($parameters["records"]);
            $skipped_records = array(
                "index"         => array(),
                "messages"      => array()
            );
            foreach ($parameters['records'] as $i => $record) {
                $missing_fields = array();
                $empty_fields = array();

                foreach ($this->mandatory_fields as $mandatory_field => $allowed_empty) {
                    if (!array_key_exists($mandatory_field, $record)) {
                        $missing_fields[] = $mandatory_field;
                    } else {
                        if (!$allowed_empty && !is_numeric($record[$mandatory_field]) && empty($record[$mandatory_field])) {
                            $empty_fields[] = $mandatory_field;
                        }
                    }
                }

                $id = (isset($record['id']) && !empty($record['id'])) ? sprintf(" (id: %d)", $record['id']) : "";

                if (count($missing_fields) > 0 || count($empty_fields) > 0) {
                    unset($parameters["records"][$i]);
                    $skipped_records["index"][] = $i;
                    if (count($missing_fields) > 0) {
                        $skipped_records["messages"][] = sprintf("Record %d%s is missing mandatory fields: %s", $i, $id, implode(", ", $missing_fields));
                    }
                    if (count($empty_fields) > 0) {
                        $skipped_records["messages"][] = sprintf("Record %d%s has empty mandatory fields: %s", $i, $id, implode(", ", $empty_fields));
                    }
                }
            }
            $skipped_count = count($skipped_records["index"]);
            if ($skipped_count > 0) {
                if ($skipped_count == $total_records) {
                    $errors["all_records_invalid"] = implode(", ", $skipped_records["messages"]);
                } else {
                    $errors["skipped_records"] = $skipped_records;
                }
            }
        } else {
            $errors['records'] = "No records";
        }

        if (count($errors) == 0) {
            return true;
        }

        return $errors;
    }

    /**
     * Convert the given parameters to a format expected by the XML request model.
     *
     * @param $parameters
     */
    protected function prepareParameters(&$parameters) {
        foreach ($parameters['records'] as &$record) {
            if (isset($record['listCategory']) && is_array($record['listCategory'])) {
                $record['listCategory'] = implode(";", $record['listCategory']);
            }

            if (isset($record['other']) && is_array($record['other'])) {
                foreach ($record['other'] as $key => &$value) {
                    $key = $this->sanitiseOtherAttribute($key);
                    $value = $this->sanitiseOtherAttribute($value);

                    if (is_array($value)) {
                        $value = implode(",", $value);
                    }

                    $value = sprintf("%s:%s", $key, $value);
                }
                $record['other'] = implode(";", $record['other']);
            }

            $pairs = array();

            foreach ($record as $key => $value) {
                $pairs[] = array(
                    'pair' => array(
                        'key' => $key,
                        'value' => $value
                    )
                );
            }

            $record = array(
                'record' => array(
                    'pairs' => $pairs
                )
            );
        }
    }

    /**
     * Remove the characters used to organise the other attribute values from the
     * passed in string.
     *
     * @param $value
     *
     * @return string
     */
    protected function sanitiseOtherAttribute($value) {
        return preg_replace("/:|,|;/", " ", $value);
    }
}
