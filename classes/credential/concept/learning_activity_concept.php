<?php

namespace local_isycredentials\credential\concept;

defined('MOODLE_INTERNAL') || die();

/**
 * Represents a learning-activity concept scheme.
 * 
 * @see http://data.europa.eu/snb/learning-activity/25831c2
 */
class learning_activity_concept extends concept_vocabulary {
    protected static function getSchemeId(): string {
        return "http://data.europa.eu/snb/learning-activity/25831c2";
    }
}
