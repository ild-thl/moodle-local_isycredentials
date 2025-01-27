<?php

namespace local_isycredentials\credential;

defined('MOODLE_INTERNAL') || die();

use local_isycredentials\credential\concept\concept;
use local_isycredentials\credential\concept\learning_opportunity_concept;
use local_isycredentials\credential\concept\learning_setting_concept;
use local_isycredentials\credential\concept\learning_assessment_concept;
use local_isycredentials\credential\concept\language_concept;
use local_isycredentials\credential\concept\target_group_concept;
use local_isycredentials\credential\concept\iscedf_concept;
use local_isycredentials\credential\concept\accreditation_status_concept;

/**
 * Class learning_achievement_specification
 * 
 * A description of what a person may learn using the opportunity, expressed as learning outcomes. A specification of learning achievement., Description of a set of knowledge and/or skills used with responsibility and autonomy, which may be acquired.
 * 
 * @see https://europa.eu/europass/elm-browser/documentation/rdf/ap/edc/documentation/edc-generic-no-cv_en.html#learning-achievement-specification
 */
class learning_achievement_specification extends base_entity {
    use additional_notes_trait, supplementary_documents_trait;

    public string $type = 'LearningAchievementSpecification';

    /**
     * Date on which the resource was last changed.
     *
     * @var integer|null Unix timestamp representing the date and time of the last modification.
     */
    public ?int $modified = null;

    /**
     * A description of the Qualification.
     */
    public ?localized_string $description = null;

    /**
     * The language of the Qualification.
     */
    public ?language_concept $language = null;

    /**
     * the title of the Qualification.
     */
    public localized_string $title;

    /**
     * The type of learning opportunity. If provided, the value must come from the list of Learning opportunity types (https://op.europa.eu/en/web/eu-vocabularies/concept/-/resource?uri=http://data.europa.eu/snb/learning-opportunity/25831c2)., 
     */
    public ?learning_opportunity_concept $dcType = null;

    // TODO: Implement awardingOpportunity. For now I see no reason to specify opportunities in the context of learning credential.

    /**
     * The category as a string of the description of a set of knowledge and/or skills used with responsibility and autonomy, which may be acquired., A category to which this specification belongs. This property can be used instead of dc:type, if the category cannot provided via a controlled vocabulary.
     * 
     * @var string[]|null
     */
    public ?array $category = null;

    /**
     * The credit points assigned to the qualification, following a credit system., A measure demonstrating the estimated workload an individual is typically required to undertake to achieve a set of learning outcomes assigned to the learning achievement specification, following an educational credit system.
     */
    public ?credit_point $creditPoint = null;

    /**
     * An associated level of education within a semantic framework describing education levels. If provided, the value should come from a controlled vocabulary. Data providers can use their own cotrolled list(s)., An associated level of education within a semantic framework describing education levels.
     * 
     * @var concept[]|null
     */
    public ?array $educationLevel = null;

    /**
     * An associated field of education from another semantic framework than the ISCED classification., An associated field of education from a different semantic framework than the ISCED-F classification. If provided, the value should come from a controlled vocabulary. Data providers can use their own cotrolled list(s).
     * 
     * @var iscedf_concept[]|null
     */
    public ?array $educationSubject = null;

    /**
     * Entitlements the owner has received as a result of this achievement.
     * 
     * @var learning_entitlement_specification[]|null
     */
    public ?learning_entitlement_specification $entitlesTo = null;

    /**
     * Specific entry requirement or prerequisite of individuals for which this learning achievement specification is designed to start this learning opportunity., Specific entry requirements or prerequisites of individuals for which this specification is designed to start this learning opportunity.
     */
    public ?note $entryRequirement = null;

    // TODO: Implement generalisationOf. For now I see no reason for the need of generalisations.

    // TODO: Implement hasPart. For now I see no reason for this while learning achievements already have a part/whole relationship.

    /**
     * A homepage for some thing.
     *
     * @var web_resource[]|null
     */
    public ?array $homepage = null;

    /**
     * Links a resource to an adms:Identifier class.
     * 
     * @var identifier[]|legal_identifier[]|null
     */
    public ?array $identifier = null;

    // TODO: Implement influencedBy. For now I see no reason for this while learning achievements already have an influence relationship.

