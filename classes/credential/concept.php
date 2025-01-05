<?php

namespace local_isycredentials\credential;

defined('MOODLE_INTERNAL') || die();

class concept extends base_entity {
    public string $type = 'Concept';
    public concept_scheme $inScheme;
    public localized_string $prefLabel;
    public string $notation;

    public function __construct(string $id, localized_string $prefLabel, string $notation, concept_scheme $inScheme) {
        parent::__construct($id);
        $this->prefLabel = $prefLabel;
        $this->notation = $notation;
        $this->inScheme = $inScheme;
    }

    public function toArray(): array {
        $data = [
            'id' => $this->getId(),
            'type' => $this->type,
            'inScheme' => $this->inScheme->toArray(),
            'notation' => $this->notation,
            'prefLabel' => $this->prefLabel->toArray(),
        ];
        return $data;
    }
}
