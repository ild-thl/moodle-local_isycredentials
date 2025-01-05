<?php

namespace local_isycredentials\credential;

defined('MOODLE_INTERNAL') || die();

class email_address extends base_entity {
    public string $type = 'Mailbox';

    public function __construct(string $email) {
        parent::__construct($email);
    }

    public function getId(): string {
        return 'mailto:' . $this->id;
    }

    public function toArray(): array {
        return [
            'id' => $this->getId(),
            'type' => $this->type,
        ];
    }
}
