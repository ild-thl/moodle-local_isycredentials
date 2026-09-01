<?php

namespace local_isycredentials;

defined('MOODLE_INTERNAL') || die();

interface sign8_http_client_interface {
    public function post_form(string $url, array $data, array $headers = [], array $tls_options = []): array;

    public function post_json(string $url, array $data, array $headers = [], array $tls_options = []): array;
}