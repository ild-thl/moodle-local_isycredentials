<?php

namespace local_isycredentials;

defined('MOODLE_INTERNAL') || die();

interface timestamp_provider_interface {
    public function get_timestamp(string $data): string;
}