<?php

namespace local_isycredentials\credential;

defined('MOODLE_INTERNAL') || die();

class web_resource extends base_entity {
    public string $type = 'WebResource';
    public string $contentURL;
    public ?localized_string $title = null;

    public function __construct(string $contentURL, ?localized_string $title = null) {
        parent::__construct();
        $this->contentURL = $contentURL;
        $this->title = $title;
    }

    public function getId(): string {
        return 'urn:epass:webResource:' . $this->id;
    }

    public function toArray(): array {
        $data = [
            'id' => $this->getId(),
            'type' => $this->type,
        ];

        $data['contentURL'] = $this->contentURL;

        if (!empty($this->title)) {
            $data['title'] = $this->title->toArray();
        }

        return $data;
    }
}
