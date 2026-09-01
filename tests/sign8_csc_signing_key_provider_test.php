<?php

namespace local_isycredentials;

defined('MOODLE_INTERNAL') || die();

class sign8_csc_signing_key_provider_test extends \advanced_testcase {
    public function test_signing_hash_is_authorized_signed_polled_and_revoked(): void {
        $this->resetAfterTest();
        putenv('SIGN8_TEST_SECRET=client-secret');
        putenv('SIGN8_TEST_CERT=/run/secrets/sign8-client.crt');
        putenv('SIGN8_TEST_KEY=/run/secrets/sign8-client.key');
        $client = new sign8_fake_http_client([
            ['code' => 'authorization-code'],
            ['access_token' => 'access-token'],
            ['responseID' => 'request-id'],
            ['signatures' => [base64_encode('signature-bytes')]],
            [],
        ]);
        $provider = new sign8_csc_signing_key_provider($this->profile(), $client);

        $signature = $provider->sign_data('DSS data to sign');

        $this->assertSame('signature-bytes', $signature);
        $this->assertSame('/oauth2/authorize_tls', $client->requests[0]['path']);
        $this->assertSame('/oauth2/token', $client->requests[1]['path']);
        $this->assertSame('/csc/v2/signatures/signHash', $client->requests[2]['path']);
        $this->assertSame('/csc/v2/signatures/signPolling', $client->requests[3]['path']);
        $this->assertSame('/oauth2/revoke', $client->requests[4]['path']);
        $this->assertSame(base64_encode(hash('sha256', 'DSS data to sign', true)), $client->requests[2]['data']['hashes'][0]);
        $this->assertSame('2.16.840.1.101.3.4.2.1', $client->requests[2]['data']['hashAlgorithmOID']);
        $this->assertSame('1.2.840.113549.1.1.1', $client->requests[2]['data']['signAlgo']);
        $this->assertSame('client-secret', $client->requests[1]['data']['client_secret']);
        $this->assertArrayNotHasKey('client_secret', $client->requests[0]['data']);
        $this->assertSame('/run/secrets/sign8-client.crt', $client->requests[0]['tls_options']['certificate']);
        $this->assertSame('json', $client->requests[0]['transport']);
        $this->assertIsString($client->requests[0]['data']['hashes']);
        $this->assertSame(
                rtrim(strtr(base64_encode(hash('sha256', 'DSS data to sign', true)), '+/', '-_'), '='),
            $client->requests[0]['data']['hashes']
        );
        $this->assertSame(
            rtrim(strtr(base64_encode(hash('sha256', $client->requests[1]['data']['code_verifier'], true)), '+/', '-_'), '='),
            $client->requests[0]['data']['code_challenge']
        );
    }

    public function test_revoke_is_called_when_signing_fails(): void {
        $this->resetAfterTest();
        putenv('SIGN8_TEST_SECRET=client-secret');
        putenv('SIGN8_TEST_CERT=/run/secrets/sign8-client.crt');
        putenv('SIGN8_TEST_KEY=/run/secrets/sign8-client.key');
        $client = new sign8_fake_http_client([
            ['code' => 'authorization-code'],
            ['access_token' => 'access-token'],
            [],
            [],
        ]);
        $provider = new sign8_csc_signing_key_provider($this->profile(), $client);

        try {
            $provider->sign_data('DSS data to sign');
            $this->fail('Expected a missing request ID exception.');
        } catch (\moodle_exception $exception) {
            $this->assertSame('sign8_missing_request_id', $exception->errorcode);
        }
        $this->assertSame('/oauth2/revoke', $client->requests[3]['path']);
    }

