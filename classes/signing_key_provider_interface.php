<?php

namespace local_isycredentials;

defined('MOODLE_INTERNAL') || die();

interface signing_key_provider_interface {
    public function get_certificate_data(): ?array;

    public function sign_data(string $data_to_sign): string;

    public function get_signature_algorithm(): string;
}