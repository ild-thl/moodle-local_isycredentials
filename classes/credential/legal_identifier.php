<?php

namespace local_isycredentials\credential;

class legal_identifier extends base_entity {
    public string $type = 'LegalIdentifier';
    public string $notation;
    public concept $spatial;

    public static function from(string $id, string $notation, concept $spatial): self {
        $legalIdentifier = new legal_identifier($id);
        $legalIdentifier->notation = $notation;
        $legalIdentifier->spatial = $spatial;
        return $legalIdentifier;
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
