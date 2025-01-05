<?php

namespace local_isycredentials\credential;

enum language_mapping_key: string {
    case ISO6392T = 'Iso6392t';
    case LITERALS = 'literals';
}

class language_mapping {
    private static array $mapping = [
        'en' => [
            'Iso6392t' => 'ENG',
            'literals' => ['de' => ['Englisch'], 'en' => ['English']]
        ],
        'de' => [
            'Iso6392t' => 'DEU',
            'literals' => ['de' => ['Deutsch'], 'en' => ['German']]
        ],
        // Add more mappings as needed
    ];

    /**
     * Get mapped data for ISO 639-1 language codes.
     *
     * @param string $iso6391 The ISO 639-1 language code.
     * @param language_mapping_key|null $targetDataKey The target data key to retrieve specific data.
     * @return mixed An array containing mapped data for the given ISO 639-1 language code.
     * @throws \Exception If the ISO 639-1 code is not found in the mapping.
     */
    public static function getMappedData(string $iso6391, ?language_mapping_key $targetDataKey = null): mixed {
        if (!isset(self::$mapping[$iso6391])) {
            throw new \Exception("ISO 639-1 code '{$iso6391}' not found in the mapping.");
        }
        if ($targetDataKey && !isset(self::$mapping[$iso6391][$targetDataKey->value])) {
            throw new \Exception("Target data key '{$targetDataKey->value}' not found in the mapping for ISO 639-1 code '{$iso6391}'.");
        }

        return $targetDataKey ? self::$mapping[$iso6391][$targetDataKey->value] : self::$mapping[$iso6391];
    }
}