    // TODO: Implement isPartOf. For now I see no reason for this while learning achievements already have a part/whole relationship.

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
     * The maximum duration (in months) that a person may use to complete the learning opportunity., The maximum duration (in months) that a person may use to complete the learning opportunity for which this learning achievement specification is designed.
     */
    public ?int $maximumDuration = null;

    /**
     * The mode of learning and or assessment., The mode of learning, and/or assessment. If provided, the value should come from a controlled vocabulary (e.g. https://op.europa.eu/en/web/eu-vocabularies/dataset/-/resource?uri=http://publications.europa.eu/resource/dataset/learning-assessment). Data providers can use their own cotrolled list(s).
     */
    public ?learning_assessment_concept $mode = null;

    // TODO: Implement specialisationOf. For now I see no reason for the need for specialisations.

    /**
     * The status. It can be the status of the verification check, Entitlement specification etc, The publication status of the quality assurance or licensing of an organisation or a qualification. If provided, the value must come from the Accredication status controlled vocabulary (http://publications.europa.eu/resource/dataset/accreditation-status).
     */
    public ?accreditation_status_concept $status = null;

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

    public function __construct(?string $id, localized_string $title) {
        parent::__construct($id);
        $this->title = $title;
    }

    public function withModified(int $modified): self {
        $this->modified = $modified;
        return $this;
    }

    public function withDescription(localized_string $description): self {
        $this->description = $description;
        return $this;
    }

    public function withDcType(learning_opportunity_concept $dcType): self {
        $this->dcType = $dcType;
        return $this;
    }

    public function withCategory(array $categories): self {
        // Check if the array contains only strings
        foreach ($categories as $category) {
            if (!is_string($category)) {
                throw new \InvalidArgumentException('The category array must contain only strings');
            }
        }
        $this->category = $categories;
        return $this;
    }

    public function withCreditPoint(credit_point $creditPoint): self {
        $this->creditPoint = $creditPoint;
        return $this;
    }

    public function withEducationLevel(array $educationLevel): self {
        // Check if the array contains only concept objects
        foreach ($educationLevel as $level) {
            if (!($level instanceof concept)) {
                throw new \InvalidArgumentException('The educationLevel array must contain only concept objects');
            }
        }
        $this->educationLevel = $educationLevel;
        return $this;
    }

    public function withEducationSubject(array $educationSubject): self {
        // Check if the array contains only iscedf_concept objects
        foreach ($educationSubject as $subject) {
            if (!($subject instanceof iscedf_concept)) {
                throw new \InvalidArgumentException('The educationSubject array must contain only iscedf_concept objects');
            }
        }
        $this->educationSubject = $educationSubject;
        return $this;
    }

    public function withEntitlesTo(array $entitlesTo): self {
        // Check if the array contains only learning_entitlement_specification objects
        foreach ($entitlesTo as $entitlement) {
            if (!($entitlement instanceof learning_entitlement_specification)) {
                throw new \InvalidArgumentException('The entitlesTo array must contain only learning_entitlement_specification objects');
            }
        }
        $this->entitlesTo = $entitlesTo;
        return $this;
    }

    public function withEntryRequirement(note $entryRequirement): self {
        $this->entryRequirement = $entryRequirement;
        return $this;
    }

    public function withHomepage(array $homepage): self {
        // Check if the array contains only web_resource objects
        foreach ($homepage as $webResource) {
            if (!($webResource instanceof web_resource)) {
                throw new \InvalidArgumentException('The homepage array must contain only web_resource objects');
            }
        }
        $this->homepage = $homepage;
        return $this;
    }

    public function withIdentifier(array $identifier): self {
        // Check if the array contains only identifier objects
        foreach ($identifier as $id) {
            if (!($id instanceof identifier || $id instanceof legal_identifier)) {
                throw new \InvalidArgumentException('The identifier array must contain only identifier or legal_identifier objects');
            }
        }
        $this->identifier = $identifier;
        return $this;
    }

    public function withLanguage(language_concept $language): self {
        $this->language = $language;
        return $this;
    }

    public function withLearningOutcomes(array $learningOutcomes): self {
        // Check if the array contains only learning_outcome objects
        foreach ($learningOutcomes as $learningOutcome) {
            if (!($learningOutcome instanceof learning_outcome)) {
                throw new \InvalidArgumentException('The learningOutcomes array must contain only learning_outcome objects');
            }
        }
        $this->learningOutcomes = $learningOutcomes;
        return $this;
    }

    public function withLearningOutcomeSummary(note $learningOutcomeSummary): self {
        $this->learningOutcomeSummary = $learningOutcomeSummary;
        return $this;
    }

