<?php

namespace local_isycredentials\credential;

defined('MOODLE_INTERNAL') || die();

/**
 * Class location
 * 
 * A spatial region or named place., dcterms:Location class fully represents the ISA Programme Location Core Vocabulary class of Location.
 * 
 * @see https://europa.eu/europass/elm-browser/documentation/rdf/ap/edc/documentation/edc-generic-no-cv_en.html#location
 */
class location extends base_entity {
    public string $type = 'Location';

    /**
     * An address associated with the location., Particulars describing the location of the place of the means of communicating with an Agent.
     */
    public address $address;

    public function __construct(string $id, address $address) {
        parent::__construct($id);
        $this->address = $address;
    }

    public function getId(): string {
        return 'urn:epass:location:' . $this->id;
    }

    public function toArray(): array {
        $data = [
            'id' => $this->getId(),
            'type' => $this->type,
        ];

        $data['address'] = $this->address->toArray();

        return $data;
    }
}
