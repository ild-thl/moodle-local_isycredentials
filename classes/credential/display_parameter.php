<?php

namespace local_isycredentials\credential;

defined('MOODLE_INTERNAL') || die();

use local_isycredentials\credential\concept\language_concept;

/**
 * Class display_parameter
 * 
 * The display parameters., Customisable aspects of a credential's visual representation.
 * 
 * @see https://europa.eu/europass/elm-browser/documentation/rdf/ap/edc/documentation/edc-generic-no-cv_en.html#display-parameter
 */
class display_parameter extends base_entity {
    public string $type = 'DisplayParameter';

    /**
     * A language of the resource.
     */
    public language_concept $language;

    /**
     * The background image of the credential., The individual display of the display parameter.
     * 
     * @var individual_display[]
     */
    public array $individualDisplay;

    /**
     * The primary language of the credential (only one language can be applied)., The primary language of the credential (only one language can be applied). The provided value must come from the Language Named Authority List (http://publications.europa.eu/resource/authority/language).
     */
    public language_concept $primaryLanguage;

    /**
     * The title of the individual display.
     */
    public localized_string $title;

    /**
     * A description of the individual display.
     */
    public ?localized_string $description = null;

    public function __construct(string $id, language_concept $language, array $individualDisplay, language_concept $primaryLanguage, localized_string $title, ?localized_string $description = null) {
        parent::__construct($id);
        $this->language = $language;
        // Check if individualDisplay is of type individual_display
        foreach ($individualDisplay as $individualDisplayItem) {
            if (!($individualDisplayItem instanceof individual_display)) {
                throw new \InvalidArgumentException('individualDisplay must be an array of individual_display');
            }
        }
        $this->individualDisplay = $individualDisplay;
        $this->title = $title;
        $this->primaryLanguage = $primaryLanguage;
        $this->description = $description;
    }

    public function withDescription(localized_string $description): self {
        $this->description = $description;
        return $this;
    }

    public function getId(): string {
        return 'urn:epass:displayParameter:' . $this->id;
    }

    public function toArray(): array {
        $data = [
            'id' => $this->getId(),
            'type' => $this->type,
        ];

        if (!empty($this->individualDisplay)) {
            $data['individualDisplay'] = array_map(function (individual_display $individualDisplay) {
                return $individualDisplay->toArray();
            }, $this->individualDisplay);
        }

        $data['primaryLanguage'] = $this->primaryLanguage->toArray();

        $data['language'] = $this->language->toArray();

        $data['title'] = $this->title->toArray();

        if (!empty($this->description)) {
            $data['description'] = $this->description->toArray();
        }

        return $data;
    }
}
