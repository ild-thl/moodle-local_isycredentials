<?php

namespace local_isycredentials\credential\concept;

defined('MOODLE_INTERNAL') || die();

/**
 * Represents a concept scheme of all available national qualification frameworks.
 * 
 * @see http://data.europa.eu/snb/qdr/25831c2
 */
class qdr_concept extends concept_vocabulary {
    protected static function getSchemeId(): string {
        return "http://data.europa.eu/snb/qdr/25831c2";
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
