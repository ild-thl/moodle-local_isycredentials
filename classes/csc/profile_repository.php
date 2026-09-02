<?php

namespace local_isycredentials\csc;

defined('MOODLE_INTERNAL') || die();

class profile_repository {
    public static function for_issuer(string $issuer_id): array {
        $profiles = json_decode(get_config('local_isycredentials', 'csc_issuer_profiles'), true);
        if (!is_array($profiles) || !isset($profiles[$issuer_id]) || !is_array($profiles[$issuer_id])) {
            throw new \moodle_exception('csc_profile_not_found', 'local_isycredentials', '', $issuer_id);
        }
        return $profiles[$issuer_id];
    }
}