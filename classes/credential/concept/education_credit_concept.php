<?php

namespace local_isycredentials\credential\concept;

defined('MOODLE_INTERNAL') || die();

/**
 * Represents a education credit concept scheme.
 * 
 * @see http://data.europa.eu/snb/education-credit/25831c2
 */
class education_credit_concept extends concept_vocabulary {
    protected static function getSchemeId(): string {
        return "http://data.europa.eu/snb/education-credit/25831c2";
    }

    public static function ECTS(): self {
        return self::getById('http://data.europa.eu/snb/education-credit/6fcec5c5af');
    }
}
