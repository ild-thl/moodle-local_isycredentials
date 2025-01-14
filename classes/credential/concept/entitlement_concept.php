<?php

namespace local_isycredentials\credential\concept;

defined('MOODLE_INTERNAL') || die();

/**
 * Represents an entitlement concept scheme.
 * 
 * @see http://data.europa.eu/snb/entitlement/25831c2
 */
class entitlement_concept extends concept_vocabulary {
    protected static function getSchemeId(): string {
        return "http://data.europa.eu/snb/entitlement/25831c2";
    }

    public static function OCCUPATION(): self {
        return self::getById('http://data.europa.eu/snb/entitlement/52f62180c2');
    }

    public static function LEARNING_OPPORTUNITY(): self {
        return self::getById('http://data.europa.eu/snb/entitlement/64aad92881');
    }

    public static function MEMBERSHIP(): self {
        return self::getById('http://data.europa.eu/snb/entitlement/bebd32e8e6');
    }
}
