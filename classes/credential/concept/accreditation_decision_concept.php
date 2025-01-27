<?php

namespace local_isycredentials\credential\concept;

defined('MOODLE_INTERNAL') || die();

/**
 * Represents an accreditation decision concept scheme.
 * 
 * @see http://data.europa.eu/snb/accreditation-decision/25831c2
 */
class accreditation_decision_concept extends concept_vocabulary {
    protected static function getSchemeId(): string {
        return "http://data.europa.eu/snb/accreditation-decision/25831c2";
    }
}
