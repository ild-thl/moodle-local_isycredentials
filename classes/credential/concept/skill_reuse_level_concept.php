<?php

namespace local_isycredentials\credential\concept;

defined('MOODLE_INTERNAL') || die();

/**
 * Represents a skill-reuse-level concept scheme.
 * 
 * @see http://data.europa.eu/snb/skill-reuse-level/25831c2
 */
class skill_reuse_level_concept extends concept_vocabulary {
    protected static function getSchemeId(): string {
        return "http://data.europa.eu/snb/skill-reuse-level/25831c2";
    }
}
