<?php

namespace local_isycredentials\credential;

defined('MOODLE_INTERNAL') || die();

use local_isycredentials\credential\concept\supervision_verification_concept;

/**
 * Class learning_assessment
 * 
 * The result of a process establishing the extent to which a learner has attained particular knowledge, skills and competences against criteria such as learning outcomes or standards of competence., The process of establishing the extent to which a learner has attained particular knowledge, skills and competences against criteria such as learning outcomes or standards of competence.
 * 
 * @see https://europa.eu/europass/elm-browser/documentation/rdf/ap/edc/documentation/edc-generic-no-cv_en.html#learning-assessment
 */
class learning_assessment extends base_entity {
    use additional_notes_trait, supplementary_documents_trait;

    public string $type = 'LearningAssessment';

    /**
     * The awarding details of the learning assessment.
     */
    public awarding_process $awardedBy;

    /**
     * The title of the learning assessment.
     */
    public localized_string $title;

    /**
     * The grade of the learning assessment.
     */
    public note $grade;

    /**
     * Method of assessment supervision and ID verification. If provided, the value should come from a controlled vocabulary (e.g. http://publications.europa.eu/resource/dataset/supervision-verification). Data providers can use their own cotrolled list(s)., Method of assessment supervision and id verification.
     */
    public supervision_verification_concept $idVerification;

    /**
     * The specification of the learning assessment.
     */
    public learning_assessment_specification $specifiedBy;

    /**
     * Date of formal issuance of the resource. E.g the date when the assessment was offically graded.
     *
     * @var integer|null Unix timestamp representing the date and time of the last modification.
     */
    public ?int $issued = null;

    /**
     * A description of the learning assessment.
     */
    public ?localized_string $description = null;

    /**
     * An association property, that defines a part/whole relationship between instances of the same class. A related resource that is included either physically or logically in the described resource., Smaller units of achievement, which when combined make up this achievement.
     * 
     * @var learning_assessment[]|null
     */
    public ?array $hasPart = null;

    /**
     * An association property, that defines a part/whole relationship between instances of the same class. A related resource in which the described resource is physically or logically included., A learning achievement,which this learning achievement is part of.
     * 
     * @var learning_assessment[]|null
     */
    public ?array $isPartOf = null;

    /**
     * The competent body that awarded the grade.
     *
     * @var person[]|organisation[]|null
     */
    public ?array $assessedBy = null;

    /**
     * The location where the learning assessment took place.
     */
    public ?location $location = null;

    /**
     * The acquisition of a set of knowledge and/or skills used with responsibility and autonomy (and related learning outcomes) which were acquired by successfully passing the assessment., The learning achievement (and related learning outcomes) which were acquired by successfully passing the assessment.
     */
    public ?learning_achievement $proves = null;

    public function __construct(string $id, awarding_process $awardedBy, localized_string $title, note $grade, supervision_verification_concept $idVerification, learning_assessment_specification $specifiedBy) {
        parent::__construct($id);
        $this->awardedBy = $awardedBy;
        $this->title = $title;
        $this->grade = $grade;
        $this->idVerification = $idVerification;
        $this->specifiedBy = $specifiedBy;
    }

    public function withIssued(int $issued): self {
        $this->issued = $issued;
        return $this;
    }

    public function withDescription(localized_string $description): self {
        $this->description = $description;
        return $this;
    }

    public function withHasPart(array $hasPart): self {
        // Check if the array contains only learning_assessment objects
        foreach ($hasPart as $achievement) {
            if (!($achievement instanceof learning_assessment)) {
                throw new \InvalidArgumentException('The hasPart array must contain only learning_assessment objects');
            }
        }
        $this->hasPart = $hasPart;
        return $this;
    }

    public function withIsPartOf(array $isPartOf): self {
        // Check if the array contains only learning_assessment objects
        foreach ($isPartOf as $achievement) {
            if (!($achievement instanceof learning_assessment)) {
                throw new \InvalidArgumentException('The isPartOf array must contain only learning_assessment objects');
            }
        }
        $this->isPartOf = $isPartOf;
        return $this;
    }

    public function withAssessedBy(array $assessedBy): self {
        // Check if the array contains only person or organisation objects
        foreach ($assessedBy as $assessor) {
            if (!($assessor instanceof person) && !($assessor instanceof organisation)) {
                throw new \InvalidArgumentException('The assessedBy array must contain only person or organisation objects');
            }
        }
        return $this;
    }

    public function withLocation(location $location): self {
        $this->location = $location;
        return $this;
    }

    public function withProves(learning_achievement $proves): self {
        $this->proves = $proves;
        return $this;
    }

    public function getId(): string {
        return 'urn:epass:learningAssessment:' . $this->id;
    }

    public function toArray(): array {
        $data = [
            'id' => $this->getId(),
            'type' => $this->type,
        ];

        $data['awardedBy'] = $this->awardedBy->toArray();

        $data['title'] = $this->title->toArray();

        $data['grade'] = $this->grade->toArray();

        $data['idVerification'] = $this->idVerification->toArray();

        $data['specifiedBy'] = $this->specifiedBy->toArray();

        if ($this->issued) {
            $data['issued'] = date('Y-m-d\TH:i:sP', $this->issued);
        }

        if ($this->description) {
            $data['description'] = $this->description->toArray();
        }

        if ($this->location) {
            $data['location'] = $this->location->toArray();
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

        if (!empty($this->isPartOf)) {
            $data['isPartOf'] = array_map(function (learning_assessment $assessment) {
                return $assessment->toArray();
            }, $this->isPartOf);
        }

        if (!empty($this->hasPart)) {
            $data['hasPart'] = array_map(function (learning_assessment $assessment) {
                return $assessment->toArray();
            }, $this->hasPart);
        }

        if (!empty($this->assessedBy)) {
            $data['assessedBy'] = array_map(function (base_entity $assessor) {
                return $assessor->toArray();
            }, $this->assessedBy);
        }

        if ($this->proves) {
            $data['proves'] = $this->proves->toArray();
        }

        return $data;
    }
}
