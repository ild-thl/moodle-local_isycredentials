<?php

namespace local_isycredentials\credential;

class address extends base_entity {
    public string $type = 'Address';
    public concept $countryCode;
    public note $fullAddress;

    public static function from(string $id, concept $countryCode, array $fullAddress): self {
        $address = new address($id);
        $address->countryCode = $countryCode;
        $address->fullAddress = note::from(
            $fullAddress,
        );
        return $address;
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
