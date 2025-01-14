<?php

namespace local_isycredentials\credential;

defined('MOODLE_INTERNAL') || die();

use local_isycredentials\credential\concept\education_credit_concept;

/**
 * Class credit_point
 * 
 * A measure demonstrating the estimated workload an individual is typically required to undertake to achieve a set of learning outcomes., The credit points.
 * 
 * @see https://europa.eu/europass/elm-browser/documentation/rdf/ap/edc/documentation/edc-generic-no-cv_en.html#credit-point
 */
class credit_point extends base_entity {
    public string $type = 'CreditPoint';

    /**
     * The framework used to assign the credit points to the learning specification. It should be provided using the European Standard List of Educational Credit Systems., The framework used to assign the credit points to the learning specification. The name of the used credit system should come from a controlled vocabulary (e.g. http://publications.europa.eu/resource/dataset/education-credit). Data providers can use their own cotrolled list(s).
     */
    public education_credit_concept $framework;

    /**
     * The measure demonstrating the estimated workload an individual is typically required to undertake to achieve a set of learning outcomes assigned to the learning specification., The credit points assigned to the learning specification.
     */
    public string $point;

    public function __construct(string $id, string $point) {
        parent::__construct($id);
        $this->point = $point;
        $this->framework = education_credit_concept::ECTS();
    }

    public function getId(): string {
        return 'urn:epass:creditPoint:' . $this->id;
    }

    public function toArray(): array {
        $data = [
            'id' => $this->getId(),
            'type' => $this->type,
            'framework' => $this->framework,
            'point' => $this->point,
        ];
        return $data;
    }
}
