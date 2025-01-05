<?php

namespace local_isycredentials\credential;

use factor_email\form\email;

defined('MOODLE_INTERNAL') || die();

abstract class organisation extends base_entity {
    public string $type = 'Organisation';
    public location $location;
    public localized_string $legalName;
    public ?contact_point $contact_point = null;

    public function __construct(string $id, address $address, string $legalName, ?string $email = null, ?address $contactAddress = null) {
        parent::__construct($id);
        $this->location = new location(
            $id,
            $address,
        );
        $this->legalName = new localized_string($legalName);
        if ($email || $contactAddress) {
            $this->contact_point = new contact_point(
                $id,
                $address,
                $email,
            );
        }
    }

    public function getId(): string {
        return 'urn:epass:org:' . $this->id;
    }

    public function toArray(): array {
        $data = [
            'id' => $this->getId(),
            'type' => $this->type,
            'contactPoint' => $this->contact_point ? $this->contact_point->toArray() : null,
            'location' => $this->location->toArray(),
            'legalName' => $this->legalName->toArray(),
        ];

        return $data;
    }
}
