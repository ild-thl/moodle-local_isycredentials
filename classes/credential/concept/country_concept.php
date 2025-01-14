<?php

namespace local_isycredentials\credential\concept;

defined('MOODLE_INTERNAL') || die();

/**
 * Represents a country concept scheme.
 * 
 * @see http://publications.europa.eu/resource/authority/country
 */
class country_concept extends concept_vocabulary {
    protected static function getSchemeId(): string {
        return "http://publications.europa.eu/resource/authority/country";
    }

    protected static function getWhitelist(): array {
        // Most common countries in Europe and some other countries.
        return array_map(function ($country_code) {
            return "http://publications.europa.eu/resource/authority/country/$country_code";
        }, [
            'AUS', // Australia
            'BGR', // Bulgaria
            'CAN', // Canada
            'CZE', // Czech Republic
            'DEU', // Germany
            'DNK', // Denmark
            'ESP', // Spain
            'EST', // Estonia
            'FIN', // Finland
            'FRA', // France
            'GBR', // United Kingdom
            'GRC', // Greece
            'HRV', // Croatia
            'HUN', // Hungary
            'IND', // India
            'IRL', // Ireland
            'ISL', // Iceland
            'ITA', // Italy
            'LTU', // Lithuania
            'LVA', // Latvia
            'MKD', // North Macedonia
            'MLT', // Malta
            'NOR', // Norway
            'NLD', // Netherlands
            'NZL', // New Zealand
            'PRT', // Portugal
            'POL', // Poland
            'ROU', // Romania
            'RUS', // Russia
            'SLV', // Slovenia
            'SRB', // Serbia
            'SVN', // Slovakia
            'SWE', // Sweden
            'TUR', // Turkey
            'UKR', // Ukraine
            'USA', // United States
        ]);
    }

    /**
     * Get country concept by ISO 3166-1 alpha-3 code.
     *
     * @param string $iso3166_1_alpha_3
     * @return country_concept|null
     */
    public static function getByCode(string $iso3166_1_alpha_3): ?self {
        $iso3166_1_alpha_3 = strtoupper($iso3166_1_alpha_3);
        $all = self::getAll();
        foreach ($all as $item) {
            if (substr($item->getId(), -3) === $iso3166_1_alpha_3) {
                return $item;
            }
        }
        return null;
    }

    /**
     * Get the english language concept.
     *
     * @return self
     */
    public static function ENG(): self {
        return self::getById('http://publications.europa.eu/resource/authority/language/ENG');
    }

    /**
     * Get the german language concept.
     *
     * @return self
     */
    public static function DEU(): self {
        return self::getById('http://publications.europa.eu/resource/authority/language/DEU');
    }
}