    public function test_pending_poll_is_retried_with_the_same_request_id(): void {
        $this->resetAfterTest();
        putenv('SIGN8_TEST_SECRET=client-secret');
        putenv('SIGN8_TEST_CERT=/run/secrets/sign8-client.crt');
        putenv('SIGN8_TEST_KEY=/run/secrets/sign8-client.key');
        $client = new sign8_fake_http_client([
            ['code' => 'authorization-code'],
            ['access_token' => 'access-token'],
            ['responseID' => 'request-id'],
            new sign8_api_exception(400, [
                'error_description' => 'The previous asynchronous signature request has been accepted for processing, but the processing has not yet been completed.',
            ]),
            ['signatures' => [base64_encode('signature-bytes')]],
            [],
        ]);
        $profile = $this->profile();
        $profile['poll_delay_microseconds'] = 0;
        $provider = new sign8_csc_signing_key_provider($profile, $client);

        $this->assertSame('signature-bytes', $provider->sign_data('DSS data to sign'));
        $this->assertSame('/csc/v2/signatures/signPolling', $client->requests[3]['path']);
        $this->assertSame('/csc/v2/signatures/signPolling', $client->requests[4]['path']);
        $this->assertSame('request-id', $client->requests[3]['data']['requestID']);
        $this->assertSame('request-id', $client->requests[4]['data']['requestID']);
        $this->assertArrayNotHasKey('clientData', $client->requests[2]['data']);
        $this->assertArrayNotHasKey('clientData', $client->requests[3]['data']);
        $this->assertArrayNotHasKey('clientData', $client->requests[4]['data']);
        $this->assertSame('json', $client->requests[3]['transport']);
        $this->assertSame('json', $client->requests[4]['transport']);
        $this->assertContains('Authorization: Bearer access-token', $client->requests[3]['headers']);
        $this->assertContains('Authorization: Bearer access-token', $client->requests[4]['headers']);
        $this->assertSame('/oauth2/revoke', $client->requests[5]['path']);
    }

    public function test_url_safe_base64_signature_is_decoded(): void {
        $this->resetAfterTest();
        putenv('SIGN8_TEST_SECRET=client-secret');
        putenv('SIGN8_TEST_CERT=/run/secrets/sign8-client.crt');
        putenv('SIGN8_TEST_KEY=/run/secrets/sign8-client.key');
        $client = new sign8_fake_http_client([
            ['code' => 'authorization-code'],
            ['access_token' => 'access-token'],
            ['responseID' => 'request-id'],
            ['signatures' => [rtrim(strtr(base64_encode("\xfb\xffsignature"), '+/', '-_'), '=')]],
            [],
        ]);
        $provider = new sign8_csc_signing_key_provider($this->profile(), $client);

        $this->assertSame("\xfb\xffsignature", $provider->sign_data('DSS data to sign'));
    }

    public function test_timestamp_is_requested_with_sha256_hash_and_revoked(): void {
        $this->resetAfterTest();
        putenv('SIGN8_TEST_SECRET=client-secret');
        putenv('SIGN8_TEST_CERT=/run/secrets/sign8-client.crt');
        putenv('SIGN8_TEST_KEY=/run/secrets/sign8-client.key');
        $client = new sign8_fake_http_client([
            ['code' => 'authorization-code'],
            ['access_token' => 'access-token'],
            ['timeStampToken' => base64_encode('timestamp-token')],
            [],
        ]);
        $provider = new sign8_csc_signing_key_provider($this->profile(), $client);

        $this->assertSame(base64_encode('timestamp-token'), $provider->get_timestamp('document-bytes'));
        $this->assertSame('/csc/v2/signatures/timestamp', $client->requests[2]['path']);
        $this->assertSame('form', $client->requests[2]['transport']);
        $this->assertSame(base64_encode(hash('sha512', 'document-bytes', true)), $client->requests[2]['data']['hash']);
        $this->assertSame('2.16.840.1.101.3.4.2.3', $client->requests[2]['data']['hashAlgo']);
        $this->assertSame('2.16.840.1.101.3.4.2.3', $client->requests[0]['data']['hashAlgorithmOID']);
        $this->assertSame('/oauth2/revoke', $client->requests[3]['path']);
    }

