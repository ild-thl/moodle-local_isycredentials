<?php

namespace local_isycredentials\credential\concept;

defined('MOODLE_INTERNAL') || die();

use local_isycredentials\credential\localized_string;
use cache;

abstract class concept_vocabulary extends concept {
    public function __construct(string $id, array $prefLabel) {
        parent::__construct($id, new localized_string($prefLabel), new concept_scheme(static::getSchemeId()), static::getNotation());
    }

    abstract protected static function getSchemeId(): string;

    protected static function getNotation(): ?string {
        return null;
    }

    protected static function getWhitelist(): array {
        return [];
    }

    public function getLabel(?string $lang): ?string {
        if ($lang === null) {
            return $this->prefLabel->getFirstTranslation();
        }
        return $this->prefLabel->getTranslation($lang);
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

    public static function getAll(): array {
        $cachedConceptIds = self::getCachedConceptIds();
        if ($cachedConceptIds !== null) {
            debugging("Using cached concept ids", DEBUG_DEVELOPER);
            $concepts = self::getCachedConcepts($cachedConceptIds);
            if ($concepts !== null) {
                debugging("Using cached concepts", DEBUG_DEVELOPER);
                return $concepts;
            }
        }
        debugging("No Cache. Fetching concepts.", DEBUG_DEVELOPER);

        return self::fetchConceptsFromScheme();
    }

    // public static function getById(string $id): ?concept {
    //     $all = self::getAll();
    //     foreach ($all as $item) {
    //         if ($item->getId() === $id) {
    //             return $item;
    //         }
    //     }
    //     return null;
    // }

    private static function fetchXmlContent($url): \SimpleXMLElement {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, "MoodleBot/1.0");
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Follow redirects
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10); // Maximum number of redirects to follow

        $xmlContent = curl_exec($ch);

        if ($xmlContent === false) {
            throw new \Exception("Failed to fetch XML content from $url");
        }

        curl_close($ch);

        $xml = simplexml_load_string($xmlContent);
        if ($xml === false) {
            throw new \Exception("Failed to parse XML content from $url");
        }

        return $xml;
    }

    protected static function extractConceptIds(\SimpleXMLElement $xml, string $schemeId): array {
        $conceptIds = [];
        foreach ($xml->xpath('//rdf:Description') as $description) {
            $conceptId = (string) $description->attributes('rdf', true)->about;
            // Skip if conceptId eq schemeId
            if ($conceptId === $schemeId) {
                continue;
            }
            $conceptIds[] = $conceptId;
        }
        return $conceptIds;
    }

    private static function fetchConceptsFromScheme(): array {
        $schemeId = static::getSchemeId();
        $xml = self::fetchXmlContent($schemeId);

        // Try to get table.id if available to use as notation
        $notation = null;
        foreach ($xml->xpath('//rdf:Description/ns6:table.id') as $tableId) {
            $notation = (string) $tableId;
        }

        $conceptIds = static::getWhitelist();

        if (empty($conceptIds)) {
            $conceptIds = static::extractConceptIds($xml, $schemeId);
        }

        self::setConceptIdsCache($conceptIds);

        $concepts = [];
        foreach ($conceptIds as $conceptId) {
            try {
                $concepts[] = self::fetchConcept($conceptId, $notation);
            } catch (\Exception $e) {
                debugging("Failed to fetch concept $conceptId: " . $e->getMessage(), DEBUG_DEVELOPER);
            }
        }

        if (empty($concepts)) {
            throw new \Exception("No concepts found in scheme " . $schemeId);
        }

        return $concepts;
    }

    private static function fetchConcept(string $conceptId, ?string $notation = null): self {
        $xml = self::fetchXmlContent($conceptId);

        $prefLabel = [];
        foreach ($xml->xpath('//skos:prefLabel') as $prefLabelElement) {
            $lang = (string) $prefLabelElement->attributes('xml', true)->lang;
            $prefLabel[$lang] = (string) $prefLabelElement;
        }

        $concept = new static($conceptId, $prefLabel);
        if ($notation !== null) {
            $concept->withNotation($notation);
        }

        self::setConceptCache($concept);
        return $concept;
    }

    private static function getCache(): cache {
        return cache::make('local_isycredentials', 'concepts');
    }

    private static function setConceptIdsCache(array $conceptIds): void {
        $cache = self::getCache();
        $cacheKey = md5(static::getSchemeId() . '_ids');
        $cache->set($cacheKey, json_encode($conceptIds));
    }

    private static function getCachedConceptIds(): ?array {
        $cache = self::getCache();
        $cacheKey = md5(static::getSchemeId() . '_ids');
        $cachedConceptIds = $cache->get($cacheKey);

        if ($cachedConceptIds === false) {
            return null;
        }

        return json_decode($cachedConceptIds, true);
    }

    private static function getCachedConcepts(string|array $conceptIds): self|array|null {
        $cache = self::getCache();
        try {
            if (is_array($conceptIds)) {
                $cacheKeys = array_map(function ($conceptId) {
                    return md5($conceptId);
                }, $conceptIds);
                $cachedConcepts = $cache->get_many($cacheKeys, MUST_EXIST);
            } else {
                $cacheKey = md5($conceptIds);
                $cachedConcepts = $cache->get($cacheKey, MUST_EXIST);
            }
        } catch (\Exception $_) {
            return null;
        }

        if (is_array($conceptIds)) {
            return array_map(function ($cachedConcept) {
                return self::fromArray(json_decode($cachedConcept, true));
            }, $cachedConcepts);
        } else {
            return self::fromArray(json_decode($cachedConcepts, true));
        }
    }

    public static function fromArray(array $data): self {
        $self = new static($data['id'], $data['prefLabel']);
        if (isset($data['notation'])) {
            $self->withNotation($data['notation']);
        }

        return $self;
    }

    private static function setConceptCache(concept $concept): void {
        $cache = self::getCache();
        $cacheKey = md5($concept->getId());
        $cache->set($cacheKey, json_encode($concept->toArray()));
    }
}
