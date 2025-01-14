<?php

namespace local_isycredentials\credential\concept;

defined('MOODLE_INTERNAL') || die();

/**
 * Represents a ISCED-F concept scheme.
 * 
 * @todo Find abstraction for concept schemes with mutiple concept levels. @see local_isycredentials\credential\concept\qdr_concept
 * @todo ISCED-F is a multi-level concept scheme. Find a way to query the other levels. Currently only the top level is queried.
 * 
 * @see http://data.europa.eu/snb/isced-f/25831c2
 */
class iscedf_concept extends concept_vocabulary {
    protected static function getSchemeId(): string {
        return "http://data.europa.eu/snb/isced-f/25831c2";
    }

    protected static function extractConceptIds(\SimpleXMLElement $xml, string $schemeId): array {
        $conceptIds = [];
        $descriptions = $xml->xpath('//rdf:Description[@rdf:about="' . $schemeId . '"]');
        foreach ($descriptions as $description) {
            foreach ($description->xpath('skos:hasTopConcept') as $topConcept) {
                $conceptId = (string) $topConcept->attributes('rdf', true)->resource;
                $conceptIds[] = $conceptId;
            }
        }
        return $conceptIds;
    }
}
