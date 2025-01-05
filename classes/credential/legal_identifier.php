<?php

namespace local_isycredentials\credential;

class legal_identifier extends base_entity {
    public string $type = 'LegalIdentifier';
    public string $notation;
    public concept $spatial;

    public function __construct(string $id, string $notation, concept $spatial) {
        parent::__construct($id);
        $this->notation = $notation;
        $this->spatial = $spatial;
    }

    public function getId(): string {
        return 'urn:epass:legalIdentifier:' . $this->id;
    }

    public function toArray(): array {
        $data =  [
            'id' => $this->getId(),
            'type' => $this->type,
            'notation' => $this->notation,
            'spatial' => $this->spatial->toArray(),
        ];
        return $data;
    }
}
