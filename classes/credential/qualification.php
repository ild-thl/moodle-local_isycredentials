<?php

namespace local_isycredentials\credential;

defined('MOODLE_INTERNAL') || die();

use local_isycredentials\credential\accreditation;
use local_isycredentials\credential\concept\concept;
use local_isycredentials\credential\concept\eqf_concept;
use local_isycredentials\credential\concept\nqf_concept;

/**
 * A specification of an assessment and validation process which is obtained when a competent authority determines that an individual has achieved learning outcomes to given standards.
 * 
 * @see https://europa.eu/europass/elm-browser/documentation/rdf/ap/edc/documentation/edc-generic-no-cv_en.html#qualification
 */
class qualification extends learning_achievement_specification {
    public string $type = 'Qualification';

    /**
     * The qualification level as specified by the European Qualification Framework., The qualification level as specified by the European Qualifications Framework. If provided, the value must come from the European Qualifications Framework list (https://op.europa.eu/en/web/eu-vocabularies/dataset/-/resource?uri=http://publications.europa.eu/resource/dataset/european-qualification-framework).
     */
    public ?eqf_concept $eqfLevel = null;

    /**
     * The qualification level as specified by a Qualifications Framework. If provided, the value must come from the Qualifications Framework list (https://op.europa.eu/en/web/eu-vocabularies/dataset/-/resource?uri=http://publications.europa.eu/resource/dataset/national-qualification-framework)., The qualification level as specified by a Qualification Framework.
     * 
     * @var nqf_concept[]|null
     */
    public ?array $nqfLevels = null;

    /**
     * The quality assurance or licensing of an organisation or a qualification of the process which resulted in the issuance of the verifiable credential., The associated accreditation.
     * 
     * @var accreditation[]|null
     */
    public ?array $accreditaions = null;

    /**
     * Indicates whether a qualification is a full qualification or part of another qualification.
     */
    public ?bool $isPartialQualification = null;

    /**
     * 	An identifying code from a qualification based reference semantic asset. This property is used to classify the qualification information with a qualification from a known qualification framework. (e.g., the link to the accredited QF qualification)., An identifying code from a qualification-based reference semantic asset. This property is used to classify the qualification information with a qualification from a known qualification framework. (e.g., the link to the accredited NQF qualification). If provided, the value should come from a controlled vocabulary.
     * 
     * @var concept[]|null
     */
    public ?array $qualificationCodes = null;

    public function withEqfLevel(eqf_concept $eqfLevel): self {
        $this->eqfLevel = $eqfLevel;
        return $this;
    }

    public function withNqfLevels(array $nqfLevels): self {
        // Check if the array contains only nqf_concept objects
        foreach ($nqfLevels as $nqfLevel) {
            if (!($nqfLevel instanceof nqf_concept)) {
                throw new \InvalidArgumentException('The nqfLevels array must contain only nqf_concept objects');
            }
        }
        $this->nqfLevels = $nqfLevels;
        return $this;
    }

    public function withAccreditations(array $accreditaions): self {
        // Check if the array contains only accreditation objects
        foreach ($accreditaions as $accreditaion) {
            if (!($accreditaion instanceof accreditation)) {
                throw new \InvalidArgumentException('The accreditaions array must contain only accreditation objects');
            }
        }
        $this->accreditaions = $accreditaions;
        return $this;
    }

    public function withIsPartialQualification(bool $isPartialQualification): self {
        $this->isPartialQualification = $isPartialQualification;
        return $this;
    }

    public function withQualificationCodes(array $qualificationCodes): self {
        // Check if the array contains only concept objects
        foreach ($qualificationCodes as $qualificationCode) {
            if (!($qualificationCode instanceof concept)) {
                throw new \InvalidArgumentException('The qualificationCodes array must contain only concept objects');
            }
        }
        $this->qualificationCodes = $qualificationCodes;
        return $this;
    }

    public function getId(): string {
        return 'urn:epass:qualification:' . $this->id;
    }

    public function toArray(): array {
        $data = parent::toArray();

        if (!empty($this->nqfLevels)) {
            $data['nqfLevel'] = array_map(function ($nqfLevel) {
                return $nqfLevel->toArray();
            }, $this->nqfLevels);
        }

        if ($this->eqfLevel !== null) {
            $data['eqfLevel'] = $this->eqfLevel->toArray();
        }

        if (!empty($this->accreditaions)) {
            $data['accreditaions'] = array_map(function ($accreditaion) {
                return $accreditaion->toArray();
            }, $this->accreditaions);
        }

        if ($this->isPartialQualification !== null) {
            $data['isPartialQualification'] = $this->isPartialQualification;
        }

        if (!empty($this->qualificationCodes)) {
            $data['qualificationCodes'] = array_map(function ($qualificationCode) {
                return $qualificationCode->toArray();
            }, $this->qualificationCodes);
        }


        return $data;
    }
}
