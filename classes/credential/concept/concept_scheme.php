<?php

namespace local_isycredentials\credential\concept;

defined('MOODLE_INTERNAL') || die();

use local_isycredentials\credential\base_entity;

/**
 * Class concept_scheme
 * 
 * @see https://www.w3.org/TR/skos-reference/#schemes
 */
class concept_scheme extends base_entity {
    public string $type;

    /**
     * Constructor.
     *
     * @param string|null $id
     */
    public function __construct(string $id, ?string $type = 'ConceptScheme') {
        parent::__construct($id);
        $this->type = $type;
    }

    /**
     * An identifier that uniquely identifies the concept scheme.
     */
    public string $id;

    public function toArray(): array {
        return [
            'id' => $this->getId(),
            'type' => $this->type,
        ];
    }

    public static function fromArray(array $data): self {
        return new self(
            $data['id'],
            $data['type'],
        );
    }
}