    public function withLearningSetting(learning_setting_concept $learningSetting): self {
        $this->learningSetting = $learningSetting;
        return $this;
    }

    public function withMaximumDuration(int $maximumDuration): self {
        $this->maximumDuration = $maximumDuration;
        return $this;
    }

    public function withMode(learning_assessment_concept $mode): self {
        $this->mode = $mode;
        return $this;
    }

    public function withStatus(accreditation_status_concept $status): self {
        $this->status = $status;
        return $this;
    }

    public function withTargetGroups(array $targetGroups): self {
        // Check if the array contains only concept objects
        foreach ($targetGroups as $targetGroup) {
            if (!($targetGroup instanceof target_group_concept)) {
                throw new \InvalidArgumentException('The targetGroups array must contain only target_group_concept objects');
            }
        }
        $this->targetGroups = $targetGroups;
        return $this;
    }

    public function withThematicAreas(array $thematicAreas): self {
        // Check if the array contains only concept objects
        foreach ($thematicAreas as $thematicArea) {
            if (!($thematicArea instanceof iscedf_concept)) {
                throw new \InvalidArgumentException('The thematicAreas array must contain only iscedf_concept objects');
            }
        }
        $this->thematicAreas = $thematicAreas;
        return $this;
    }

    public function withVolumeOfLearning(string $volumeOfLearning): self {
        $this->volumeOfLearning = $volumeOfLearning;
        return $this;
    }

    public function getId(): string {
        return 'urn:epass:learningAchievementSpec:' . $this->id;
    }

    public function toArray(): array {
        $data = [
            'id' => $this->getId(),
            'type' => $this->type,
        ];

        if ($this->modified) {
            $data['modified'] = date('Y-m-d\TH:i:sP', $this->modified);
        }

        if ($this->description !== null) {
            $data['description'] = $this->description->toArray();
        }

        if ($this->language !== null) {
            $data['language'] = $this->language->toArray();
        }

        $data['title'] = $this->title->toArray();

        if ($this->dcType !== null) {
            $data['dcType'] = $this->dcType->toArray();
        }

        if (!empty($this->additionalNotes)) {
            $data['additionalNote'] = array_map(function (note $note) {
                return $note->toArray();
            }, $this->additionalNotes);
        }

        if (!empty($this->category)) {
            $data['category'] = $this->category;
        }

        if ($this->creditPoint !== null) {
            $data['creditPoint'] = $this->creditPoint->toArray();
        }

        if (!empty($this->educationLevel)) {
            $data['educationLevel'] = array_map(function (concept $level) {
                return $level->toArray();
            }, $this->educationLevel);
        }

        if (!empty($this->educationSubject)) {
            $data['educationSubject'] = array_map(function (iscedf_concept $subject) {
                return $subject->toArray();
            }, $this->educationSubject);
        }

        if (!empty($this->entitlesTo)) {
            $data['entitlesTo'] = array_map(function (learning_entitlement_specification $entitlesTo) {
                return $entitlesTo->toArray();
            }, $this->entitlesTo);
        }

        if ($this->entryRequirement !== null) {
            $data['entryRequirement'] = $this->entryRequirement->toArray();
        }

        if ($this->homepage) {
            $data['homepage'] = $this->homepage;
        }

        if ($this->identifier) {
            $data['identifier'] = $this->identifier;
        }

        if ($this->learningOutcomes !== null) {
            $data['learningOutcomes'] = array_map(function ($learningOutcome) {
                return $learningOutcome->toArray();
            }, $this->learningOutcomes);
        }

        if ($this->learningOutcomeSummary !== null) {
            $data['learningOutcomeSummary'] = $this->learningOutcomeSummary->toArray();
        }

        if ($this->learningSetting !== null) {
            $data['learningSetting'] = $this->learningSetting->toArray();
        }

        if ($this->maximumDuration > -1) {
            $data['maximumDuration'] = "P{$this->maximumDuration}M";
        }

        if ($this->mode !== null) {
            $data['mode'] = $this->mode->toArray();
        }

        if ($this->status !== null) {
            $data['status'] = $this->status->toArray();
        }

        if (!empty($this->supplementaryDocuments)) {
            $data['supplementaryDocument'] = array_map(function (web_resource $document) {
                return $document->toArray();
            }, $this->supplementaryDocuments);
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

        return $data;
    }
}
