<?php

namespace local_isycredentials\credential;

defined('MOODLE_INTERNAL') || die();

/**
 * Class learning_entitlement
 * 
 * A right, e.g., to practice a profession, take advantage of a learning opportunity or join an organisation, as a result of the acquisition of knowledge, skills, responsibility and/or autonomy., An earned right, e.g., to practice a profession, take advantage of a learning opportunity or join an organisation, as a result of the acquisition of knowledge, skills, responsibility and/or autonomy.
 * 
 * @see https://europa.eu/europass/elm-browser/documentation/rdf/ap/edc/documentation/edc-generic-no-cv_en.html#learning-entitlement
 */
class learning_entitlement extends base_entity {
    use additional_notes_trait, supplementary_documents_trait;

    public string $type = 'LearningEntitlement';

    /**
     * The title of the learning entitlement.
     */
    public localized_string $title;


    /**
     * The awarding details of this claim., The awarding details of the set of statements made about an Agent in the context of learning and / or employment.
     */
    public awarding_process $awardedBy;

    /**
     * A description of the learning assessment.
     */
    public ?localized_string $description = null;

    /**
     * The learning achievement (and related learning outcomes) which gave rise to this entitlement., The acquisition of a set of knowledge and/or skills used with responsibility and autonomy (and related learning outcomes) which gave rise to this entitlement.
     *
     * @var learning_achievement_specification[]|qualification[]|null
     */
    public ?array $entitledBy = null;

    /**
     * The date when the accreditation expires or was expired., The date when the accreditation decision expires or has expired.
     *
     * @var int|null Unix timestamp representing the time of expiry.
     */
    public ?int $expiryDate = null;

    /**
     * Date of formal issuance of the resource. E.g the date when the assessment was offically graded.
     *
     * @var integer|null Unix timestamp representing the time of issuance.
     */
    public ?int $issued = null;

    /**
     * The specification of the learning entitlement.
     */
    public ?entitlement_specification $specifiedBy = null;

    public function __construct(string $id, localized_string $title, awarding_process $awardedBy) {
        parent::__construct($id);
        $this->title = $title;
        $this->awardedBy = $awardedBy;
    }

    public function withIssued(int $issued): self {
        $this->issued = $issued;
        return $this;
    }

    public function withDescription(localized_string $description): self {
        $this->description = $description;
        return $this;
    }

    public function withEntitledBy(array $entitledBy): self {
        foreach ($entitledBy as $entitlement) {
            if (!($entitlement instanceof learning_achievement_specification) && !($entitlement instanceof qualification)) {
                throw new \Exception('entitledBy must be an instance of learning_achievement_specification or qualification');
            }
        }

        $this->entitledBy = $entitledBy;
        return $this;
    }

    public function withExpiryDate(int $expiryDate): self {
        $this->expiryDate = $expiryDate;
        return $this;
    }

    public function withSpecifiedBy(entitlement_specification $specifiedBy): self {
        $this->specifiedBy = $specifiedBy;
        return $this;
    }

    public function getId(): string {
        return 'urn:epass:learningEntitlement:' . $this->id;
    }

    public function toArray(): array {
        $data = [
            'id' => $this->getId(),
            'type' => $this->type,
        ];

        $data['title'] = $this->title->toArray();

        $data['awardedBy'] = $this->awardedBy->toArray();

        if ($this->issued) {
            $data['issued'] = date('Y-m-d\TH:i:sP', $this->issued);
        }

        if ($this->description) {
            $data['description'] = $this->description->toArray();
        }

        if (!empty($this->entitledBy)) {
            $data['entitledBy'] = array_map(function ($entitlement) {
                return $entitlement->toArray();
            }, $this->entitledBy);
        }

        if ($this->expiryDate) {
            $data['expiryDate'] = date('Y-m-d\TH:i:sP', $this->expiryDate);
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

        if ($this->specifiedBy) {
            $data['specifiedBy'] = $this->specifiedBy->toArray();
        }

        return $data;
    }
}
