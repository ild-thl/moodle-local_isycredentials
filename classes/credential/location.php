<?php

namespace local_isycredentials\credential;

class location extends base_entity {
    public string $type = 'Location';
    public array $address;

    public static function from(string $id, array $address): self {
        $location = new location($id);
        $location->address = $address;
        return $location;
    }

    public function getId(): string {
        return 'urn:epass:location:' . $this->id;
    }

    public function toArray(): array {
        return [
            'id' => $this->getId(),
            'type' => $this->type,
            'address' => array_map(function (address $address) {
                return $address->toArray();
            }, $this->address)
        ];
    }
}
