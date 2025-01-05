<?php

namespace local_isycredentials\credential;

defined('MOODLE_INTERNAL') || die();

class address extends base_entity {
    public string $type = 'Address';
    public concept $countryCode;
    public note $fullAddress;

    public function __construct(string $id, concept $countryCode, string $fullAddress) {
        parent::__construct($id);
        $this->countryCode = $countryCode;
        $this->fullAddress = new note(
            new localized_string($fullAddress),
        );
    }

    public function getId(): string {
        return 'urn:epass:address:' . $this->id;
    }

    public function toArray(): array {
        $data = [
            'id' => $this->getId(),
            'type' => $this->type,
            'countryCode' => $this->countryCode->toArray(),
            'fullAddress' => $this->fullAddress->toArray(),
        ];

        return $data;
    }
}
