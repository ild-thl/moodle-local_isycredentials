<?php

namespace local_isycredentials;

require_once __DIR__ . '/../lib.php';

use local_isycredentials\csc\profile_repository;

class profile_repository_test extends \advanced_testcase {
    public function test_profile_is_selected_by_trusted_issuer_id(): void {
        $this->resetAfterTest(true);
        set_config('csc_issuer_profiles', json_encode([
            'trusted-issuer' => ['credential_id' => 'trusted-credential'],
            'document-issuer' => ['credential_id' => 'document-credential'],
        ]), 'local_isycredentials');

        $profile = profile_repository::for_issuer('trusted-issuer');

        $this->assertSame('trusted-credential', $profile['credential_id']);
    }

    public function test_signing_rejects_document_with_different_issuer(): void {
        $this->resetAfterTest(true);
        set_config('elm_issuer_data', json_encode(['id' => 'trusted-issuer']), 'local_isycredentials');
        set_config('csc_issuer_profiles', json_encode([
            'trusted-issuer' => ['credential_id' => 'trusted-credential'],
            'document-issuer' => ['credential_id' => 'document-credential'],
        ]), 'local_isycredentials');

        $this->expectException(\moodle_exception::class);
        $this->expectExceptionMessage('does not match the configured issuer');

        local_isycredentials_sign_document(json_encode([
            'issuer' => ['id' => 'document-issuer'],
        ]));
    }
}
