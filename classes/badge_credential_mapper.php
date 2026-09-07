<?php

namespace local_isycredentials;

defined('MOODLE_INTERNAL') || die();

require_once __DIR__ . '/../vendor/autoload.php';

use DateTimeImmutable;
use IsyThl\EuropeanLearningModel\Core\Address;
use IsyThl\EuropeanLearningModel\Core\AwardingProcess;
use IsyThl\EuropeanLearningModel\Core\Concept;
use IsyThl\EuropeanLearningModel\Core\ConceptScheme;
use IsyThl\EuropeanLearningModel\Core\ElmVocabularySchemes;
use IsyThl\EuropeanLearningModel\Core\LearningActivity;
use IsyThl\EuropeanLearningModel\Core\LearningActivitySpecification;
use IsyThl\EuropeanLearningModel\Core\LearningOutcome;
use IsyThl\EuropeanLearningModel\Core\LocalizedString;
use IsyThl\EuropeanLearningModel\Core\Location;
use IsyThl\EuropeanLearningModel\Core\Note;
use IsyThl\EuropeanLearningModel\Core\Organisation;
use IsyThl\EuropeanLearningModel\Core\Qualification;
use IsyThl\EuropeanLearningModel\Edc\Credential;
use IsyThl\EuropeanLearningModel\Edc\CredentialSubject;
use IsyThl\EuropeanLearningModel\Edc\DisplayParameter;
use IsyThl\EuropeanLearningModel\Edc\LearningAchievement;

final class badge_credential_mapper {
    private const COURSE_CRITERIA_TYPE = 4;
    private const COMPETENCY_CRITERIA_TYPE = 9;

    private readonly object $database;

    /** @var \Closure(string): mixed */
    private readonly \Closure $configReader;

    public function __construct(?object $database = null, ?\Closure $configReader = null) {
        global $DB;
        $this->database = $database ?? $DB;
        $this->configReader = $configReader ?? static fn (string $name): mixed => get_config(
            'local_isycredentials',
            $name,
        );
    }

    public function from_badge(\stdClass $badge, \stdClass $user): Credential {
        $issued = $this->database->get_record(
            'badge_issued',
            ['badgeid' => $badge->id, 'userid' => $user->id],
            '*',
            MUST_EXIST,
        );
        $languageTag = $this->language_tag($badge->language);
        $language = $this->language($languageTag);
        $awardingBody = $this->awarding_body();
        $criteria = $this->criteria_for_badge((int) $badge->id);
        $achievementTitle = new LocalizedString([$languageTag => (string) $badge->name]);
        $outcomes = $this->outcomes_for_badge($criteria, $languageTag);
        $qualification = new Qualification(
            'badge-' . $badge->id,
            $achievementTitle,
            language: $language,
            learningOutcomes: $outcomes,
            publisher: $awardingBody,
        );
        $activities = $this->activities_for_badge($criteria, $awardingBody, $languageTag);
        $achievement = new LearningAchievement(
            'badge-achievement-' . $badge->id,
            $achievementTitle,
            new AwardingProcess('badge-awarding-' . $badge->id, $awardingBody),
            $qualification,
            influencedBy: $activities,
        );
        $subject = new CredentialSubject(
            'user-' . $user->id,
            new LocalizedString([$languageTag => (string) $user->firstname]),
            new LocalizedString([$languageTag => (string) $user->lastname]),
            new LocalizedString([$languageTag => $this->full_name($user)]),
            [$achievement],
        );
        $display = new DisplayParameter(
            'badge-display-' . $badge->id,
            $language,
            $language,
            new LocalizedString([$languageTag => (string) $badge->name]),
        );

        $credential = new Credential(
            'badge-credential-' . $badge->id . '-user-' . $user->id,
            $subject,
            $display,
            new DateTimeImmutable('@' . (int) $issued->dateissued),
            $issued->dateexpire === null ? null : new DateTimeImmutable('@' . (int) $issued->dateexpire),
        );

        return $credential;
    }

    private function language_tag(string $languageCode): string {
        $parts = explode('-', str_replace('_', '-', $languageCode));
        $parts[0] = strtolower($parts[0]);
        if (isset($parts[1])) {
            $parts[1] = strtoupper($parts[1]);
        }
        $languageCode = implode('-', $parts);
        if (preg_match('/^[a-z]{2,3}(?:-[A-Z]{2})?$/', $languageCode) !== 1) {
            throw new \InvalidArgumentException('Badge language must be a valid language tag.');
        }

        return $languageCode;
    }

