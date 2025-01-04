<?php

namespace local_isycredentials\credential;

defined('MOODLE_INTERNAL') || die();

class concept extends base_entity {
    public string $type = 'Concept';
    public concept_scheme $inScheme;
    public array $prefLabel;
    public string $notation;


    public static function from(string $id, array $prefLabel, string $notation, concept_scheme $inScheme): self {
        $concept = new concept($id);
        $concept->prefLabel = $prefLabel;
        $concept->notation = $notation;
        $concept->inScheme = $inScheme;
        return $concept;
    }

    public function getId(): string {
        return $this->id;
    }

    public function toArray(): array {
        $data = [
            'id' => $this->getId(),
            'type' => $this->type,
            'inScheme' => $this->inScheme->toArray(),
            'notation' => $this->notation,
            'prefLabel' => $this->prefLabel,
        ];
        return $data;
    }
}
