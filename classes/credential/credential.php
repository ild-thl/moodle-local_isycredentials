<?php

namespace local_isycredentials\credential;

defined('MOODLE_INTERNAL') || die();

use local_isycredentials\credential\concept\credential_type_concept;
use local_isycredentials\credential\concept\concept_scheme;

/**
 * Class credential
 * 
 * A set of claims made by an issuer in Europe, using the European Standards. A European credential is a set of one or more claims which may be used to demonstrate that the owner has certain skills or has achieved certain learning outcomes through formal, non-formal or informal learning., A special type of verifiable credential used to express learning and employment data, in line with the Council Recommendation of 22 May 2017 on the European Qualifications Framework for lifelong learning.
 * 
 * @see https://europa.eu/europass/elm-browser/documentation/rdf/ap/edc/documentation/edc-generic-no-cv_en.html#european-digital-credential
 */
class credential extends base_entity {
    public string $type = 'VerifiableCredential';
    public array $credentialType = ["VerifiableCredential", "EuropeanDigitalCredential"];

    /**
     * The type of the european digital credential.
     */
    public concept_scheme $credentialSchema;

    /**
     * A label associated to the european digital credential. It should be provided using the EDC Controlled List of Credential Types., A profile of a special type of verifiable credential used to express learning and employment data, in line with the Council Recommendation of 22 May 2017 on the European Qualifications Framework for lifelong learning. If provided, the value must come from the Credential type controlled vocabulary (http://publications.europa.eu/resource/dataset/credential).
     */
    public credential_type_concept $credentialProfiles;

    public array $context = [
        "https://www.w3.org/2018/credentials/v1",
        "http://data.europa.eu/snb/model/context/edc-ap"
    ];

    /**
     * The person (subject) about which claims are made and who owns the credential.
     */
    public credential_subject $credentialSubject;

    /**
     * The display details of the credential.
     */
    public display_parameter $displayParameter;

    /**
     * The agent that issued the credential and sealed it with its digital e-seal. While optional here, it is mandatory for the European Digital Credential to be valid. Should be set during Issuance.
     */
    public ?issuer $issuer = null;

    /**
     * The earliest date when the information associated with the credential subject property became valid. This date can be earlier than the issued date of the credential.
     * 
     * @var int|null A Unix timestamp representing the date when the credential becomes or became valid.
     */
    public int $validFrom;

    /**
     * Defines the date, when the European Digital Credential becomes valid.
     * 
     * @var int|null A Unix timestamp representing the date when the credential becomes valid.
     */
    public ?int $issuanceDate = null;

    /**
     * The date when the credential was issued. While optional here, it is mandatory for the European Digital Credential to be valid. Should be set during Issuance.
     * 
     * @var int|null A Unix timestamp representing the issuance date of the credential.
     */
    public ?int $issued = null;

    /**
     * The date when the credential expires.
     * 
     * @var int|null A Unix timestamp representing the expiration date of the credential.
     */
    public ?int $expirationDate = null;

    /**
     * The date, when the credential expires (automatic invalidation).
     * 
     * @var int|null A Unix timestamp representing the date when the credential expires.
     */
    public ?int $validUntil = null;

    /**
     * Any digital document (PDF, JPEG or PNG format) that an issuer has attached to the European digital credential document., Any digital document (PDF, JPEG or PNG format) that an issuer has attached to the European Digital Credential document.
     *
     * @var media_object[]|null
     */
    public ?array $attachments = null;

    /**
     * Constructor
     *
     * @param credential_subject $credentialSubject
     * @param display_parameter $displayParameter
     * @param int $validFrom
     */
    public function __construct(credential_subject $credentialSubject, display_parameter $displayParameter, int $validFrom) {
        parent::__construct();
        $this->validFrom = $validFrom;
        $this->credentialSubject = $credentialSubject;
        $this->displayParameter = $displayParameter;
        $this->credentialProfiles = credential_type_concept::GENERIC();
        $this->credentialSchema = new concept_scheme(
            'http://data.europa.eu/snb/model/ap/edc-generic-full',
            'ShaclValidator2017',
        );
    }

    public function withExpirationDate(int $expirationDate): self {
        $this->expirationDate = $expirationDate;
        return $this;
    }

    public function withIssuanceDate(int $issuanceDate): self {
        $this->issuanceDate = $issuanceDate;
        return $this;
    }

    public function withIssued(int $issued): self {
        $this->issued = $issued;
        return $this;
    }

    public function withValidUntil(int $validUntil): self {
        $this->validUntil = $validUntil;
        return $this;
    }

    public function withIssuer(issuer $issuer): self {
        $this->issuer = $issuer;
        return $this;
    }

    public function withAttachments(array $attachments): self {
        // Check if the array contains only media_object objects
        foreach ($attachments as $document) {
            if (!($document instanceof media_object)) {
                throw new \InvalidArgumentException('The attachment array must contain only media_object objects');
            }
        }
        $this->attachments = $attachments;
        return $this;
    }

    public function getId(): string {
        return 'urn:credential:' . $this->id;
    }

    public function toArray(): array {
        $data = [
            'id' => $this->getId(),
            'type' => $this->credentialType,
        ];

        $data['credentialProfiles'] = $this->credentialProfiles->toArray();

        $data['displayParameter'] = $this->displayParameter->toArray();

        if (!empty($this->attachments)) {
            $data['attachment'] = array_map(function ($attachment) {
                return $attachment->toArray();
            }, $this->attachments);
        }

        $data['credentialSchema'] = $this->credentialSchema->toArray();

        $data['credentialSubject'] = $this->credentialSubject->toArray();

        if ($this->expirationDate) {
            $data['expirationDate'] = date('Y-m-d\TH:i:sP', $this->expirationDate);
        }

        if ($this->issuanceDate) {
            $data['issuanceDate'] = date('Y-m-d\TH:i:sP', $this->issuanceDate);
        }

        if ($this->issued) {
            $data['issued'] = date('Y-m-d\TH:i:sP', $this->issued);
        }

        if ($this->issuer) {
            $data['issuer'] = $this->issuer->toArray();
        }

        $data['validFrom'] = date('Y-m-d\TH:i:sP', $this->validFrom);

        if ($this->validUntil) {
            $data['validUntil'] = date('Y-m-d\TH:i:sP', $this->validUntil);
        }

        $data['@context'] = $this->context;

        return $data;
    }
}
