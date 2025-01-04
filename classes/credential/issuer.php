<?php

namespace local_isycredentials\credential;

class issuer extends base_entity {
    public string $type = 'Organisation';
    public array $location;
    public array $legalName;

    public static function from(string $id, array $location, array $legalName): self {
        $issuer = new issuer($id);
        $issuer->location = $location;
        $issuer->legalName = $legalName;
        return $issuer;
    }

    public function getId(): string {
        return $this->id;
    }

    public function toArray(): array {
        return [
            'id' => $this->getId(),
            'type' => $this->type,
            'location' => array_map(function (Location $location) {
                return $location->toArray();
            }, $this->location),
            'legalName' => $this->legalName,
        ];
    }
}
