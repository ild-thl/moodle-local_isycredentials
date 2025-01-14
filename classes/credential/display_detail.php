<?php

namespace local_isycredentials\credential;

defined('MOODLE_INTERNAL') || die();

/**
 * Class display_detail
 * 
 * A single section within an individual display., The display detail.
 * 
 * @see https://europa.eu/europass/elm-browser/documentation/rdf/ap/edc/documentation/edc-generic-no-cv_en.html#display-detail
 */
class display_detail extends base_entity {
    public string $type = 'DisplayDetail';

    /**
     * The image of a single section within a visual representation of a credential., An image associated with the display.
     */
    public media_object $image;

    /**
     * The page of a single section within an individual display., The page number of the display.
     */
    public int $page;

    public function __construct(int $page, media_object $image) {
        parent::__construct();
        $this->page = $page;
        $this->image = $image;
    }

    public function getId(): string {
        return 'urn:epass:displayDetail:' . $this->id;
    }

    public function toArray(): array {
        $data = [
            'id' => $this->getId(),
            'type' => $this->type,
            'image' => $this->image->toArray(),
            'page' => $this->page
        ];

        return $data;
    }
}