    public function test_empty_signature_list_is_polled_again(): void {
        $this->resetAfterTest();
        putenv('SIGN8_TEST_SECRET=client-secret');
        putenv('SIGN8_TEST_CERT=/run/secrets/sign8-client.crt');
        putenv('SIGN8_TEST_KEY=/run/secrets/sign8-client.key');
        $client = new sign8_fake_http_client([
            ['code' => 'authorization-code'],
            ['access_token' => 'access-token'],
            ['responseID' => 'request-id'],
            ['signatures' => []],
            ['signatures' => [base64_encode('signature-bytes')]],
            [],
        ]);
        $profile = $this->profile();
        $profile['poll_delay_microseconds'] = 0;
        $provider = new sign8_csc_signing_key_provider($profile, $client);

        $this->assertSame('signature-bytes', $provider->sign_data('DSS data to sign'));
        $this->assertSame('/csc/v2/signatures/signPolling', $client->requests[3]['path']);
        $this->assertSame('/csc/v2/signatures/signPolling', $client->requests[4]['path']);
        $this->assertSame('/oauth2/revoke', $client->requests[5]['path']);
    }

    public function test_credential_info_returns_the_signing_certificate_and_revokes_token(): void {
        $this->resetAfterTest();
        putenv('SIGN8_TEST_SECRET=client-secret');
        putenv('SIGN8_TEST_CERT=/run/secrets/sign8-client.crt');
        putenv('SIGN8_TEST_KEY=/run/secrets/sign8-client.key');
        $client = new sign8_fake_http_client([
            ['code' => 'authorization-code'],
            ['access_token' => 'access-token'],
            ['cert' => ['certificates' => [base64_encode('certificate-der')]]],
            [],
        ]);
        $provider = new sign8_csc_signing_key_provider($this->profile(), $client);

        $certificate = $provider->get_certificate_data();

        $this->assertStringContainsString('-----BEGIN CERTIFICATE-----', $certificate['certificate']);
        $this->assertSame('/csc/v2/credentials/info', $client->requests[2]['path']);
        $this->assertSame('credential-id', $client->requests[2]['data']['credentialID']);
        $this->assertSame('/oauth2/revoke', $client->requests[3]['path']);
    }

    private function profile(): array {
        return [
            'credential_id' => 'credential-id',
            'client_id' => 'client-id',
            'oauth2_url' => 'https://oauth.example',
            'api_url' => 'https://api.example',
            'redirect_uri' => 'https://moodle.example/sign8',
            'sign_algo' => '1.2.840.113549.1.1.1',
            'dss_signature_algorithm' => 'RSA_SHA256',
            'client_secret_env' => 'SIGN8_TEST_SECRET',
            'tls_certificate_path_env' => 'SIGN8_TEST_CERT',
            'tls_key_path_env' => 'SIGN8_TEST_KEY',
        ];
    }
}

class sign8_fake_http_client implements sign8_http_client_interface {
    public $responses;
    public $requests = [];

    public function __construct(array $responses) {
        $this->responses = $responses;
    }

    public function post_form(string $url, array $data, array $headers = [], array $tls_options = []): array {
        return $this->record('form', $url, $data, $headers, $tls_options);
    }

    public function post_json(string $url, array $data, array $headers = [], array $tls_options = []): array {
        return $this->record('json', $url, $data, $headers, $tls_options);
    }

    private function record(string $transport, string $url, array $data, array $headers, array $tls_options): array {
        $this->requests[] = [
            'transport' => $transport,
            'path' => parse_url($url, PHP_URL_PATH),
            'data' => $data,
            'headers' => $headers,
            'tls_options' => $tls_options,
        ];
        $response = array_shift($this->responses);
        if ($response instanceof \Throwable) {
            throw $response;
        }
        return $response;
    }
}