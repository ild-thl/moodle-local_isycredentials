<?php

namespace local_isycredentials;

use local_isycredentials\credential\credential;
use local_isycredentials\credential\credential_subject;
use local_isycredentials\credential\display_parameter;
use local_isycredentials\credential\localized_string;
use local_isycredentials\credential\concept\language_concept;

class credential_test extends \advanced_testcase {
    public function test_credential_serialization_preserves_required_fields_and_dates(): void {
        $subject = new credential_subject('subject-1', 'Ada', 'Lovelace', 'Ada Lovelace', []);
        $language = language_concept::EN();
        $display = new display_parameter(
            'display-1',
            $language,
            [],
            $language,
            new localized_string('Course completion')
        );
        $credential = (new credential($subject, $display, 1700000000))
            ->withExpirationDate(1701000000)
            ->withIssuanceDate(1700100000)
            ->withIssued(1700100000)
            ->withValidUntil(1701000000);

        $data = $credential->toArray();

        $this->assertSame('urn:credential:' . $credential->id, $data['id']);
        $this->assertSame(['VerifiableCredential', 'EuropeanDigitalCredential'], $data['type']);
        $this->assertSame(['en' => 'Ada'], $data['credentialSubject']['givenName']);
        $this->assertSame(['en' => 'Ada Lovelace'], $data['credentialSubject']['fullName']);
        $this->assertSame(date('Y-m-d\TH:i:sP', 1700000000), $data['validFrom']);
        $this->assertSame(date('Y-m-d\TH:i:sP', 1700100000), $data['issuanceDate']);
        $this->assertSame(date('Y-m-d\TH:i:sP', 1700100000), $data['issued']);
        $this->assertSame(date('Y-m-d\TH:i:sP', 1701000000), $data['expirationDate']);
        $this->assertSame($data['expirationDate'], $data['validUntil']);
        $this->assertContains('https://www.w3.org/2018/credentials/v1', $data['@context']);
    }

    public function test_date_setters_are_fluent_and_update_only_their_date(): void {
        $subject = new credential_subject('subject-1', 'Ada', 'Lovelace', 'Ada Lovelace', []);
        $language = language_concept::EN();
        $display = new display_parameter('display-1', $language, [], $language, new localized_string('Title'));
        $credential = new credential($subject, $display, 1700000000);

        $this->assertSame($credential, $credential->withExpirationDate(1701000000));
        $this->assertSame($credential, $credential->withValidUntil(1701000000));
        $this->assertSame($credential, $credential->withIssuanceDate(1700100000));
        $this->assertSame($credential, $credential->withIssued(1700100000));
        $this->assertSame(1700000000, $credential->validFrom);
        $this->assertSame(1701000000, $credential->expirationDate);
        $this->assertSame(1701000000, $credential->validUntil);
        $this->assertSame(1700100000, $credential->issuanceDate);
        $this->assertSame(1700100000, $credential->issued);
    }

    public function test_optional_expiration_dates_are_not_serialized_when_unset(): void {
        $subject = new credential_subject('subject-1', 'Ada', 'Lovelace', 'Ada Lovelace', []);
        $language = language_concept::EN();
        $display = new display_parameter('display-1', $language, [], $language, new localized_string('Title'));
        $credential = new credential($subject, $display, 1700000000);

        $data = $credential->toArray();
        $this->assertArrayNotHasKey('expirationDate', $data);
        $this->assertArrayNotHasKey('validUntil', $data);
    }

    public function test_json_serialization_is_deterministic_and_preserves_unicode(): void {
        $subject = new credential_subject('subject-1', 'Zoë', 'Lovelace', 'Zoë Lovelace', []);
        $language = language_concept::EN();
        $display = new display_parameter('display-1', $language, [], $language, new localized_string('Titel'));
        $credential = new credential($subject, $display, 1700000000);

        $serialized = $credential->toJson();

        $this->assertSame($serialized, $credential->toJson());
        $this->assertStringContainsString('Zoë', $serialized);
        $this->assertSame($credential->toArray(), json_decode($serialized, true));
    }
}