    private function language(string $languageCode): Concept {
        $notation = strtoupper(substr(explode('-', $languageCode)[0], 0, 3));

        return new Concept(
            'http://publications.europa.eu/resource/authority/language/' . $notation,
            new LocalizedString([$languageCode => $notation]),
            new ConceptScheme(ElmVocabularySchemes::LANGUAGE),
            $notation,
        );
    }

    private function awarding_body(): Organisation {
        $countryCode = strtoupper((string) $this->config('awarding_body_address_country_code'));
        if (preg_match('/^[A-Z]{3}$/', $countryCode) !== 1) {
            throw new \InvalidArgumentException('The awarding body country code must contain three letters.');
        }
        $country = new Concept(
            'http://publications.europa.eu/resource/authority/country/' . $countryCode,
            new LocalizedString(['en' => $countryCode]),
            new ConceptScheme(ElmVocabularySchemes::COUNTRY),
            $countryCode,
        );
        $address = new Address(
            'awarding-body-address',
            $country,
            new Note(
                'awarding-body-address-note',
                new LocalizedString(['en' => (string) $this->config('awarding_body_address')]),
            ),
        );

        return new Organisation(
            'awarding-body',
            new Location('awarding-body-location', $address),
            new LocalizedString([
                'en' => (string) $this->config('awarding_body_legal_name'),
            ]),
        );
    }

    /** @return list<object> */
    private function criteria_for_badge(int $badgeId): array {
        $records = $this->database->get_records_sql(
            'SELECT bcp.value, bc.criteriatype
               FROM {badge_criteria} bc
               JOIN {badge_criteria_param} bcp ON bc.id = bcp.critid
              WHERE bc.badgeid = :badgeid',
            ['badgeid' => $badgeId],
        );
        $supported = array_filter(
            $records,
            static fn (object $record): bool => in_array(
                (int) $record->criteriatype,
                [self::COURSE_CRITERIA_TYPE, self::COMPETENCY_CRITERIA_TYPE],
                true,
            ),
        );
        if ($supported === []) {
            throw new \InvalidArgumentException('Badge must contain a course or competency criterion.');
        }

        return array_values($supported);
    }

    /** @param list<object> $criteria @return list<LearningOutcome> */
    private function outcomes_for_badge(array $criteria, string $language): array {
        $records = array_filter(
            $criteria,
            static fn (object $record): bool => (int) $record->criteriatype === self::COMPETENCY_CRITERIA_TYPE,
        );
        if ($records === []) {
            return [];
        }
        $competencies = $this->database->get_records_list('competency', 'id', array_map(
            static fn (object $record): int => (int) $record->value,
            $records,
        ));

        return array_values(array_map(
            static fn (object $competency): LearningOutcome => new LearningOutcome(
                'competency-' . $competency->id,
                new LocalizedString([$language => (string) ($competency->shortname ?? $competency->description)]),
            ),
            $competencies,
        ));
    }

    /** @param list<object> $criteria @return list<LearningActivity> */
    private function activities_for_badge(array $criteria, Organisation $awardingBody, string $language): array {
        $records = array_filter(
            $criteria,
            static fn (object $record): bool => (int) $record->criteriatype === self::COURSE_CRITERIA_TYPE,
        );
        if ($records === []) {
            return [];
        }
        $courses = $this->database->get_records_list('course', 'id', array_map(
            static fn (object $record): int => (int) $record->value,
            $records,
        ));

        return array_values(array_map(
            static function (object $course) use ($awardingBody, $language): LearningActivity {
                $title = new LocalizedString([$language => (string) $course->fullname]);
                return new LearningActivity(
                    'course-' . $course->id,
                    $title,
                    new AwardingProcess('course-awarding-' . $course->id, $awardingBody),
                    new LearningActivitySpecification('course-specification-' . $course->id, $title),
                );
            },
            $courses,
        ));
    }

    private function full_name(\stdClass $user): string {
        return trim((string) ($user->firstname ?? '') . ' ' . (string) ($user->lastname ?? ''));
    }

    private function config(string $name): mixed {
        return ($this->configReader)($name);
    }
}