<?php

namespace local_isycredentials;

defined('MOODLE_INTERNAL') || die();

use moodle_exception;

class certificate_manager {
    private $certificate;
    private $certificate_password;
    private $certificate_data;

    public function __construct(string $certificate, string $certificate_password) {
        $this->certificate = $certificate;
        $this->certificate_password = $certificate_password;
        $this->certificate_data = $this->load_certificate();
    }

    /**
     * Loads the certificate from the provided file path.
     * 
     * @return array|null Returns the certificate data or null on failure
     */
    private function load_certificate(): ?array {

        if (!openssl_pkcs12_read($this->certificate, $certs, $this->certificate_password)) {
            return null;
        }

        return [
            'certificate' => $certs['cert'],
            'private_key' => $certs['pkey'],
        ];
    }

    /**
     * Retrieves the certificate data, including the encoded certificate and private key.
     *
     * @return array|null Returns the certificate data or null on failure
     */
    public function get_certificate_data(): ?array {
        return $this->certificate_data;
    }

    /**
     * Signs the provided data with the private key
     *
     * @param string $toSignData The data to sign.
     * @return string The signature value or throws exception if signature fails.
     * @throws Exception If signature fails or if data is empty.
     */
    public function sign_data(string $toSignData): string {
        if (empty($toSignData)) {
            throw new moodle_exception('Error: Could not sign data, provided data is empty');
        }

        if (!$this->certificate_data || !isset($this->certificate_data['private_key'])) {
            throw new moodle_exception('Error: No private key found. Please check your certificate configuration.');
        }

        $signature_sucess = openssl_sign(
            $toSignData,
            $signature,
            $this->certificate_data['private_key'],
            OPENSSL_ALGO_SHA256
        );

        if (!$signature_sucess) {
            throw new moodle_exception('Error: Could not sign data with private key.');
        }

        // Verify the signature
        $public_key = openssl_pkey_get_public($this->certificate_data['certificate']);
        $verify_success = openssl_verify(
            $toSignData,
            $signature,
            $public_key,
            OPENSSL_ALGO_SHA256
        );

        if ($verify_success !== 1) {
            throw new moodle_exception('Error: Could not verify signature.');
        } else {
            debugging('Signature verified successfully.', DEBUG_DEVELOPER);
        }

        return $signature;
    }
}
