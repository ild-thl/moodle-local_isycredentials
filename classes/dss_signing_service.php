<?php

namespace local_isycredentials;

defined('MOODLE_INTERNAL') || die();

use moodle_exception;

use local_isycredentials\signing_service_interface;

class dss_signing_service implements signing_service_interface {
    private $certificate_manager;
    private $signing_service_url;
    private $certificate_data;
    private $request_body_base;

    public function __construct($certificate, $certificate_password) {
        $this->certificate_manager = new certificate_manager($certificate, $certificate_password);
        $this->certificate_data = $this->certificate_manager->get_certificate_data();
        if (!$this->certificate_data) {
            throw new \Exception('Error: Could not load certificate data. Please check your certificate configuration in Site Administration.');
        }

        $this->signing_service_url = get_config('local_isycredentials', 'dss_signing_service_url');
        if (empty($this->signing_service_url) || empty($certificate_password)) {
            throw new \Exception('Error: Plugin settings are not set. Please set the needed values in Site Administration.');
        }
    }

    /**
     * Signs a document using the DSS signing service.
     *
     * @param string $document The document to sign.
     * @return string The signed document data.
     */
    public function sign(string $document): string {
        $this->validate($document);

        // Add issuance process information
        $document = $this->add_issuance_process_info($document);

        // Start the signing process
        $data_to_sign = $this->request_data_to_sign($document);
        $signature_value = $this->certificate_manager->sign_data($data_to_sign);
        return $this->request_sign_document($signature_value);
    }

