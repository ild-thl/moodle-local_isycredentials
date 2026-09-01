<?php

namespace local_isycredentials\csc;

defined('MOODLE_INTERNAL') || die();

use local_isycredentials\signing_key_provider_interface;
use local_isycredentials\timestamp_provider_interface;

class signing_key_provider implements signing_key_provider_interface, timestamp_provider_interface {
    private const SHA256_OID = '2.16.840.1.101.3.4.2.1';
    private const SHA512_OID = '2.16.840.1.101.3.4.2.3';
    private const RSA_PKCS1_V15_OID = '1.2.840.113549.1.1.1';
    private const ECDSA_SHA256_OID = '1.2.840.10045.4.3.2';

    private $profile;
    private $http_client;
    private $certificate_data;

    public function __construct(array $profile, ?http_client_interface $http_client = null) {
        $this->validate_profile($profile);
        $this->profile = $profile;
        $this->http_client = $http_client ?? new curl_http_client();
    }

    public function get_certificate_data(): ?array {
        if ($this->certificate_data !== null) {
            return $this->certificate_data;
        }

        $token = $this->authorize_and_get_token([hash('sha256', 'certificate-info', true)]);
        try {
            $info = $this->credential_info($token);
            $chain = $info['cert']['certificates'] ?? $info['certificates'] ?? [];
            if (empty($chain) || !is_string($chain[0])) {
                throw new \moodle_exception('csc_missing_certificate', 'local_isycredentials');
            }
            $this->certificate_data = ['certificate' => $this->pem_certificate(base64_decode($chain[0], true))];
            return $this->certificate_data;
        } finally {
            $this->revoke_token($token);
        }
    }

    public function sign_data(string $data_to_sign): string {
        if ($data_to_sign === '') {
            throw new \moodle_exception('csc_empty_data', 'local_isycredentials');
        }

        $hash = hash('sha256', $data_to_sign, true);
        $token = $this->authorize_and_get_token([$hash]);
        try {
            $response = $this->http_client->post_json($this->api_url('/csc/v2/signatures/signHash'), [
                'credentialID' => $this->profile['credential_id'],
                'hashes' => [base64_encode($hash)],
                'hashAlgorithmOID' => self::SHA256_OID,
                'signAlgo' => $this->sign_algo(),
                'operationMode' => 'A',
            ], $this->bearer_header($token));
            $request_id = $response['responseID'] ?? null;
            if (!is_string($request_id) || $request_id === '') {
                throw new \moodle_exception('csc_missing_request_id', 'local_isycredentials');
            }
            return $this->poll_signature($token, $request_id);
        } finally {
            $this->revoke_token($token);
        }
    }

    public function get_signature_algorithm(): string {
        if (!empty($this->profile['dss_signature_algorithm'])) {
            return $this->profile['dss_signature_algorithm'];
        }
        return $this->sign_algo() === self::ECDSA_SHA256_OID ? 'ECDSA_SHA256' : 'RSA_SHA256';
    }

    public function get_timestamp(string $data): string {
        if ($data === '') {
            throw new \moodle_exception('csc_empty_data', 'local_isycredentials');
        }

        $hash = hash('sha512', $data, true);
        $token = $this->authorize_and_get_token([$hash], self::SHA512_OID);
        try {
            $response = $this->http_client->post_form($this->api_url('/csc/v2/signatures/timestamp'), [
                'hash' => base64_encode($hash),
                'hashAlgo' => self::SHA512_OID,
            ], $this->bearer_header($token));
            $timestamp = $response['timeStampToken'] ?? null;
            if (!is_string($timestamp) || $timestamp === '') {
                debugging('CSC timestamp response did not contain a non-empty timeStampToken. Response structure: '
                    . $this->response_structure($response), DEBUG_DEVELOPER);
                    throw new \moodle_exception('csc_missing_timestamp', 'local_isycredentials');
            }
            return $timestamp;
        } finally {
            $this->revoke_token($token);
        }
    }

