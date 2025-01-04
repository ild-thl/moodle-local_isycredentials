<?php

namespace local_isycredentials\credential;

class display_parameter extends base_entity {
    public string $type = 'DisplayParameter';
    public array $language;
    public array $individualDisplay;
    public concept $primaryLanguage;
    public array $title;

    public static function from(string $id, array $language, array $individualDisplay, concept $primaryLanguage, array $title): self {
        $displayParameter = new display_parameter($id);
        $displayParameter->language = $language;
        $displayParameter->individualDisplay = $individualDisplay;
        $displayParameter->title = $title;
        $displayParameter->primaryLanguage = $primaryLanguage;
        return $displayParameter;
    }

    public function getId(): string {
        return 'urn:epass:displayParameter:' . $this->id;
    }

    public function toArray(): array {
        $data = [
            'id' => $this->getId(),
            'type' => $this->type,
            'language' => array_map(function (concept $concept) {
                return $concept->toArray();
            }, $this->language),
            'individualDisplay' => array_map(function (individual_display $individualDisplay) {
                return $individualDisplay->toArray();
            }, $this->individualDisplay),
            'primaryLanguage' => $this->primaryLanguage->toArray(),
            'title' => $this->title,
        ];

        return $data;
    }
}
