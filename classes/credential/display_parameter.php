<?php

namespace local_isycredentials\credential;

class display_parameter extends base_entity {
    public string $type = 'DisplayParameter';
    public array $language;
    public array $individualDisplay;
    public concept $primaryLanguage;
    public localized_string $title;

    public function __construct(string $id, array $language, array $individualDisplay, concept $primaryLanguage, localized_string $title) {
        parent::__construct($id);
        $this->language = $language;
        $this->individualDisplay = $individualDisplay;
        $this->title = $title;
        $this->primaryLanguage = $primaryLanguage;
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
            'title' => $this->title->toArray(),
        ];

        return $data;
    }
}
