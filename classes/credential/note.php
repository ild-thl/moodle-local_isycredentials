<?php

namespace local_isycredentials\credential;

defined('MOODLE_INTERNAL') || die();

class note extends base_entity {
    public string $type = 'Note';
    public localized_string $noteLiteral;

    public function __construct(localized_string $noteLiteral) {
        parent::__construct();
        $this->noteLiteral = $noteLiteral;
    }

    public function getId(): string {
        return 'urn:epass:note:' . $this->id;
    }

    public function toArray(): array {
        return [
            'id' => $this->getId(),
            'type' => $this->type,
            'noteLiteral' => $this->noteLiteral->toArray(),
        ];
    }
}
