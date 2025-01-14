<?php

namespace local_isycredentials\credential;

defined('MOODLE_INTERNAL') || die();

use local_isycredentials\credential\concept\learning_activity_concept;
use local_isycredentials\credential\concept\learning_assessment_concept;
use local_isycredentials\credential\concept\language_concept;

/**
 * Class learning_activity_specification
 * 
 * Description of an action, which may lead to the acquisition of knowledge, skills or responsibility and autonomy., The specification of a process which leads to the acquisition of knowledge, skills or responsibility and autonomy.
 * 
 * @see https://europa.eu/europass/elm-browser/documentation/rdf/ap/edc/documentation/edc-generic-no-cv_en.html#learning-activity-specification
 */
class learning_activity_specification extends base_entity {
    use additional_notes_trait, supplementary_documents_trait;

    public string $type = 'LearningActivitySpecification';

    /**
     * The title of the learning activity.
     */
    public localized_string $title;

    /**
     * The type of learning activity. If provided, the value must come from the list of Learning activity types (https://op.europa.eu/en/web/eu-vocabularies/concept/-/resource?uri=http://data.europa.eu/snb/learning-activity/25831c2)., 
     */
    public ?learning_activity_concept $dcType = null;

    /**
     * The estimated number of hours the learner is expected to spend engaged in learning to earn the award. This would include the notional number of hours in, in group work, in practicals, as well as hours engaged in self-motivated study., The estimated number of hours the learner is expected to spend engaged in learning to earn the award. This would include the notional number of hours in class, in group work, in practicals, as well as hours engaged in self-motivated study.
     */
    public ?int $volumeOfLearning = null;

    /**
     * The mode of learning and or assessment., The mode of learning, and/or assessment. If provided, the value should come from a controlled vocabulary (e.g. https://op.europa.eu/en/web/eu-vocabularies/dataset/-/resource?uri=http://publications.europa.eu/resource/dataset/learning-assessment). Data providers can use their own cotrolled list(s).
     */
    public ?learning_assessment_concept $mode = null;

    /**
     * The language of the Qualification.
     */
    public ?language_concept $language = null;

    public function __construct(string $id,  localized_string $title) {
        parent::__construct($id);
        $this->title = $title;
    }

    public function withType(learning_activity_concept $dcType): self {
        $this->dcType = $dcType;
        return $this;
    }

    public function withVolumeOfLearning(int $volumeOfLearning): self {
        $this->volumeOfLearning = $volumeOfLearning;
        return $this;
    }

    public function withMode(learning_assessment_concept $mode): self {
        $this->mode = $mode;
        return $this;
    }

    public function withLanguage(language_concept $language): self {
        $this->language = $language;
        return $this;
    }

    public function getId(): string {
        return 'urn:epass:learningActivitySpec:' . $this->id;
    }

    public function toArray(): array {
        $data = [
            'id' => $this->getId(),
            'type' => $this->type,
        ];

        $data['title'] = $this->title->toArray();

        if ($this->dcType) {
            $data['dcType'] = $this->dcType->toArray();
        }

        if ($this->language) {
            $data['language'] = $this->language->toArray();
        }

        if ($this->mode) {
            $data['mode'] = $this->mode->toArray();
        }

        if (isset($this->volumeOfLearning)) {
            $data['volumeOfLearning'] = 'PT' . $this->volumeOfLearning . 'H';
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

        return $data;
    }
}
