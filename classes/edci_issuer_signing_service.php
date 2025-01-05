<?php

namespace local_isycredentials;

defined('MOODLE_INTERNAL') || die();

use local_isycredentials\signing_service_interface;

class edci_issuer_signing_service implements signing_service_interface {
    private $signing_service_url;
    private $certificate_password;

    public function __construct($certificate_password) {
        $this->certificate_password = $certificate_password;

        $this->signing_service_url = get_config('local_isycredentials', 'edci_signing_service_url');
        if (empty($this->signing_service_url) || empty($certificate_password)) {
            throw new \Exception('Error: Plugin settings are not set. Please set the needed values in Site Administration.');
        }
    }

    /**
     * Signs a document using the EDCI Issuer signing service.
     *
     * @param string $document The document to sign.
     * @return string The signed document data.
     * @throws \Exception If validation fails or signing service call fails.
     */
    public function sign(string $document): string {
        $this->validate($document);

        // Convert the document to UTF-8 and minify it.
        $json_data = mb_convert_encoding($document, 'UTF-8', 'auto');
        $document = json_encode(json_decode($json_data), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $sealing_endpoint = '/europass2/edci-issuer/api/v2/public/credentials/seal';

        $temp_file = tempnam(sys_get_temp_dir(), 'json_');
        file_put_contents($temp_file, $document);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->signing_service_url . $sealing_endpoint . '?password=' . urlencode($this->certificate_password));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: multipart/form-data']);
        curl_setopt($curl, CURLOPT_POSTFIELDS, [
            '_file' => new \CURLFile($temp_file, 'application/json', 'document.json')
        ]);

        $response = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);
        unlink($temp_file);

        if ($http_code !== 200) {
            throw new \Exception("Error: Failed to call signing service. HTTP code: {$http_code}. Response: {$response}");
        }

        return $response;
    }

    /**
     * Validates the document to be signed.
     *
     * @param string $document The document to validate.
     * @throws \Exception If validation fails.
     */
    public function validate(string $document): void {
        // Check if the document is valid JSON
        $decoded_document = json_decode($document, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Error: Document is not valid JSON.');
        }

        // Check if the document contains the required structure
        if (!isset($decoded_document['credential']) || !isset($decoded_document['deliveryDetails']['deliveryAddress'])) {
            throw new \Exception('Error: Document does not contain the required structure. Must contain "credential" and "deliveryDetails".');
        }
    }
}
