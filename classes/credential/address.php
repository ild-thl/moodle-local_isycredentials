<?php

namespace local_isycredentials\credential;

defined('MOODLE_INTERNAL') || die();

use local_isycredentials\credential\concept\country_concept;

/**
 * Class address
 * 
 * Particulars describing the location of the place., An Address.
 * 
 * @see https://europa.eu/europass/elm-browser/documentation/rdf/ap/edc/documentation/edc-generic-no-cv_en.html#address
 */
class address extends base_entity {
    public string $type = 'Address';
    /**
     * The address country code., The address’ country code. The provided value must come from the Country Named Authority List (http://publications.europa.eu/resource/authority/country).
     */
    public country_concept $countryCode;

    /**
     * The complete address with or without formatting.
     */
    public note $fullAddress;

    public function __construct(string $id, country_concept $countryCode, string $fullAddress) {
        parent::__construct($id);
        $this->countryCode = $countryCode;
        $this->fullAddress = new note(
            new localized_string($fullAddress),
        );
    }

    public function getId(): string {
        return 'urn:epass:address:' . $this->id;
    }

    public function toArray(): array {
        $data = [
            'id' => $this->getId(),
            'type' => $this->type,
            'countryCode' => $this->countryCode->toArray(),
            'fullAddress' => $this->fullAddress->toArray(),
        ];

        return $data;
    }
}
