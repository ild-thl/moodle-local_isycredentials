<?php

namespace local_isycredentials\credential;

defined('MOODLE_INTERNAL') || die();

/**
 * Class email_address
 * 
 * An e-mail used for contacting the agent. This property associates the Contact Information class with the Email Address class.
 * 
 * @see http://data.europa.eu/snb/model/elm/emailAddress
 */
class email_address extends base_entity {
    public string $type = 'Mailbox';

    public function __construct(string $email) {
        // Check if the email is valid
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \Exception('Invalid email address');
        }
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
