<?php

namespace local_isycredentials\credential;

defined('MOODLE_INTERNAL') || die();

use local_isycredentials\credential\concept\concept;
use local_isycredentials\credential\concept\concept_scheme;

class note extends base_entity {
    public string $type = 'Note';
    public localized_string $noteLiteral;
    public ?localized_string $subject = null;

    public function __construct(localized_string $noteLiteral, ?localized_string $subject = null) {
        parent::__construct();
        $this->noteLiteral = $noteLiteral;
        $this->subject = $subject;
    }

    public function getId(): string {
        return 'urn:epass:note:' . $this->id;
    }

    public function toArray(): array {
        $data = [
            'id' => $this->getId(),
            'type' => $this->type,
        ];

        $data['noteLiteral'] = $this->noteLiteral->toArray();

        if ($this->subject) {
            $data['subject'] = (new concept(
                \core\uuid::generate(),
                $this->subject,
                new concept_scheme(\core\uuid::generate()),
            ))->toArray();
        }
        return $data;
    }
}
