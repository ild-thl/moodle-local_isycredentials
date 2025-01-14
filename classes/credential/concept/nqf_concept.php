<?php

namespace local_isycredentials\credential\concept;

defined('MOODLE_INTERNAL') || die();

/**
 * Represents a specific national qualification framework.
 * 
 * The SchemeId will be one of the values from the qdr_concept scheme.
 * 
 * Example getting the concepts of the german national qualification framework:
 * 
 * $nqf = qdr_concept::getById('http://data.europa.eu/snb/qdr/c_ef113b94');
 * nqf_concept::setSchemeId($nqf->getId());
 * $concepts = nqf_concept::getAll();
 */
class nqf_concept extends concept_vocabulary {
    protected static ?string $schemeId = null;

    protected static function getSchemeId(): string {
        return self::$schemeId;
    }

    public static function setSchemeId(string $schemeId): void {
        self::$schemeId = $schemeId;
    }
}
