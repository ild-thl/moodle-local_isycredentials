<?php

namespace local_isycredentials;

defined('MOODLE_INTERNAL') || die();

class badge_credential_mapper_test_db {
    public function get_record(string $table, array $conditions, string $fields = '*', mixed $strict = null): object {
        return (object) [
            'dateissued' => 1700000000,
            'dateexpire' => 1701000000,
        ];
    }

    public function get_records_sql(string $sql, array $params): array {
        return [
            (object) ['value' => 7, 'criteriatype' => 9],
            (object) ['value' => 8, 'criteriatype' => 5],
        ];
    }

    public function get_records_list(string $table, string $field, array $values): array {
        if ($table === 'competency') {
            return [7 => (object) ['id' => 7, 'shortname' => 'Web development']];
        }
        return [8 => (object) ['id' => 8, 'fullname' => 'Intro course']];
    }
}

class badge_credential_mapper_test extends \advanced_testcase {
    public function test_badge_is_mapped_to_validated_elm_credential(): void {
        $credential = $this->mapper()->from_badge(
            (object) ['id' => 1, 'language' => 'en', 'name' => 'Badge'],
            (object) ['id' => 2, 'firstname' => 'Ada', 'lastname' => 'Lovelace'],
        );
        $document = json_decode($credential->toJson(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(['VerifiableCredential', 'EuropeanDigitalCredential'], $document['type']);
        $this->assertSame('Badge', $document['credentialSubject']['hasClaim'][0]['title']['en'][0]);
        $this->assertCount(1, $document['credentialSubject']['hasClaim'][0]['specifiedBy']['learningOutcome']);
        $this->assertCount(1, $document['credentialSubject']['hasClaim'][0]['influencedBy']);
        $this->assertSame('2023-11-26T12:00:00Z', $document['expirationDate']);
    }

    public function test_invalid_badge_language_is_rejected(): void {
        $this->expectException(\InvalidArgumentException::class);
        $this->mapper()->from_badge(
            (object) ['id' => 1, 'language' => 'english', 'name' => 'Badge'],
            (object) ['id' => 2, 'firstname' => 'Ada', 'lastname' => 'Lovelace'],
        );
    }

    private function mapper(): badge_credential_mapper {
        return new badge_credential_mapper(
            new badge_credential_mapper_test_db(),
            static fn (string $name): mixed => [
                'awarding_body_address_country_code' => 'NL',
                'awarding_body_address' => 'Example Street',
                'awarding_body_legal_name' => 'Example Institute',
            ][$name] ?? null,
        );
    }
}