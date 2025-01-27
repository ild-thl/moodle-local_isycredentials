<?php

namespace local_isycredentials\credential;

defined('MOODLE_INTERNAL') || die();

use local_isycredentials\credential\concept\credential_type_concept;
use local_isycredentials\credential\concept\concept_scheme;
use local_isycredentials\credential\address;
use local_isycredentials\credential\concept\language_concept;
use local_isycredentials\credential\concept\country_concept;
use local_isycredentials\credential\credential_subject;
use local_isycredentials\credential\display_parameter;
use local_isycredentials\credential\email_address;
use local_isycredentials\credential\individual_display;
use local_isycredentials\credential\legal_identifier;
use local_isycredentials\credential\localized_string;
use local_isycredentials\credential\organisation;

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

    /**
     * Create a credential from a moodle badge and a user object.
     * 
     * The resulting credential will use the badges awarding criteria to create suitable claims.
     * Currently supports course completion and competency awarded criteria.
     *
     * @param \stdClass $badge The badges db record
     * @param \stdClass $user The recipients user db record
     * @return self
     */
    public static function fromBadge(\stdClass $badge, \stdClass $user): self {
        global $DB;
        $badge_issued = $DB->get_record('badge_issued', ['badgeid' => $badge->id, 'userid' => $user->id], '*', MUST_EXIST);

        // Set the primary language
        localized_string::setPrimaryLanguage($badge->language);
        localized_string::setLanguageRestrictions(array_unique([$badge->language, 'de', 'en']));

        // Create language concept
        $languageConcept = language_concept::getByCode($badge->language);
        if (!$languageConcept) {
            debugging("Language concept not found for language code: " . $badge->language, DEBUG_DEVELOPER);
            $languageConcept = language_concept::EN();
            localized_string::setPrimaryLanguage('en');
        }

        // Get country code and name from settings
        $countryCode = get_config('local_isycredentials', 'awarding_body_address_country_code');
        $countryNameJson = get_config('local_isycredentials', 'awarding_body_address_country_name');
        $countryName = json_decode($countryNameJson, true);

        // Create concept for country code
        $countryConcept = new country_concept(
            "http://publications.europa.eu/resource/authority/country/{$countryCode}",
            $countryName,
            $countryCode
        );

        // Create address
        $address = new address(
            '1',
            $countryConcept,
            get_config('local_isycredentials', 'awarding_body_address')
        );

        // Create legal identifier
        $legalIdentifier = new legal_identifier(
            '1',
            get_config('local_isycredentials', 'awarding_body_legal_identifier'),
            $countryConcept
        );

        // Create awarding body
        $awarding_body = new organisation(
            '1',
            $address,
            get_config('local_isycredentials', 'awarding_body_legal_name'),
            $legalIdentifier,
            get_config('local_isycredentials', 'awarding_body_email'),
        );
        $awarding_body->withLegalIdentifier($legalIdentifier)
            ->withEmail(new email_address(get_config('local_isycredentials', 'awarding_body_email')));

        $claims[] = learning_achievement::fromBadge($badge, $awarding_body);

        // Create credential subject
        $credentialSubject = new credential_subject(
            '1',
            $user->firstname,
            $user->lastname,
            \core_user::get_fullname($user),
            $claims
        );

        //Create Individual display showing the badge image
        $individualDisplay = new individual_display(
            $languageConcept,
            $badge,
        );

        // Create Display Parameter
        $displayParameter = new display_parameter(
            '1',
            $languageConcept,
            [$individualDisplay],
            $languageConcept,
            new localized_string($badge->name),
        );


        return new self(
            $credentialSubject,
            $displayParameter,
            $badge_issued->dateissued,
            $badge_issued->dateexpire,
            $badge_issued->dateexpire,
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
