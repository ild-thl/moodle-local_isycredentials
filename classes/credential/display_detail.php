<?php

namespace local_isycredentials\credential;

defined('MOODLE_INTERNAL') || die();

class display_detail extends base_entity {
    public string $type = 'DisplayDetail';
    public media_object $image;
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
