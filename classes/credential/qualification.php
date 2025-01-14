<?php

namespace local_isycredentials\credential;

defined('MOODLE_INTERNAL') || die();

use local_isycredentials\credential\concept\learning_opportunity_concept;
use local_isycredentials\credential\concept\eqf_concept;
use local_isycredentials\credential\concept\nqf_concept;
use local_isycredentials\credential\concept\learning_setting_concept;
use local_isycredentials\credential\concept\learning_assessment_concept;
use local_isycredentials\credential\concept\language_concept;
use local_isycredentials\credential\concept\target_group_concept;
use local_isycredentials\credential\concept\iscedf_concept;

/**
 * A specification of an assessment and validation process which is obtained when a competent authority determines that an individual has achieved learning outcomes to given standards.
 * 
 * @see https://europa.eu/europass/elm-browser/documentation/rdf/ap/edc/documentation/edc-generic-no-cv_en.html#qualification
 */
class qualification extends learning_achievement_specification {
    public string $type = 'Qualification';

    /**
     * the title of the Qualification.
     */
    public localized_string $title;

    /**
     * A description of the Qualification.
     */
    public ?localized_string $description = null;

    /**
     * The type of learning opportunity. If provided, the value must come from the list of Learning opportunity types (https://op.europa.eu/en/web/eu-vocabularies/concept/-/resource?uri=http://data.europa.eu/snb/learning-opportunity/25831c2)., 
     */
    public ?learning_opportunity_concept $dcType = null;

    /**
     * The credit points assigned to the qualification, following a credit system., A measure demonstrating the estimated workload an individual is typically required to undertake to achieve a set of learning outcomes assigned to the learning achievement specification, following an educational credit system.
     */
    public ?credit_point $creditPoint = null;

    /**
     * Specific entry requirement or prerequisite of individuals for which this learning achievement specification is designed to start this learning opportunity., Specific entry requirements or prerequisites of individuals for which this specification is designed to start this learning opportunity.
     */
    public ?note $entryRequirement = null;

    /**
     * The language of the Qualification.
     */
    public ?language_concept $language = null;

    /**
     * Individual (expected) learning outcomes of the qualification.
     */
    public ?array $learningOutcomes = null;

    /**
     * The full learning outcome summary of the description of a set of knowledge and/or skills used with responsibility and autonomy, which may be acquired., The full learning outcome summary of the qualification.
     */
    public ?note $learningOutcomeSummary = null;

    /**
     * The type of learning setting (formal, non-formal). If provided, the value must come from the list of Learning setting types (https://op.europa.eu/en/web/eu-vocabularies/dataset/-/resource?uri=http://publications.europa.eu/resource/dataset/learning-setting)., The type of learning setting (formal, non-formal).
     */
    public ?learning_setting_concept $learningSetting = null;

    /**
     * The mode of learning and or assessment., The mode of learning, and/or assessment. If provided, the value should come from a controlled vocabulary (e.g. https://op.europa.eu/en/web/eu-vocabularies/dataset/-/resource?uri=http://publications.europa.eu/resource/dataset/learning-assessment). Data providers can use their own cotrolled list(s).
     */
    public ?learning_assessment_concept $mode = null;

    /**
     * 	A specific target group or category for which this learning achievement specification is designed. If provided, the value should come from a controlled vocabulary (e.g. https://op.europa.eu/en/web/eu-vocabularies/dataset/-/resource?uri=http://publications.europa.eu/resource/dataset/target-group). Data providers can use their own cotrolled list(s)., A specific target group or category for which this specification is designed.
     * 
     * @var target_group_concept[]|null
     */
    public ?array $targetGroups = null;

    /**
     * Thematic Area according to the ISCED-F 2013 classification. If provided, the value must come from the ISCED-F controlled vocabulary (https://op.europa.eu/en/web/eu-vocabularies/dataset/-/resource?uri=http://publications.europa.eu/resource/dataset/international-education-classification)., Thematic Area according to the ISCED-F 2013 Classification .
     * 
     * @var iscedf_concept[]|null
     */
    public ?array $thematicAreas = null;

    /**
     * The estimated number of hours the learner is expected to spend engaged in learning to earn the award. This would include the notional number of hours in, in group work, in practicals, as well as hours engaged in self-motivated study., The estimated number of hours the learner is expected to spend engaged in learning to earn the award. This would include the notional number of hours in class, in group work, in practicals, as well as hours engaged in self-motivated study.
     */
    public ?string $volumeOfLearning = null;

    /**
     * The qualification level as specified by a Qualifications Framework. If provided, the value must come from the Qualifications Framework list (https://op.europa.eu/en/web/eu-vocabularies/dataset/-/resource?uri=http://publications.europa.eu/resource/dataset/national-qualification-framework)., The qualification level as specified by a Qualification Framework.
     */
    public ?nqf_concept $nqfLevel = null;

