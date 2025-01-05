<?php

namespace local_isycredentials\credential;

class credential_subject extends base_entity {
    public string $type = 'Person';
    public localized_string $givenName;
    public localized_string $familyName;
    public localized_string $fullName;
    public array $hasClaim;

    public function __construct(string $id, string $givenName, string $familyName, string $fullName, array $hasClaim) {
        parent::__construct($id);
        $this->givenName = new localized_string($givenName);
        $this->familyName = new localized_string($familyName);
        $this->fullName = new localized_string($fullName);
        $this->hasClaim = $hasClaim;
    }

    public function getId(): string {
        return 'urn:epass:person:' . $this->id;
    }

    public function toArray(): array {
        return [
            'id' => $this->getId(),
            'type' => $this->type,
            'givenName' => $this->givenName->toArray(),
            'familyName' => $this->familyName->toArray(),
            'fullName' => $this->fullName->toArray(),
            'hasClaim' => array_map(function (base_entity $claim) {
                return $claim->toArray();
            }, $this->hasClaim),
        ];
    }
}
