<?php

namespace local_isycredentials;

defined('MOODLE_INTERNAL') || die();

class sign8_api_exception extends \moodle_exception {
    /** @var int */
    public $http_code;

    /** @var array */
    public $response_data;

    public function __construct(int $http_code, array $response_data, string $details = '') {
        $this->http_code = $http_code;
        $this->response_data = $response_data;
        parent::__construct('sign8_request_failed', 'local_isycredentials', '', null, $details);
    }
}