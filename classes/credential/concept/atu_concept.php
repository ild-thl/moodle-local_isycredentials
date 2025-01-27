<?php

namespace local_isycredentials\credential\concept;

defined('MOODLE_INTERNAL') || die();

/**
 * Represents an administrative territorial unit concept scheme.
 * 
 * @see http://publications.europa.eu/resource/authority/atu
 */
class atu_concept extends concept_vocabulary {
    protected static function getSchemeId(): string {
        return "http://publications.europa.eu/resource/authority/atu";
    }
}
