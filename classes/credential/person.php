<?php

namespace local_isycredentials\credential;

defined('MOODLE_INTERNAL') || die();


use local_isycredentials\credential\concept\country_concept;

/**
 * Class person
 * 
 * A human being (a natural person).
 * 
 * @see https://europa.eu/europass/elm-browser/documentation/rdf/ap/edc/documentation/edc-generic-no-cv_en.html#person
 */
class person extends base_entity {
    public string $type = 'Person';
    /**
     * The given name of the person.
     */
    public localized_string $givenName;

    /**
     * The family name of the person.
     */
    public localized_string $familyName;

    /**
     * The complete name of the person as one string.
     */
    public localized_string $fullName;

    /**
     * All data associated with an individual is subject to change. Names can change for a variety of reasons, either formally or informally, and new information may come to light that means that a correction or clarification can be made to an existing record. Birth names tend to be persistent however and for this reason they are recorded by some public sector information systems. There is no granularity for birth name - the full name should be recorded in a single field.
     */
    public ?localized_string $birthName = null;

    /**
     * The primary national identifier of the person., The 'primary' national identifier of a human being.
     */
    public ?legal_identifier $nationalID = null;

    /**
     * The date of birth of the person.
     */
    public ?int $dateOfBirth = null;

    /**
     * The entitlement of the person., The contact information of the entity that is able to carry out actions.
     */
    public ?contact_point $contactPoint = null;

    /**
     * The country (or countries) that conferred citizenship rights on the person., The country (or countries) that conferred citizenship rights on a human being. If provided, the value must come from the Country Named Authority List (http://publications.europa.eu/resource/authority/country).
     */
    public ?country_concept $citizenshipCountry = null;

    /**
     * The identifier of the person. E.g. a student id.
     */
    public ?identifier $identifier = null;

    /**
     * The identifiable geographic place where a human being is born., The place of birth of the person.
     */
    public ?location $placeOfBirth = null;

    public function __construct(string $id, string $givenName, string $familyName, string $fullName, ?legal_identifier $nationalID = null, ?int $dateOfBirth = null, ?address $address = null, ?string $email = null, ?country_concept $citizenshipCountry = null, ?identifier $identifier = null, ?location $placeOfBirth = null, ?string $birthName = null) {
        parent::__construct($id);
        $this->givenName = new localized_string($givenName);
        $this->familyName = new localized_string($familyName);
        $this->fullName = new localized_string($fullName);
        $this->nationalID = $nationalID;
        $this->dateOfBirth = $dateOfBirth;
        if ($email || $address) {
            $this->contactPoint = new contact_point(
                $id,
                $address,
                $email,
            );
        }
        $this->citizenshipCountry = $citizenshipCountry;
        $this->identifier = $identifier;
        $this->placeOfBirth = $placeOfBirth;
        $this->birthName = $birthName ? new localized_string($birthName) : null;
    }

    public function getId(): string {
        return 'urn:epass:person:' . $this->id;
    }

    public function toArray(): array {
        $data = [
            'id' => $this->getId(),
            'type' => $this->type,
        ];

        if ($this->contactPoint) {
            $data['contactPoint'] = $this->contactPoint->toArray();
        }

        if ($this->identifier) {
            $data['identifier'] = $this->identifier->toArray();
        }

        if ($this->citizenshipCountry) {
            $data['citizenshipCountry'] = $this->citizenshipCountry->toArray();
        }

        if ($this->dateOfBirth) {
            $data['dateOfBirth'] = date('Y-m-d\TH:i:sP', $this->dateOfBirth);
        }

        $data['familyName'] = $this->familyName->toArray();

        $data['fullName'] = $this->fullName->toArray();

        $data['givenName'] = $this->givenName->toArray();

        if ($this->nationalID) {
            $data['nationalID'] = $this->nationalID->toArray();
        }

        if ($this->placeOfBirth) {
            $data['placeOfBirth'] = $this->placeOfBirth->toArray();
        }

        if ($this->birthName) {
            $data['birthName'] = $this->birthName->toArray();
        }

        return $data;
    }
}
