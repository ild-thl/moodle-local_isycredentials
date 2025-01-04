<?php

namespace local_isycredentials\credential;

class concept_scheme extends base_entity {
    public string $type = 'ConceptScheme';
    public string $id;

    public static function from(string $id): self {
        $conceptScheme = new concept_scheme($id);
        return $conceptScheme;
    }

    public function getId(): string {
        return $this->id;
    }

    public function toArray(): array {
        return [
            'id' => $this->getId(),
            'type' => $this->type,
        ];
    }
}
