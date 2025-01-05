<?php

namespace local_isycredentials\credential;

defined('MOODLE_INTERNAL') || die();

class credential extends base_entity {
    public string $type = 'VerifiableCredential';
    public array $credentialType = ["VerifiableCredential", "EuropeanDigitalCredential"];
    public array $credentialSchema = [
        "id" => "http://data.europa.eu/snb/model/ap/edc-generic-full",
        "type" => "ShaclValidator2017"
    ];
    public array $credentialProfiles = [
        "id" => "http://data.europa.eu/snb/credential/e34929035b",
        "type" => "Concept",
        "inScheme" => [
            "id" => "http://data.europa.eu/snb/credential/25831c2",
            "type" => "ConceptScheme"
        ],
        "prefLabel" => [
            "de" => "Generisch",
            "en" => "Generic"

        ]
    ];
    public array $context = [
        "https://www.w3.org/2018/credentials/v1",
        "http://data.europa.eu/snb/model/context/edc-ap"
    ];
    public credential_subject $credentialSubject;
    public display_parameter $displayParameter;
    public issuer $issuer;
    public string $issuanceDate;
    public string $issued;
    public string $validFrom;
    public ?string $expirationDate = null;
    public ?string $validUntil = null;

    public function __construct(credential_subject $credentialSubject, issuer $issuer, display_parameter $displayParameter, string $issuanceDate, string $issued, string $validFrom, ?string $expirationDate, ?string $validUntil) {
        parent::__construct();
        $this->expirationDate = $expirationDate;
        $this->issuanceDate = $issuanceDate;
        $this->issued = $issued;
        $this->validUntil = $validUntil;
        $this->validFrom = $validFrom;
        $this->credentialSubject = $credentialSubject;
        $this->issuer = $issuer;
        $this->displayParameter = $displayParameter;
    }

    public function getId(): string {
        return 'urn:credential:' . $this->id;
    }

    public function toArray(): array {
        $data = [
            'id' => $this->getId(),
            'type' => $this->credentialType,
            'credentialProfiles' => $this->credentialProfiles,
            'displayParameter' => $this->displayParameter->toArray(),
            'credentialSchema' =>  $this->credentialSchema,
            'credentialSubject' => $this->credentialSubject->toArray(),
            'expirationDate' => $this->expirationDate ? date('Y-m-d\TH:i:sP', $this->expirationDate) : null,
            // 'issuanceDate' => $this->issuanceDate ? date('Y-m-d\TH:i:sP', $this->issuanceDate) : null,
            // 'issued' => $this->issued ? date('Y-m-d\TH:i:sP', $this->issued) : null,
            // 'issuer' => $this->issuer->toArray(), // issuance values will be added later during the issuance process
            'validFrom' => $this->validFrom ? date('Y-m-d\TH:i:sP', $this->validFrom) : null,
            'validUntil' => $this->validUntil ? date('Y-m-d\TH:i:sP', $this->validUntil) : null,
            '@context' => $this->context,
        ];

        return $data;
    }
}
