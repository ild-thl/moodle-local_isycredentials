<?php

namespace local_isycredentials\credential\concept;

defined('MOODLE_INTERNAL') || die();

/**
 * Represents the Digital Competence Framework concept scheme.
 * 
 * @see http://data.europa.eu/snb/dcf/25831c2
 */
class dcf_skills_concept extends concept_vocabulary {
    protected static function getSchemeId(): string {
        return "http://data.europa.eu/snb/dcf/25831c2";
    }

    protected static function getWhitelist(): array {
        global $DB;
        // Get all idnumbers from table mdl_competency, that contain "data.europa.eu/esco"
        $sql = "SELECT idnumber FROM {competency} WHERE idnumber LIKE '%data.europa.eu/snb/dcf%'";
        $idnumbers = $DB->get_records_sql($sql);
        return array_map(function ($idnumber) {
            return $idnumber->idnumber;
        }, $idnumbers);
    }
}
