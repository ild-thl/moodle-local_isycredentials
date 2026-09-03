<?php

namespace local_isycredentials;

require_once __DIR__ . '/../classes/package_csc_signing_service.php';

use IsyThl\Signing\Exception\SigningException;

class package_csc_signing_service_test extends \advanced_testcase {
    public function test_constructor_rejects_profile_without_secret_configuration(): void {
        $this->resetAfterTest(true);

        $this->expectException(SigningException::class);
        $this->expectExceptionMessage('CSC profile is missing: client_secret_env');

        new package_csc_signing_service([], ['id' => 'trusted-issuer']);
    }

    public function test_constructor_rejects_missing_dss_url_after_profile_validation(): void {
        $this->resetAfterTest(true);
        set_config('dss_signing_service_url', '', 'local_isycredentials');

        $this->expectException(SigningException::class);
        $this->expectExceptionMessage('DSS signing service URL is not configured.');

        new package_csc_signing_service($this->profile(), ['id' => 'trusted-issuer']);
    }

    public function test_constructor_rejects_insecure_dss_url_by_default(): void {
        $this->resetAfterTest(true);
        set_config('dss_signing_service_url', 'http://dss:8080/services/rest/signature', 'local_isycredentials');

        $this->expectException(SigningException::class);
        $this->expectExceptionMessage('DSS service URL must use HTTPS.');
        new package_csc_signing_service($this->profile(), ['id' => 'trusted-issuer']);
    }

    public function test_constructor_rejects_profile_without_tls_certificate_configuration(): void {
        $this->resetAfterTest(true);
        $profile = $this->profile();
        unset($profile['tls_certificate_path_env']);

        $this->expectException(SigningException::class);
        $this->expectExceptionMessage('CSC profile is missing: tls_certificate_path_env');

        new package_csc_signing_service($profile, ['id' => 'trusted-issuer']);
    }

    /** @return array<string, string> */
    private function profile(): array {
        return [
            'credential_id' => 'credential-id',
            'client_id' => 'client-id',
            'oauth2_url' => 'https://oauth.example',
            'api_url' => 'https://api.example',
            'redirect_uri' => 'https://client.example/callback',
            'sign_algo' => '1.2.840.113549.1.1.1',
            'client_secret_env' => 'CLIENT_SECRET',
            'tls_certificate_path_env' => 'TLS_CERTIFICATE',
            'tls_key_path_env' => 'TLS_KEY',
        ];
    }
}
