<?php

namespace local_isycredentials\credential;

defined('MOODLE_INTERNAL') || die();

class localized_string {
    private array $translations;
    private static string $primaryLanguage = 'en';

    public function __construct($translations) {
        if (is_string($translations)) {
            $this->translations = [self::$primaryLanguage => $translations];
        } elseif (is_array($translations)) {
            $this->translations = $translations;
        } else {
            throw new \InvalidArgumentException('Invalid argument type for translations. Expected string or array.');
        }
    }

    public static function setPrimaryLanguage(string $language): void {
        self::$primaryLanguage = $language;
    }

    public function getPrimaryLanguageValue(): ?string {
        return $this->translations[self::$primaryLanguage] ?? null;
    }

    public function toArray(): array {
        return $this->translations;
    }
}