    /**
     * The qualification level as specified by the European Qualification Framework., The qualification level as specified by the European Qualifications Framework. If provided, the value must come from the European Qualifications Framework list (https://op.europa.eu/en/web/eu-vocabularies/dataset/-/resource?uri=http://publications.europa.eu/resource/dataset/european-qualification-framework).
     */
    public ?eqf_concept $eqfLevel = null;

    public function __construct(string $id, localized_string $title) {
        parent::__construct($id);
        $this->title = $title;
    }

    public function description(localized_string $description): self {
        $this->description = $description;
        return $this;
    }

    public function dcType(learning_opportunity_concept $dcType): self {
        $this->dcType = $dcType;
        return $this;
    }

    public function creditPoint(credit_point $creditPoint): self {
        $this->creditPoint = $creditPoint;
        return $this;
    }

    public function entryRequirement(note $entryRequirement): self {
        $this->entryRequirement = $entryRequirement;
        return $this;
    }

    public function language(language_concept $language): self {
        $this->language = $language;
        return $this;
    }

    public function learningOutcomes(array $learningOutcomes): self {
        // Check if the array contains only learning_outcome objects
        foreach ($learningOutcomes as $learningOutcome) {
            if (!($learningOutcome instanceof learning_outcome)) {
                throw new \InvalidArgumentException('The learningOutcomes array must contain only learning_outcome objects');
            }
        }
        $this->learningOutcomes = $learningOutcomes;
        return $this;
    }

    public function learningSetting(learning_setting_concept $learningSetting): self {
        $this->learningSetting = $learningSetting;
        return $this;
    }

    public function mode(learning_assessment_concept $mode): self {
        $this->mode = $mode;
        return $this;
    }

    public function targetGroups(array $targetGroups): self {
        // Check if the array contains only concept objects
        foreach ($targetGroups as $targetGroup) {
            if (!($targetGroup instanceof target_group_concept)) {
                throw new \InvalidArgumentException('The targetGroups array must contain only target_group_concept objects');
            }
        }
        $this->targetGroups = $targetGroups;
        return $this;
    }

    public function thematicAreas(array $thematicAreas): self {
        // Check if the array contains only concept objects
        foreach ($thematicAreas as $thematicArea) {
            if (!($thematicArea instanceof iscedf_concept)) {
                throw new \InvalidArgumentException('The thematicAreas array must contain only iscedf_concept objects');
            }
        }
        $this->thematicAreas = $thematicAreas;
        return $this;
    }

    public function volumeOfLearning(string $volumeOfLearning): self {
        $this->volumeOfLearning = $volumeOfLearning;
        return $this;
    }

    public function nqfLevel(nqf_concept $nqfLevel): self {
        $this->nqfLevel = $nqfLevel;
        return $this;
    }

    public function eqfLevel(eqf_concept $eqfLevel): self {
        $this->eqfLevel = $eqfLevel;
        return $this;
    }

    public function getId(): string {
        return 'urn:epass:qualification:' . $this->id;
    }

    public function toArray(): array {
        $data = [
            'id' => $this->getId(),
            'type' => $this->type,
        ];

        $data['title'] = $this->title->toArray();

        if ($this->description !== null) {
            $data['description'] = $this->description->toArray();
        }

        if ($this->dcType !== null) {
            $data['dcType'] = $this->dcType->toArray();
        }

        if ($this->creditPoint !== null) {
            $data['creditPoint'] = $this->creditPoint->toArray();
        }

        if ($this->entryRequirement !== null) {
            $data['entryRequirement'] = $this->entryRequirement->toArray();
        }

        if ($this->language !== null) {
            $data['language'] = $this->language->toArray();
        }

        if ($this->learningOutcomes !== null) {
            $data['learningOutcomes'] = array_map(function ($learningOutcome) {
                return $learningOutcome->toArray();
            }, $this->learningOutcomes);
        }

        if ($this->learningSetting !== null) {
            $data['learningSetting'] = $this->learningSetting->toArray();
        }

        if ($this->mode !== null) {
            $data['mode'] = $this->mode->toArray();
        }

        if ($this->targetGroups !== null) {
            $data['targetGroups'] = array_map(function ($targetGroup) {
                return $targetGroup->toArray();
            }, $this->targetGroups);
        }

        if ($this->thematicAreas !== null) {
            $data['thematicAreas'] = array_map(function ($thematicArea) {
                return $thematicArea->toArray();
            }, $this->thematicAreas);
        }

        if ($this->volumeOfLearning !== null) {
            $data['volumeOfLearning'] = $this->volumeOfLearning;
        }

        if ($this->nqfLevel !== null) {
            $data['nqfLevel'] = $this->nqfLevel->toArray();
        }

        if ($this->eqfLevel !== null) {
            $data['eqfLevel'] = $this->eqfLevel->toArray();
        }

        return $data;
    }
}
