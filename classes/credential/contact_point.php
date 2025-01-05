<?php

namespace local_isycredentials\credential;

defined('MOODLE_INTERNAL') || die();

class contact_point extends base_entity {
    public string $type = 'ContactPoint';
    public ?address $address = null;
    public ?email_address $emailAddress = null;

    public function __construct(string $id, ?address $address = null, ?string $emailAddress = null) {
        parent::__construct($id);
        $this->address = $address;
        $this->emailAddress = new email_address($emailAddress);
    }

    public function getId(): string {
        return 'urn:epass:contactPoint:' . $this->id;
    }

    public function toArray(): array {
        $data = [
            'id' => $this->getId(),
            'type' => $this->type,
        ];

        if ($this->address) {
            $data['address'] = $this->address->toArray();
        }

        if ($this->emailAddress) {
            $data['emailAddress'] = $this->emailAddress->toArray();
        }

        return $data;
    }
}
