<?php

namespace local_isycredentials\credential\concept;

defined('MOODLE_INTERNAL') || die();

/**
 * Represents a quropean qualification framework concept scheme.
 * 
 * @see http://data.europa.eu/snb/eqf/25831c2
 */
class eqf_concept extends concept_vocabulary {
    protected static function getSchemeId(): string {
        return "http://data.europa.eu/snb/eqf/25831c2";
    }
}
