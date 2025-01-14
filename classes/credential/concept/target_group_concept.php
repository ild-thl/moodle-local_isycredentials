<?php

namespace local_isycredentials\credential\concept;

defined('MOODLE_INTERNAL') || die();

/**
 * Represents a target group concept scheme.
 * 
 * @see http://data.europa.eu/snb/target-group/25831c2
 */
class target_group_concept extends concept_vocabulary {
    protected static function getSchemeId(): string {
        return "http://data.europa.eu/snb/target-group/25831c2";
    }
}
