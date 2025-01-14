<?php

namespace local_isycredentials\credential;

defined('MOODLE_INTERNAL') || die();

/**
 * Class issuer
 * 
 * This shape adds an extra constraint on the issuer of the European Digital Credential.
 * 
 * @see https://europa.eu/europass/elm-browser/documentation/rdf/ap/edc/documentation/edc-generic-no-cv_en.html#issuer-node
 */
class issuer extends organisation {
    /**
     * The official identification number of the organisation, as awarded by the relevant national authority.authority., An issuer must have one and only one formally issued identifier by a given public authority, that has a spatial context.
     */
    public legal_identifier $registration;

    public function __construct(string $id, address $address, string $legalName, legal_identifier $registration) {
        parent::__construct($id, $address, $legalName, $registration);
    }

    /**
     * Overrides the parent method to only return the original set id as is.
     * 
     * @override
     * @return string
     */
    public function getId(): string {
        return $this->id;
    }
}
