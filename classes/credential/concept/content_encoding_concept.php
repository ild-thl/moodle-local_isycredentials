<?php

namespace local_isycredentials\credential\concept;

defined('MOODLE_INTERNAL') || die();

/**
 * Represents a content encoding concept scheme.
 * 
 * @see http://data.europa.eu/snb/encoding/25831c2
 */
class content_encoding_concept extends concept_vocabulary {
    protected static function getSchemeId(): string {
        return "http://data.europa.eu/snb/encoding/25831c2";
    }

    public static function BASE64(): self {
        return self::getById('http://data.europa.eu/snb/encoding/6146cde7dd');
    }
}
