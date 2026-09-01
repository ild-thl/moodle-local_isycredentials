<?php

namespace local_isycredentials\csc;

defined('MOODLE_INTERNAL') || die();

class profile_repository {
    public static function for_document(string $document): array {
        $issuer_id = self::issuer_id($document);
        $profiles = json_decode(get_config('local_isycredentials', 'csc_issuer_profiles'), true);
        if (!is_array($profiles) || !isset($profiles[$issuer_id]) || !is_array($profiles[$issuer_id])) {
            throw new \moodle_exception('csc_profile_not_found', 'local_isycredentials', '', $issuer_id);
        }
        return $profiles[$issuer_id];
    }

    private static function issuer_id(string $document): string {
        $data = json_decode($document, true);
        if (is_array($data) && !empty($data['issuer']['id']) && is_string($data['issuer']['id'])) {
            return $data['issuer']['id'];
        }

        $issuer = json_decode(get_config('local_isycredentials', 'elm_issuer_data'), true);
        if (!is_array($issuer) || empty($issuer['id']) || !is_string($issuer['id'])) {
            throw new \moodle_exception('csc_invalid_issuer', 'local_isycredentials');
        }
        return $issuer['id'];
    }
}