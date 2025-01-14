<?php

namespace local_isycredentials\credential\concept;

defined('MOODLE_INTERNAL') || die();

/**
 * Represents a Learning opportunity type concept scheme.
 * 
 * @see http://data.europa.eu/snb/learning-opportunity/25831c2
 */
class learning_opportunity_concept extends concept_vocabulary {
    protected static function getSchemeId(): string {
        return "http://data.europa.eu/snb/learning-opportunity/25831c2";
    }
}
