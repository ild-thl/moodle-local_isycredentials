<?php

namespace local_isycredentials\credential\concept;

defined('MOODLE_INTERNAL') || die();

/**
 * Represents a credential type concept scheme.
 * 
 * @see http://data.europa.eu/snb/credential/25831c2
 */
class credential_type_concept extends concept_vocabulary {
    protected static function getSchemeId(): string {
        return "http://data.europa.eu/snb/credential/25831c2";
    }

    public static function GENERIC(): self {
        return self::getById('http://data.europa.eu/snb/credential/e34929035b');
    }
}