    private function authorize_and_get_token(array $hashes, string $hash_algorithm_oid = self::SHA256_OID): string {
        $verifier = $this->base64url_encode(random_bytes(32));
        $challenge = $this->base64url_encode(hash('sha256', $verifier, true));
        $secret = $this->secret('client_secret_env');
        $response = $this->http_client->post_json($this->oauth_url('/oauth2/authorize_tls'), [
            'response_type' => 'code',
            'client_id' => $this->profile['client_id'],
            'redirect_uri' => $this->profile['redirect_uri'],
            'scope' => 'credential',
            'code_challenge' => $challenge,
            'code_challenge_method' => 'S256',
            'numSignatures' => (string) count($hashes),
            'hashAlgorithmOID' => $hash_algorithm_oid,
            'hashes' => implode(',', array_map([$this, 'authorize_hash_encode'], $hashes)),
            'state' => bin2hex(random_bytes(16)),
            'credentialID' => $this->profile['credential_id'],
        ], [], $this->tls_options());

        if (empty($response['code'])) {
            throw new \moodle_exception('csc_missing_authorization_code', 'local_isycredentials');
        }
        $token = $this->http_client->post_form($this->oauth_url('/oauth2/token'), [
            'grant_type' => 'authorization_code',
            'code' => $response['code'],
            'client_id' => $this->profile['client_id'],
            'client_secret' => $secret,
            'redirect_uri' => $this->profile['redirect_uri'],
            'code_verifier' => $verifier,
        ]);
        if (empty($token['access_token'])) {
            throw new \moodle_exception('csc_missing_access_token', 'local_isycredentials');
        }
        return $token['access_token'];
    }

    private function credential_info(string $token): array {
        return $this->http_client->post_json($this->api_url('/csc/v2/credentials/info'), [
            'credentialID' => $this->profile['credential_id'],
            'certificates' => 'chain',
            'certInfo' => true,
            'authInfo' => false,
        ], $this->bearer_header($token));
    }

    private function poll_signature(string $token, string $request_id): string {
        $max_attempts = isset($this->profile['poll_max_attempts']) ? (int) $this->profile['poll_max_attempts'] : 10;
        $delay_microseconds = isset($this->profile['poll_delay_microseconds']) ? (int) $this->profile['poll_delay_microseconds'] : 2000000;
        if ($max_attempts < 1 || $delay_microseconds < 0) {
            throw new \moodle_exception('csc_invalid_profile', 'local_isycredentials', '', 'polling');
        }

        for ($attempt = 1; $attempt <= $max_attempts; $attempt++) {
            try {
                $response = $this->http_client->post_json($this->api_url('/csc/v2/signatures/signPolling'), [
                    'requestID' => $request_id,
                ], $this->bearer_header($token));
                if (isset($response['signatures']) && is_array($response['signatures']) && empty($response['signatures'])) {
                    if ($attempt === $max_attempts) {
                        throw new \moodle_exception('csc_poll_timeout', 'local_isycredentials', '', $request_id);
                    }
                    if ($delay_microseconds > 0) {
                        usleep($delay_microseconds);
                    }
                    continue;
                }
                $signature = $response['signatures'][0] ?? null;
                $decoded_signature = is_string($signature) ? $this->decode_signature($signature) : false;
                if ($decoded_signature === false) {
                    debugging('CSC polling response did not contain a decodable signatures[0]. Response structure: '
                        . $this->response_structure($response), DEBUG_DEVELOPER);
                    throw new \moodle_exception('csc_missing_signature', 'local_isycredentials');
                }
                return $decoded_signature;
            } catch (api_exception $exception) {
                $description = $exception->response_data['error_description'] ?? '';
                if ($description !== 'The previous asynchronous signature request has been accepted for processing, but the processing has not yet been completed.') {
                    throw $exception;
                }
                if ($attempt === $max_attempts) {
                    throw new \moodle_exception('csc_poll_timeout', 'local_isycredentials', '', $request_id);
                }
                if ($delay_microseconds > 0) {
                    usleep($delay_microseconds);
                }
            }
        }

        throw new \moodle_exception('csc_poll_timeout', 'local_isycredentials', '', $request_id);
    }

