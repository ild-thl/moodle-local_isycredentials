<?php

namespace local_isycredentials\credential;

class individual_display extends base_entity {
    public string $type = 'IndividualDisplay';
    public concept $language;
    public array $displayDetail;

    public function __construct(concept $language, object $badge) {
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
            'language' => $this->language->toArray(),
            'displayDetail' => array_map(function (display_detail $displayDetail) {
                return $displayDetail->toArray();
            }, $this->displayDetail),
        ];

        return $data;
    }
}
