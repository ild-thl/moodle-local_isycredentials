<?php

namespace local_isycredentials\credential;

defined('MOODLE_INTERNAL') || die();

use local_isycredentials\credential\concept\language_concept;

/**
 * Class individual_display
 * 
 * A visual representation of a credential., An individual display.
 * 
 * @see https://europa.eu/europass/elm-browser/documentation/rdf/ap/edc/documentation/edc-generic-no-cv_en.html#individual-display-individual-display
 */
class individual_display extends base_entity {
    public string $type = 'IndividualDisplay';

    /**
     * The language of the individual displays content.
     */
    public language_concept $language;

    /**
     * The detail of the display, The detail(s) of the visual representation of a credential.
     * 
     * @var display_detail[]
     */
    public array $displayDetail;

    public function __construct(language_concept $language, \stdClass $badge) {
        parent::__construct();
        $this->language = $language;
        //Create Display Details
        $this->displayDetail = [
            new display_detail(
                1,
                media_object::fromBadgeImage($badge),
            )
        ];
    }

    public function getId(): string {
        return 'urn:epass:individualDisplay:' . $this->id;
    }

    public function toArray(): array {
        $data = [
            'id' => $this->getId(),
            'type' => $this->type,
        ];

        if (!empty($this->displayDetail)) {
            $data['displayDetail'] = array_map(function (display_detail $displayDetail) {
                return $displayDetail->toArray();
            }, $this->displayDetail);
        }

        $data['language'] = $this->language->toArray();

        return $data;
    }
}
