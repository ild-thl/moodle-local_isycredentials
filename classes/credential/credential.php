<?php

namespace local_isycredentials\credential;

class credential extends base_entity {
    public string $type = 'VerifiableCredential';
    public array $credentialType = ["VerifiableCredential", "EuropeanDigitalCredential"];
    public array $credentialSchema = [
        [
            "id" => "http://data.europa.eu/snb/model/ap/edc-generic-full",
            "type" => "ShaclValidator2017"
        ]
    ];
    public array $credentialProfiles = [
        [
            "id" => "http://data.europa.eu/snb/credential/e34929035b",
            "type" => "Concept",
            "inScheme" => [
                "id" => "http://data.europa.eu/snb/credential/25831c2",
                "type" => "ConceptScheme"
            ],
            "prefLabel" => [
                "de" => [
                    "Generisch"
                ],
                "en" => [
                    "Generic"
                ]
            ]
        ]
    ];
    public credential_subject $credentialSubject;
    public display_parameter $displayParameter;
    public issuer $issuer;
    public string $issuanceDate;
    public string $issued;
    public string $validFrom;
    public ?string $expirationDate = null;
    public ?string $validUntil = null;

    public static function from(credential_subject $credentialSubject, issuer $issuer, display_parameter $displayParameter, string $issuanceDate, string $issued, string $validFrom, ?string $expirationDate, ?string $validUntil): self {
        $credential = new credential();
        $credential->expirationDate = $expirationDate;
        $credential->issuanceDate = $issuanceDate;
        $credential->issued = $issued;
        $credential->validUntil = $validUntil;
        $credential->validFrom = $validFrom;
        $credential->credentialSubject = $credentialSubject;
        $credential->issuer = $issuer;
        $credential->displayParameter = $displayParameter;

        return $credential;
    }

    public function getCredentialKey(): string {
        return '';
    }

    public function getId(): string {
        return 'urn:credential:' . $this->id;
    }

    public function toArray(): array {
        $data = [
            'id' => $this->getId(),
            'type' => $this->credentialType,
            'credentialSchema' =>  $this->credentialSchema,
            'expirationDate' => $this->expirationDate ? date('Y-m-d\TH:i:sP', $this->expirationDate) : null,
            'credentialSubject' => $this->credentialSubject->toArray(),
            'issuanceDate' => $this->issuanceDate ? date('Y-m-d\TH:i:sP', $this->issuanceDate) : null,
            'issued' => $this->issued ? date('Y-m-d\TH:i:sP', $this->issued) : null,
            'validUntil' => $this->validUntil ? date('Y-m-d\TH:i:sP', $this->validUntil) : null,
            'validFrom' => $this->validFrom ? date('Y-m-d\TH:i:sP', $this->validFrom) : null,
            'credentialProfiles' => $this->credentialProfiles,
            'displayParameter' => $this->displayParameter->toArray(),
            'issuer' => $this->issuer->toArray(),
        ];

        return $data;
    }
}
