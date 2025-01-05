<?php

namespace local_isycredentials\credential;

defined('MOODLE_INTERNAL') || die();

class location extends base_entity {
    public string $type = 'Location';
    public address $address;

    public function __construct(string $id, address $address) {
        parent::__construct($id);
        $this->address = $address;
    }

    public function getId(): string {
        return 'urn:epass:location:' . $this->id;
    }

    public function toArray(): array {
        return [
            'id' => $this->getId(),
            'type' => $this->type,
            'address' => $this->address->toArray(),
        ];
    }
}
