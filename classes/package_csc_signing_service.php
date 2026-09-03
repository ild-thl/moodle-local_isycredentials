<?php

namespace local_isycredentials;

defined('MOODLE_INTERNAL') || die();

require_once __DIR__ . '/../vendor/autoload.php';

use IsyThl\Signing\Csc\CscSigningProvider;
use IsyThl\Signing\Dss\DssSigner;
use IsyThl\Signing\Dss\DssValidator;
use IsyThl\Signing\Exception\SigningException;
use IsyThl\Signing\Http\CurlHttpClient;
use IsyThl\Signing\Logging\LoggerInterface;
use IsyThl\Signing\Security\EnvironmentSecretResolver;

final class package_csc_signing_service implements signing_service_interface {
    private DssSigner $signer;

    /**
     * @param array<string, mixed> $profile
     * @param array<string, mixed> $issuer
     */
    public function __construct(array $profile, array $issuer) {
        $packageProfile = $this->packageProfile($profile);
        $logger = get_config('local_isycredentials', 'csc_debug_logging')
            ? new class implements LoggerInterface {
                /** @param array<string, scalar|null> $context */
                public function debug(string $message, array $context = []): void {
                    debugging($message . ' ' . json_encode($context), DEBUG_DEVELOPER);
                }
            }
            : null;
        $httpClient = new CurlHttpClient(null, $logger);
        $provider = new CscSigningProvider(
            $packageProfile,
            $httpClient,
            new EnvironmentSecretResolver()
        );
        $serviceUrl = (string) get_config('local_isycredentials', 'dss_signing_service_url');
        if ($serviceUrl === '') {
            throw new SigningException('DSS signing service URL is not configured.');
        }
        $allowInsecureTransport = (bool) get_config('local_isycredentials', 'allow_insecure_dss_transport');
        if (!$allowInsecureTransport && parse_url($serviceUrl, PHP_URL_SCHEME) !== 'https') {
            throw new SigningException('DSS service URL must use HTTPS.');
        }
        $this->signer = new DssSigner(
            $httpClient,
            $provider,
            $serviceUrl,
            $provider,
            $provider,
            null,
            new DssValidator($httpClient, $serviceUrl, '/validation/validateDocument', $allowInsecureTransport),
            $allowInsecureTransport
        );
        $this->issuer = $issuer;
    }

    /** @var array<string, mixed> */
    private array $issuer;

    public function sign(string $document): string {
        $now = gmdate('Y-m-d\\TH:i:s\\Z');
        return $this->signer->sign($document, [
            'issuanceDate' => $now,
            'issued' => $now,
            'issuer' => $this->issuer,
        ]);
    }

    public function validate(string $document): void {
        $decoded = json_decode($document, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            throw new SigningException('Document must be a JSON object.');
        }
    }

    /** @param array<string, mixed> $profile */
    private function packageProfile(array $profile): array {
        foreach (['client_secret_env', 'tls_certificate_path_env', 'tls_key_path_env'] as $legacyKey) {
            if (empty($profile[$legacyKey]) || !is_string($profile[$legacyKey])) {
                throw new SigningException('CSC profile is missing: ' . $legacyKey);
            }
        }
        $profile['client_secret'] = $profile['client_secret_env'];
        $profile['tls_certificate'] = $profile['tls_certificate_path_env'];
        $profile['tls_key'] = $profile['tls_key_path_env'];
        return $profile;
    }
}
