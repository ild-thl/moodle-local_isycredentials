<?php

namespace local_isycredentials\credential;

defined('MOODLE_INTERNAL') || die();

class display_parameter extends base_entity {
    public string $type = 'DisplayParameter';
    public concept $language;
    public array $individualDisplay;
    public concept $primaryLanguage;
    public localized_string $title;

    public function __construct(string $id, concept $language, array $individualDisplay, concept $primaryLanguage, localized_string $title) {
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
        // If there is only one individual display, do not wrap it in an array
        if (count($this->individualDisplay) === 1) {
            $individualDisplay = $this->individualDisplay[0]->toArray();
        } else {
            $individualDisplay = array_map(function (individual_display $individualDisplay) {
                return $individualDisplay->toArray();
            }, $this->individualDisplay);
        }

        $data = [
            'id' => $this->getId(),
            'type' => $this->type,
            'individualDisplay' => $individualDisplay,
            'primaryLanguage' => $this->primaryLanguage->toArray(),
            'language' => $this->language->toArray(),
            'title' => $this->title->toArray(),
        ];

        return $data;
    }
}
