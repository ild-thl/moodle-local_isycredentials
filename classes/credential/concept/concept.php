<?php

namespace local_isycredentials\credential\concept;

defined('MOODLE_INTERNAL') || die();

use local_isycredentials\credential\base_entity;
use local_isycredentials\credential\localized_string;

/**
 * Class concept
 * 
 * @see https://www.w3.org/TR/skos-reference/#concepts
 */
class concept extends base_entity {
    public string $type = 'Concept';

    /**
     * The concept scheme to which the concept belongs.
     */
    public concept_scheme $inScheme;

    /**
     * The identifier for the concept in the concept scheme.
     */
    public string $id;

    /**
     * The lexical label for the concept.
     */
    public localized_string $prefLabel;

    /**
     * A label describing the purpose of the concept.
     */
    public ?string $notation = null;

    public function __construct(string $id, localized_string $prefLabel, concept_scheme $inScheme, ?string $notation = null) {
        parent::__construct($id);
        $this->prefLabel = $prefLabel;
        $this->notation = $notation;
        $this->inScheme = $inScheme;
    }

    public function withNotation(string $notation): self {
        $this->notation = $notation;
        return $this;
    }

    public function toArray(): array {
        $data = [
            'id' => $this->getId(),
            'type' => $this->type,
        ];

        $data['inScheme'] = $this->inScheme->toArray();

        if (!empty($this->notation)) {
            $data['notation'] = $this->notation;
        }

        $data['prefLabel'] = $this->prefLabel->toArray();

        return $data;
    }

    public static function fromArray(array $data): self {
        $inScheme = concept_scheme::fromArray($data['inScheme']);
        $prefLabel = localized_string::fromArray($data['prefLabel']);
        $notation = $data['notation'] ?? null;

        return new self($data['id'], $prefLabel, $inScheme, $notation);
    }
}