    private function decode_signature(string $signature) {
        $decoded = base64_decode($signature, true);
        if ($decoded !== false) {
            return $decoded;
        }
        $normalized = strtr($signature, '-_', '+/');
        $padding = strlen($normalized) % 4;
        if ($padding > 0) {
            $normalized .= str_repeat('=', 4 - $padding);
        }
        return base64_decode($normalized, true);
    }

    private function response_structure(array $response): string {
        $structure = [];
        foreach ($response as $key => $value) {
            if (is_array($value)) {
                $structure[$key] = [
                    'type' => 'array',
                    'count' => count($value),
                    'first_type' => isset($value[0]) ? gettype($value[0]) : null,
                    'first_length' => isset($value[0]) && is_string($value[0]) ? strlen($value[0]) : null,
                ];
            } else {
                $structure[$key] = gettype($value);
            }
        }
        return json_encode($structure);
    }

    private function revoke_token(string $token): void {
        try {
            $this->http_client->post_form($this->oauth_url('/oauth2/revoke'), [
                'token' => $token,
                'token_type_hint' => 'access_token',
                'client_id' => $this->profile['client_id'],
                'client_secret' => $this->secret('client_secret_env'),
            ], $this->bearer_header($token));
        } catch (\Throwable $exception) {
            debugging('CSC access-token revocation failed: ' . $exception->getMessage(), DEBUG_DEVELOPER);
        }
    }

    private function bearer_header(string $token): array {
        return ['Authorization: Bearer ' . $token];
    }

    private function oauth_url(string $path): string {
        return rtrim($this->profile['oauth2_url'], '/') . $path;
    }

    private function api_url(string $path): string {
        return rtrim($this->profile['api_url'], '/') . $path;
    }

    private function tls_options(): array {
        return [
            'certificate' => $this->secret('tls_certificate_path_env'),
            'key' => $this->secret('tls_key_path_env'),
            'ca_info' => !empty($this->profile['tls_ca_path_env']) ? $this->secret('tls_ca_path_env') : null,
        ];
    }

    private function secret(string $name): string {
        $value = getenv($this->profile[$name]);
        if ($value === false || $value === '') {
            throw new \moodle_exception('csc_missing_secret', 'local_isycredentials', '', $this->profile[$name]);
        }
        return $value;
    }

    private function validate_profile(array $profile): void {
        foreach (['credential_id', 'client_id', 'oauth2_url', 'api_url', 'redirect_uri', 'client_secret_env', 'tls_certificate_path_env', 'tls_key_path_env'] as $field) {
            if (empty($profile[$field]) || !is_string($profile[$field])) {
                throw new \moodle_exception('csc_invalid_profile', 'local_isycredentials', '', $field);
            }
        }
    }

    private function sign_algo(): string {
        if (!empty($this->profile['sign_algo'])) {
            return $this->profile['sign_algo'];
        }
        if (empty($this->certificate_data)) {
            $this->get_certificate_data();
        }
        $certificate = openssl_x509_read($this->certificate_data['certificate']);
        $public_key = $certificate === false ? false : openssl_pkey_get_public($certificate);
        $details = $public_key === false ? false : openssl_pkey_get_details($public_key);
        if (is_array($details) && ($details['type'] ?? null) === OPENSSL_KEYTYPE_EC) {
            return self::ECDSA_SHA256_OID;
        }
        if (is_array($details) && ($details['type'] ?? null) === OPENSSL_KEYTYPE_RSA) {
            return self::RSA_PKCS1_V15_OID;
        }
        throw new \moodle_exception('csc_unsupported_key_type', 'local_isycredentials');
    }

    private function base64url_encode(string $value): string {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function authorize_hash_encode(string $value): string {
        return $this->base64url_encode($value);
    }

    private function pem_certificate($certificate): string {
        if (!is_string($certificate) || $certificate === '') {
            throw new \moodle_exception('csc_missing_certificate', 'local_isycredentials');
        }
        return "-----BEGIN CERTIFICATE-----\n" . chunk_split(base64_encode($certificate), 64, "\n") . "-----END CERTIFICATE-----\n";
    }
}