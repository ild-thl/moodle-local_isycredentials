<?php

namespace local_isycredentials\credential;

use factor_email\form\email;

defined('MOODLE_INTERNAL') || die();

class organisation extends base_entity {
    public string $type = 'Organisation';
    public location $location;
    public localized_string $legalName;
    /**
     * The registration is a fundamental relationship between a legal entity and the authority with which it is registered and that confers legal status upon it. rov:registration is a sub property of adms:identifier which has a range of adms:Identifier. rov:registration has a domain of rov:RegisteredOrganization.
     */
    public ?legal_identifier $registration = null;
    public ?contact_point $contact_point = null;
    public ?web_resource $homepage = null;
    public ?media_object $logo = null;

    public function __construct(string $id, address $address, string $legalName) {
        parent::__construct($id);
        $this->location = new location(
            $id,
            $address,
        );
        $this->legalName = new localized_string($legalName);
    }

    public function withLegalIdentifier(legal_identifier $registration): self {
        $this->registration = $registration;
        return $this;
    }

    public function withContactAddress(address $address): self {
        if ($this->contact_point) {
            $this->contact_point->withAddress($address);
        } else {
            $this->contact_point = new contact_point(
                $this->id,
                $address,
            );
        }
        return $this;
    }

    public function withEmail(email_address $email): self {
        if ($this->contact_point) {
            $this->contact_point->withEmail($email);
        } else {
            $this->contact_point = new contact_point(
                $this->id,
                null,
                $email,
            );
        }
        return $this;
    }

    public function getId(): string {
        return 'urn:epass:org:' . $this->id;
    }

    public function toArray(): array {
        $data = [
            'id' => $this->getId(),
            'type' => $this->type,
        ];

        if ($this->contact_point) {
            $data['contactPoint'] = $this->contact_point->toArray();
        }

        $data['location'] = $this->location->toArray();

        if ($this->homepage) {
            $data['homepage'] = $this->homepage->toArray();
        }

        $data['legalName'] = $this->legalName->toArray();

        if ($this->logo) {
            $data['logo'] = $this->logo->toArray();
        }

        if ($this->registration) {
            $data['registration'] = $this->registration->toArray();
        }

        return $data;
    }
}
