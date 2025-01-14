<?php

namespace local_isycredentials\credential;

defined('MOODLE_INTERNAL') || die();

class localized_string {
    private array $translations;
    private static string $primaryLanguage = 'en';
    private static ?array $languageRestrictions = null;

    public function __construct($translations) {
        if (is_string($translations)) {
            $this->translations = [self::$primaryLanguage => $translations];
        } elseif (is_array($translations)) {
            $this->translations = $translations;
        } else {
            throw new \InvalidArgumentException('Invalid argument type for translations. Expected string or array.');
        }

        // Ensure that language keys are valid ISO 639-1 codes and that translations are not empty.
        foreach ($this->translations as $language => $translation) {
            if (!is_string($translation) || empty($translation)) {
                throw new \InvalidArgumentException('Invalid translation. Expected non-empty string.');
            }
        }
    }

    public static function setLanguageRestrictions(array $languages): void {
        self::$languageRestrictions = $languages;
    }

    public static function setPrimaryLanguage(string $language): void {
        self::$primaryLanguage = $language;
    }

    public function getPrimaryLanguageValue(): ?string {
        return $this->translations[self::$primaryLanguage] ?? null;
    }

    public function getTranslations(): array {
        return $this->translations;
    }

    public function getTranslation(string $language): ?string {
        return $this->translations[$language] ?? null;
    }

    public function getFirstTranslation(): ?string {
        return reset($this->translations);
    }

    public function toArray(): array {
        // If language restrictions are set, only return translations for the restricted languages.
        if (self::$languageRestrictions !== null) {
            return array_filter($this->translations, function ($language) {
                return in_array($language, self::$languageRestrictions);
            }, ARRAY_FILTER_USE_KEY);
        } else {
            return $this->translations;
        }
    }

    public static function fromArray(array $data): self {
        return new self($data);
    }
}
