<?php

namespace local_isycredentials\credential;

defined('MOODLE_INTERNAL') || die();

class awarding_body extends organisation {
    public legal_identifier $registration;

    public function __construct(string $id, address $address, string $legalName, ?string $email = null, legal_identifier $registration) {
        parent::__construct($id, $address, $legalName, $email);
        $this->registration = $registration;
    }

    public function toArray(): array {
        $data = parent::toArray();
        $data['registration'] = $this->registration->toArray();
        return $data;
    }
}
