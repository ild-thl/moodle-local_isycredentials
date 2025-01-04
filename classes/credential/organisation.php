<?php

namespace local_isycredentials\credential;

class Organisation extends base_entity {
    public string $type = 'Organisation';
    public array $location;
    public array $legalName;
    public legal_identifier $registration;

    public static function from(string $id, array $location, array $legalName, legal_identifier $registration): self {
        $organisation = new Organisation($id);
        $organisation->location = $location;
        $organisation->legalName = $legalName;
        $organisation->registration = $registration;

        return $organisation;
    }

    public function getId(): string {
        return 'urn:epass:org:' . $this->id;
    }

    public function toArray(): array {
        $data = [
            'id' => $this->getId(),
            'type' => $this->type,
            'location' => array_map(function (Location $location) {
                return $location->toArray();
            }, $this->location),
            'legalName' => $this->legalName,
            'registration' => $this->registration->toArray(),
        ];

        return $data;
    }
}
