<?php

namespace local_isycredentials\credential\concept;

defined('MOODLE_INTERNAL') || die();

/**
 * Represents a language concept scheme.
 *
 * @see http://publications.europa.eu/resource/authority/language
 */
class language_concept extends concept_vocabulary {
    private static array $iso639_1_to_alpha_3 = [
        'bg' => 'BUL', // Bulgarian
        'cs' => 'CES', // Czech
        'da' => 'DAN', // Danish
        'de' => 'DEU', // German
        'el' => 'ELL', // Greek
        'en' => 'ENG', // English
        'et' => 'EST', // Estonian
        'fi' => 'FIN', // Finnish
        'fr' => 'FRA', // French
        'ga' => 'GLE', // Irish
        'hr' => 'HRV', // Croatian
        'hu' => 'HUN', // Hungarian
        'is' => 'ISL', // Icelandic
        'it' => 'ITA', // Italian
        'lv' => 'LAV', // Latvian
        'lt' => 'LIT', // Lithuanian
        'mk' => 'MKD', // Macedonian
        'mt' => 'MLT', // Maltese
        'nl' => 'NLD', // Dutch
        'no' => 'NOR', // Norwegian
        'pl' => 'POL', // Polish
        'pt' => 'POR', // Portuguese
        'ro' => 'RON', // Romanian
        'ru' => 'RUS', // Russian
        'sk' => 'SLK', // Slovak
        'sl' => 'SLV', // Slovenian
        'es' => 'SPA', // Spanish
        'sr' => 'SRP', // Serbian
        'sv' => 'SWE', // Swedish
        'tr' => 'TUR', // Turkish
        'uk' => 'UKR', // Ukrainian
    ];

    protected static function getSchemeId(): string {
        return "http://publications.europa.eu/resource/authority/language";
    }

    protected static function getWhitelist(): array {
        return array_map(function ($lang_code) {
            return "http://publications.europa.eu/resource/authority/language/$lang_code";
        }, array_values(self::$iso639_1_to_alpha_3));
    }

    /**
     * Get language concept by ISO 639-1 code.
     *
     * @param string $iso639_1 The ISO 639-1 code.
     * @return language_concept|null The language concept with the given ISO 639-1 code, or null if not found.
     */
    public static function getByCode(string $iso639_1): ?self {
        $alpha_3 = self::$iso639_1_to_alpha_3[strtolower($iso639_1)] ?? null;
        if ($alpha_3 === null) {
            return null;
        }
        return self::getById("http://publications.europa.eu/resource/authority/language/$alpha_3");
    }

    /**
     * Get the english language concept.
     *
     * @return self
     */
    public static function EN(): self {
        return self::getById('http://publications.europa.eu/resource/authority/language/ENG');
    }

    /**
     * Get the german language concept.
     *
     * @return self
     */
    public static function DE(): self {
        return self::getById('http://publications.europa.eu/resource/authority/language/DEU');
    }
}
