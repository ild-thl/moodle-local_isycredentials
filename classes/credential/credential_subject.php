<?php

namespace local_isycredentials\credential;

class credential_subject extends base_entity {
    public string $type = 'Person';
    public array $givenName;
    public array $familyName;
    public array $fullName;
    public array $hasClaim;

    public static function from(string $id, array $givenName, array $familyName, array $hasClaim): self {
        $person = new credential_subject($id);
        $person->givenName = $givenName;
        $person->familyName = $familyName;
        $person->fullName = ['de' => [$person->givenName[array_key_first($person->givenName)][0] . ' ' . $person->familyName[array_key_first($person->familyName)][0]]];
        $person->hasClaim = $hasClaim;
        return $person;
    }

    public function getId(): string {
        return 'urn:epass:person:' . $this->id;
    }

    public function toArray(): array {
        return [
            'id' => $this->getId(),
            'type' => $this->type,
            'givenName' => $this->givenName,
            'familyName' => $this->familyName,
            'fullName' => $this->fullName,
            'hasClaim' => array_map(function (base_entity $claim) {
                return $claim->toArray();
            }, $this->hasClaim),
        ];
    }
}
