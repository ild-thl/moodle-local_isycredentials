<?php

namespace local_isycredentials\credential;

class display_detail extends base_entity {
    public string $type = 'DisplayDetail';
    public media_object $image;
    public int $page;

    public static function from(int $page, media_object $image): self {
        $displayDetail = new display_detail();
        $displayDetail->page = $page;
        $displayDetail->image = $image;
        return $displayDetail;
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
