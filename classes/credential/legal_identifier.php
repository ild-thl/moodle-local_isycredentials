<?php

namespace local_isycredentials\credential;

defined('MOODLE_INTERNAL') || die();

use local_isycredentials\credential\concept\country_concept;

/**
 * Class legal_identifier
 * 
 * A Legal Identifier. A legal identifier is a formally issued identifier by a given authority within a given jurisdiction. The identifier has a spatial context., A formally issued identifier by a given public authority, that has a spatial context.
 * 
 * @see https://europa.eu/europass/elm-browser/documentation/rdf/ap/edc/documentation/edc-generic-no-cv_en.html#legal-identifier
 */
class legal_identifier extends base_entity {
    public string $type = 'LegalIdentifier';

    /**
     * The content string which is the identifier. This property is used to assign a notation as a typed literal
     */
    public string $notation;

    /**
     * Spatial characteristics of the resource.
     */
    public country_concept $spatial;

    public function __construct(string $id, string $notation, country_concept $spatial) {
        parent::__construct($id);
        $this->notation = $notation;
        $this->spatial = $spatial;
    }

    public function getId(): string {
        return 'urn:epass:legalIdentifier:' . $this->id;
    }

    public function toArray(): array {
        $data =  [
            'id' => $this->getId(),
            'type' => $this->type,
        ];

        $data['spatial'] = $this->spatial->toArray();

        $data['notation'] = $this->notation;

        return $data;
    }
}
