<?php

namespace local_isycredentials;

class dss_signing_service_test extends \advanced_testcase {
    public function test_signing_orchestrates_timestamp_data_to_sign_signature_and_signed_document(): void {
        $this->resetAfterTest();
        set_config('dss_signing_service_url', 'https://dss.example/services/rest/signature', 'local_isycredentials');
        set_config('elm_issuer_data', json_encode([
            'id' => 'urn:epass:org:test',
            'type' => 'Organisation',
        ]), 'local_isycredentials');

        $provider = new dss_test_signing_provider();
        $service = new dss_test_signing_service($provider);
        $signed_document = $service->sign(json_encode([
            'id' => 'urn:credential:test',
            'expirationDate' => '2030-01-01T00:00:00Z',
        ]));

        $this->assertSame('{"signed":true}', $signed_document);
        $this->assertSame(['timestamp', 'getDataToSign', 'signDocument'], $service->calls);
        $this->assertSame('dss-data-to-sign', $provider->signing_input);
        $this->assertSame('RSA_SHA256', $provider->algorithm);

        $data_to_sign_request = $service->requests[0]['body'];
        $enriched_document_bytes = base64_decode($data_to_sign_request['toSignDocument']['bytes']);
        $enriched_document = json_decode($enriched_document_bytes, true);
        $this->assertSame($enriched_document_bytes, $provider->timestamp_input);
        $this->assertSame('urn:epass:org:test', $enriched_document['issuer']['id']);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z$/', $enriched_document['issuanceDate']);
        $this->assertSame($enriched_document['issuanceDate'], $enriched_document['issued']);
        $this->assertSame('timestamp-token', $data_to_sign_request['parameters']['contentTimestamps'][0]['binaries']);

        $sign_document_request = $service->requests[1]['body'];
        $this->assertSame('RSA_SHA256', $sign_document_request['signatureValue']['algorithm']);
        $this->assertSame(base64_encode('signature-bytes'), $sign_document_request['signatureValue']['value']);
    }

    public function test_invalid_json_is_rejected_before_any_signing_step(): void {
        $this->resetAfterTest();
        set_config('dss_signing_service_url', 'https://dss.example/services/rest/signature', 'local_isycredentials');
        $provider = new dss_test_signing_provider();
        $service = new dss_test_signing_service($provider);

        $this->expectException(\Exception::class);
        $service->sign('{invalid json');
        $this->assertSame([], $service->calls);
    }
}

class dss_test_signing_service extends dss_signing_service {
    public array $calls = [];
    public array $requests = [];

    protected function call_signing_service(string $url, array $request_body): ?array {
        $this->requests[] = ['url' => $url, 'body' => $request_body];
        if (str_ends_with($url, '/getDataToSign')) {
            $this->calls[] = 'getDataToSign';
            return ['bytes' => base64_encode('dss-data-to-sign')];
        }
        $this->calls[] = 'signDocument';
        return ['bytes' => base64_encode('{"signed":true}')];
    }

    public function request_timestamp_document($document): array {
        $this->calls[] = 'timestamp';
        return parent::request_timestamp_document($document);
    }
}

class dss_test_signing_provider implements signing_key_provider_interface, timestamp_provider_interface {
    public ?string $timestamp_input = null;
    public ?string $signing_input = null;
    public string $algorithm = '';

    public function get_certificate_data(): ?array {
        return ['certificate' => 'certificate-data'];
    }

    public function get_timestamp(string $data): string {
        $this->timestamp_input = $data;
        return 'timestamp-token';
    }

    public function sign_data(string $data_to_sign): string {
        $this->signing_input = $data_to_sign;
        return 'signature-bytes';
    }

    public function get_signature_algorithm(): string {
        $this->algorithm = 'RSA_SHA256';
        return $this->algorithm;
    }
}