    /**
     * Adds issuance process information to the document.
     *
     * @param string $document The document to add the information to.
     * @return string The updated document.
     */
    private function add_issuance_process_info(string $document): string {
        // Convert the document to UTF-8 and minify it.
        $document = mb_convert_encoding($document, 'UTF-8', 'auto');
        $document_data = json_decode($document, true);

        $current_time = gmdate('Y-m-d\TH:i:s\Z');
        $issuer_info = json_decode(get_config('local_isycredentials', 'elm_issuer_data'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Error: Could not decode issuer data as valid json. Please check your configuration in Site Administration.');
        }

        // Insert new values after the key "expirationDate"
        $new_values = [
            'issuanceDate' => $current_time,
            'issued' => $current_time,
            'issuer' => $issuer_info
        ];

        $updated_document_data = [];
        foreach ($document_data as $key => $value) {
            $updated_document_data[$key] = $value;
            if ($key === 'expirationDate') {
                $updated_document_data = array_merge($updated_document_data, $new_values);
            }
        }

        // If "expirationDate" is not found, append the new values at the end
        if (!isset($document_data['expirationDate'])) {
            $updated_document_data = array_merge($updated_document_data, $new_values);
        }

        return json_encode($updated_document_data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Validates the document to be signed.
     *
     * @param string $document The document to validate.
     * @throws \Exception If validation fails.
     */
    public function validate(string $document): void {
        // Check if the document is valid JSON
        json_decode($document, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Error: Document is not valid JSON.');
        }
    }

    /**
     * Calls the /getDataToSign endpoint of the signing service
     *
     * @param string $toSignDocument The document to sign.
     * @return string The data to sign.
     * @throws Exception If any error occurs during the http request to the signing service.
     */
    public function request_data_to_sign($toSignDocument): string {
        $endpoint = $this->signing_service_url . '/one-document/getDataToSign';

        // Timestamp the document
        $timestamp_data = $this->request_timestamp_document($toSignDocument);

        $this->request_body_base = $this->generate_request_body_base($toSignDocument, $timestamp_data);
        $response = $this->call_signing_service($endpoint, $this->request_body_base);

        if (!$response || !isset($response['bytes'])) {
            throw new moodle_exception('Error: Could not retrieve data to sign from service.');
        }

        return base64_decode($response['bytes']);
    }

    /**
     * Calls the /timestampDocument endpoint of the signing service
     *
     * @param string $toTimpstampDocument The document to sign.
     * @return array The timestamped document data.
     * @throws Exception If any error occurs during the http request to the signing service.
     */
    public function request_timestamp_document($toTimpstampDocument): array {
        $endpoint = $this->signing_service_url . '/one-document/timestampDocument';
        $request_body = [
            'timestampParameters' => [
                'digestAlgorithm' => 'SHA512',
                'canonicalizationMethod' => 'http://www.w3.org/2001/10/xml-exc-c14n#',
                'timestampContainerForm' => 'ASiC_S',
            ],
            'toTimestampDocument' => [
                'bytes' => base64_encode($toTimpstampDocument),
            ]
        ];

        $response = $this->call_signing_service($endpoint, $request_body);
        return $response;
    }

    /**
     * Calls the /signDocument endpoint of the signing service
     *
     * @param string $signatureValue The signature value.
     * @return string The signed document data.
     * @throws Exception If any error occurs during the http request to the signing service.
     */
    public function request_sign_document(string $signatureValue): string {
        $endpoint = $this->signing_service_url . '/one-document/signDocument';
        $request_body = $this->request_body_base;
        $request_body['signatureValue'] = [
            'algorithm' => 'RSA_SHA256',
            'value' => base64_encode($signatureValue),
        ];

        $response = $this->call_signing_service($endpoint, $request_body);

        if (!$response || !isset($response['bytes'])) {
            throw new moodle_exception('Error: Could not retrieve signed document from service.');
        }
        return base64_decode($response['bytes']);
    }

    /**
     * Makes the call to the signing service
     *
     * @param string $url The URL of the endpoint.
     * @param array $request_body The data to be sent.
     * @return array|null The response array or null on failure.
     * @throws Exception If any error occurs during the http request to the signing service.
     */
    private function call_signing_service(string $url, array $request_body): ?array {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($request_body));
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        debugging('Request to ' . $url, DEBUG_DEVELOPER);

        $request_debug = json_decode(json_encode($request_body), true);
        if (isset($request_debug['toSignDocument'])) {
            $request_debug['toSignDocument']['bytes'] = substr($request_debug['toSignDocument']['bytes'], 0, 50) . '...'; // Truncate to 50 characters
        }
        if (isset($request_debug['signatureValue'])) {
            $request_debug['signatureValue']['value'] = substr($request_debug['signatureValue']['value'], 0, 50) . '...'; // Truncate to 50 characters
        }
        if (isset($request_debug['toTimestampDocument'])) {
            $request_debug['toTimestampDocument']['bytes'] = substr($request_debug['toTimestampDocument']['bytes'], 0, 50) . '...'; // Truncate to 50 characters
        }
        if (isset($request_debug['bytes'])) {
            $request_debug['bytes'] = substr($request_debug['bytes'], 0, 50) . '...'; // Truncate to 50 characters
        }
        if (isset($request_debug['parameters']['signingCertificate'])) {
            $request_debug['parameters']['signingCertificate']['encodedCertificate'] = substr($request_debug['parameters']['signingCertificate']['encodedCertificate'], 0, 50) . '...'; // Truncate to 50 characters
        }
        debugging(' - Request Body: ' . json_encode($request_debug), DEBUG_DEVELOPER);

        $response = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ($http_code !== 200) {
            $error_message = curl_error($curl);
            curl_close($curl);
            throw new moodle_exception("Error: Failed to call signing service. HTTP code: {$http_code}. Error message: {$error_message} Response: {$response}");
        }
        curl_close($curl);

        $response_debug = json_decode($response, true);
        if (isset($response_debug['bytes'])) {
            $response_debug['bytes'] = substr($response_debug['bytes'], 0, 50) . '...'; // Truncate to 50 characters
        }
        debugging(' - Response Body: ' . json_encode($response_debug), DEBUG_DEVELOPER);


        return json_decode($response, true);
    }

    /**
     * Generates the base request body for the signature service.
     *
     * @param string $toSignDocument Base64 encoded document to sign.
     * @return array The generated request body array.
     */
    private function generate_request_body_base(string $toSignDocument, array $timestamp_data): array {
        return  [
            'parameters' => [
                'signingCertificate' => [
                    'encodedCertificate' => base64_encode($this->certificate_data['certificate'])
                ],
                'certificateChain' => [],
                'detachedContents' => null,
                'asicContainerType' => null,
                'signatureLevel' => 'JAdES_BASELINE_LTA',
                'signaturePackaging' => 'ENVELOPING',
                'embedXML' => false,
                'manifestSignature' => false,
                'jwsSerializationType' => 'JSON_SERIALIZATION',
                'sigDMechanism' => null,
                'base64UrlEncodedPayload' => false,
                'base64UrlEncodedEtsiUComponents' => true,
                'signatureAlgorithm' => null,
                'digestAlgorithm' => 'SHA256',
                'encryptionAlgorithm' => 'RSA',
                'maskGenerationFunction' => null,
                'referenceDigestAlgorithm' => null,
                'contentTimestamps' => [
                    [
                        'binaries' => $timestamp_data['bytes'],
                        'type' => 'DOCUMENT_TIMESTAMP',
                        'canonicalizationMethod' => null,
                        'includes' => null,
                    ]
                ],
                'contentTimestampParameters' => [
                    'digestAlgorithm' => 'SHA512',
                    'canonicalizationMethod' => 'http://www.w3.org/2001/10/xml-exc-c14n#',
                    'timestampContainerForm' => 'ASiC_S',
                ],
                'signatureTimestampParameters' => [
                    'digestAlgorithm' => 'SHA512',
                    'canonicalizationMethod' => 'http://www.w3.org/2001/10/xml-exc-c14n#',
                    'timestampContainerForm' => null,
                ],
                'archiveTimestampParameters' => [
                    'digestAlgorithm' => 'SHA512',
                    'canonicalizationMethod' => 'http://www.w3.org/2001/10/xml-exc-c14n#',
                    'timestampContainerForm' => null,
                ],
                'signWithExpiredCertificate' => false,
                'generateTBSWithoutCertificate' => false,
                'imageParameters' => null,
                'signatureIdToCounterSign' => null,
                'blevelParams' => [
                    'trustAnchorBPPolicy' => true,
                    'signingDate' => time() * 1000, // Current timestamp in milliseconds
                    'claimedSignerRoles' => null,
                    'signedAssertions' => null,
                    'policyId' => null,
                    'policyQualifier' => null,
                    'policyDescription' => null,
                    'policyDigestAlgorithm' => null,
                    'policyDigestValue' => null,
                    'policySpuri' => null,
                    'commitmentTypeIndications' => null,
                    'signerLocationPostalAddress' => [],
                    'signerLocationPostalCode' => null,
                    'signerLocationLocality' => null,
                    'signerLocationStateOrProvince' => null,
                    'signerLocationCountry' => null,
                    'signerLocationStreet' => null,
                ],
            ],
            'toSignDocument' => [
                'bytes' => base64_encode($toSignDocument),
                'digestAlgorithm' => null,
                'name' => 'RemoteDocument',
            ]
        ];
    }
}
