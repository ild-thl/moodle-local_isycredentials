<?php

namespace local_isycredentials\credential\concept;

defined('MOODLE_INTERNAL') || die();


/**
 * Represents a supervicion verification concept scheme.
 * 
 * @see http://data.europa.eu/snb/supervision-verification/25831c2
 */
class supervision_verification_concept extends concept_vocabulary {
    protected static function getSchemeId(): string {
        return "http://data.europa.eu/snb/supervision-verification/25831c2";
    }
}
