<?php

class Klevu_Search_Model_Api_Action_Deleterecords extends Klevu_Search_Model_Api_Action_Addrecords {

    const ENDPOINT = "https://box.klevu.com/rest/service/deleteRecords";
    const METHOD   = "POST";

    // mandatory_field_name => allowed_empty
    protected $mandatory_fields = array(
        "id" => false
    );
}
