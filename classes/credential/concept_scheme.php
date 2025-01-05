<?php

namespace local_isycredentials\credential;

class concept_scheme extends base_entity {
    public string $type = 'ConceptScheme';
    public string $id;

    public function toArray(): array {
        return [
            'id' => $this->getId(),
            'type' => $this->type,
        ];
    }
}
