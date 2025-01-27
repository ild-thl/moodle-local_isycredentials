<?php

namespace local_isycredentials\credential\concept;

defined('MOODLE_INTERNAL') || die();

/**
 * Represents an esco skills concept scheme.
 * 
 * @see http://data.europa.eu/esco/concept-scheme/skills
 */
class esco_skills_concept extends concept_vocabulary {
    protected static function getSchemeId(): string {
        return "http://data.europa.eu/esco/concept-scheme/skills";
    }

    protected static function getWhitelist(): array {
        global $DB;
        // Get all idnumbers from table mdl_competency, that contain "data.europa.eu/esco"
        $sql = "SELECT idnumber FROM {competency} WHERE idnumber LIKE '%data.europa.eu/esco%'";
        $idnumbers = $DB->get_records_sql($sql);
        return array_map(function ($idnumber) {
            return $idnumber->idnumber;
        }, $idnumbers);
    }

    public static function getById(string $conceptId): ?self {
        $cachedConcept = self::getCachedConcepts($conceptId);
        if ($cachedConcept !== null) {
            return $cachedConcept;
        }

        // If cache not found get all concepts
        $allConcepts = self::fetchConceptsFromScheme($conceptId);
        foreach ($allConcepts as $concept) {
            if ($concept->getId() === $conceptId) {
                return $concept;
            }
        }
    }

    protected static function fetchConceptsFromScheme(): array {
        $schemeId = static::getSchemeId();

        $conceptIds = static::getWhitelist();

        if (empty($conceptIds)) {
            return [];
        }

        self::setConceptIdsCache($conceptIds);

        $concepts = [];
        foreach ($conceptIds as $conceptId) {
            try {
                $concepts[] = self::fetchConcept($conceptId, null);
            } catch (\Exception $e) {
                debugging("Failed to fetch concept $conceptId: " . $e->getMessage(), DEBUG_DEVELOPER);
            }
        }

        if (empty($concepts)) {
            throw new \Exception("No concepts found in scheme " . $schemeId);
        }

        return $concepts;
    }

    protected static function fetchConcept(string $conceptId, ?string $notation = null): self {
        // HTTP Request to fetch skill data from ESCO API
        $url = "https://ec.europa.eu/esco/api/resource/skill?uri=$conceptId";
        $response = self::fetchUrl($url);

        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Failed to decode JSON response: " . json_last_error_msg());
        }

        $prefLabel = $data['preferredLabel'];

        $concept = new static($conceptId, $prefLabel);

        self::setConceptCache($concept);
        return $concept;
    }

    protected static function fetchUrl(string $url): string {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($httpCode !== 200) {
            throw new \Exception("Failed to fetch URL $url: HTTP $httpCode");
        }

        return $response;
    }
}
