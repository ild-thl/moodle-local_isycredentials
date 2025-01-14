<?php

namespace local_isycredentials\credential\concept;

defined('MOODLE_INTERNAL') || die();

/**
 * Represents a learning-assessment concept scheme.
 * 
 * @see http://data.europa.eu/snb/assessment/25831c2
 */
class learning_assessment_concept extends concept_vocabulary {
    protected static function getSchemeId(): string {
        return "http://data.europa.eu/snb/assessment/25831c2";
    }
}
