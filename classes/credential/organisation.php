<?php

namespace local_isycredentials\credential;

abstract class organisation extends base_entity {
    public string $type = 'Organisation';
    public location $location;
    public localized_string $legalName;
    public ?string $email = null;

    public function __construct(string $id, address $address, string $legalName, ?string $email = null) {
        parent::__construct($id);
        $this->location = new location(
            $id,
            $address,
        );
        $this->legalName = new localized_string($legalName);
        $this->email = $email;
    }

    public function getId(): string {
        return 'urn:epass:org:' . $this->id;
    }

    public function toArray(): array {
        $data = [
            'id' => $this->getId(),
            'type' => $this->type,
            'location' => [$this->location->toArray()],
            'legalName' => $this->legalName->toArray(),
        ];

        return $data;
    }
}
