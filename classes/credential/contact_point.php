<?php

namespace local_isycredentials\credential;

defined('MOODLE_INTERNAL') || die();

/**
 * Class contact_point
 * 
 * Means of communicating with an Agent., Details to Contact an Agent. A contact point for an agent.
 * 
 * @see https://europa.eu/europass/elm-browser/documentation/rdf/ap/edc/documentation/edc-generic-no-cv_en.html#contact-point
 */
class contact_point extends base_entity {
    public string $type = 'ContactPoint';

    /**
     * An address associated with the location., Particulars describing the location of the place of the means of communicating with an Agent.
     */
    public ?address $address = null;

    /**
     * An e-mail used for contacting the agent. This property associates the Contact Information class with the Email Address class., An e-mail address used for contacting the agent.
     */
    public ?email_address $emailAddress = null;

    public function __construct(string $id, ?address $address = null, ?email_address $emailAddress = null) {
        parent::__construct($id);
        $this->address = $address;
        $this->emailAddress = $emailAddress;
    }

    public function withAddress(address $address): self {
        $this->address = $address;
        return $this;
    }

    public function withEmail(email_address $emailAddress): self {
        $this->emailAddress = $emailAddress;
        return $this;
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
