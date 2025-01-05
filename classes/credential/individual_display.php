<?php

namespace local_isycredentials\credential;

defined('MOODLE_INTERNAL') || die();

class individual_display extends base_entity {
    public string $type = 'IndividualDisplay';
    public concept $language;
    public array $displayDetail;

    public function __construct(concept $language, \stdClass $badge) {
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
            'displayDetail' => array_map(function (display_detail $displayDetail) {
                return $displayDetail->toArray();
            }, $this->displayDetail),
            'language' => $this->language->toArray(),
        ];

        return $data;
    }
}
