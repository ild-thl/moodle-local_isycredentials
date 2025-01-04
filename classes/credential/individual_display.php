<?php

namespace local_isycredentials\credential;

class individual_display extends base_entity {
    public string $type = 'IndividualDisplay';
    public concept $language;
    public array $displayDetail;

    public static function from(concept $language, object $badge): self {
        $individualDisplay = new individual_display();
        $individualDisplay->language = $language;
        //Create Display Details
        $individualDisplay->displayDetail = [
            display_detail::from(
                1,
                media_object::fromBadgeImage($badge),
            )
        ];
        return $individualDisplay;
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
