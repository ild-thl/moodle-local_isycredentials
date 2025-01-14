<?php

namespace local_isycredentials\credential;

defined('MOODLE_INTERNAL') || die();

use local_isycredentials\credential\concept\learning_assessment_concept;
use local_isycredentials\credential\concept\language_concept;

/**
 * Class learning_assessment_specification
 * 
 * Description of the process by which a learner's attainment of particular knowledge, skills and competences may be established against criteria such as learning outcomes or standards of competence., A Learning Assessment Specification is a specification of a process establishing the extent to which a learner has attained particular knowledge, skills and competences against criteria such as learning outcomes or standards of competence.
 * 
 * @see https://europa.eu/europass/elm-browser/documentation/rdf/ap/edc/documentation/edc-generic-no-cv_en.html#learning-assessment-specification
 */
class learning_assessment_specification extends base_entity {
    use additional_notes_trait, supplementary_documents_trait;

    public string $type = 'LearningAssessmentSpecification';

    /**
     * The title of the learning assessment.
     */
    public localized_string $title;

    /**
     * The type of learning assessment. If provided, the value must come from the list of Learning assessment types (https://op.europa.eu/en/web/eu-vocabularies/concept/-/resource?uri=http://data.europa.eu/snb/learning-assessment/25831c2)., 
     */
    public learning_assessment_concept $dcType;

    /**
     * A set of criteria that measures varying levels of achievement., A description of the specification of which learning outcomes are or have been proven.
     */
    public grading_scheme $gradingScheme;

    /**
     * A language of the resource.
     */
    public language_concept $language;

    /**
     * The mode of learning and or assessment., The mode of learning, and/or assessment. If provided, the value should come from a controlled vocabulary (e.g. http://publications.europa.eu/resource/dataset/learning-assessment). Data providers can use their own cotrolled list(s).
     */
    public learning_assessment_concept $mode;

    /**
     * Date on which the resource was last changed.
     *
     * @var integer|null Unix timestamp representing the date and time of the last modification.
     */
    public ?int $modified = null;

    /**
     * A description of the learning assessment.
     */
    public ?localized_string $description = null;

    /**
     * The acquisition of a set of knowledge and/or skills used with responsibility and autonomy (and related learning outcomes) which were acquired by successfully passing the assessment., The learning achievement (and related learning outcomes) which were acquired by successfully passing the assessment.
     *
     * @var learning_achievement_specification[]|qualification[]|null
     */
    public ?array $proves = null;

    public function __construct(string $id, localized_string $title, learning_assessment_concept $dcType, grading_scheme $gradingScheme, language_concept $language, learning_assessment_concept $mode) {
        parent::__construct($id);
        $this->title = $title;
        $this->dcType = $dcType;
        $this->gradingScheme = $gradingScheme;
        $this->language = $language;
        $this->mode = $mode;
    }

    public function withDescription(localized_string $description): self {
        $this->description = $description;
        return $this;
    }

    public function withModified(int $modified): self {
        $this->modified = $modified;
        return $this;
    }

    public function withProves(array $proves): self {
        // Check if the array contains only learning_achievement_specification or qualification objects
        foreach ($proves as $achievement) {
            if (!($achievement instanceof learning_achievement_specification) && !($achievement instanceof qualification)) {
                throw new \InvalidArgumentException('The proves array must contain only learning_achievement_specification or qualification objects');
            }
        }
        $this->proves = $proves;
        return $this;
    }

    public function getId(): string {
        return 'urn:epass:learningAssessmentSpec:' . $this->id;
    }

    public function toArray(): array {
        $data = [
            'id' => $this->getId(),
            'type' => $this->type,
        ];

        $data['title'] = $this->title->toArray();

        $data['dcType'] = $this->dcType->toArray();

        $data['gradingScheme'] = $this->gradingScheme->toArray();

        $data['language'] = $this->language->toArray();

        $data['mode'] = $this->mode->toArray();

        if (!$this->modified) {
            $data['modified'] = date('Y-m-d\TH:i:sP', $this->modified);
        }

        if (!empty($this->description)) {
            $data['description'] = $this->description->toArray();
        }

        if (!empty($this->additionalNotes)) {
            $data['additionalNote'] = array_map(function (note $note) {
                return $note->toArray();
            }, $this->additionalNotes);
        }

        if (!empty($this->supplementaryDocuments)) {
            $data['supplementaryDocument'] = array_map(function (web_resource $document) {
                return $document->toArray();
            }, $this->supplementaryDocuments);
        }

        if (!empty($this->proves)) {
            $data['proves'] = array_map(function (base_entity $achievement) {
                return $achievement->toArray();
            }, $this->proves);
        }

        return $data;
    }
}
