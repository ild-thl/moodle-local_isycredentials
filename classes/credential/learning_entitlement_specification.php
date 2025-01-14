<?php

namespace local_isycredentials\credential;

defined('MOODLE_INTERNAL') || die();

use local_isycredentials\credential\concept\entitlement_concept;
use local_isycredentials\credential\concept\occupation_concept;

/**
 * Class entitlement_specification
 * 
 * Description of a right, e.g., to practice a profession, take advantage of a learning opportunity or join an organisation, that may be a result of the acquisition of knowledge, skills, responsibility and/or autonomy., The specification of a right a person has access to, typically as a result of a learning achievement. It may take the form of the right to be a member of an organisation, to follow a certain learning opportunity specification, or to follow a certain career.
 * 
 * @see https://europa.eu/europass/elm-browser/documentation/rdf/ap/edc/documentation/edc-generic-no-cv_en.html#learning-entitlement-specification
 */
class entitlement_specification extends base_entity {
    use additional_notes_trait, supplementary_documents_trait;

    public string $type = 'LearningEntitlementSpecification';

    /**
     * The title of the learning entitlement.
     */
    public localized_string $title;

    /**
     * The type of learning entitlement. If provided, the value must come from the list of Learning entitlement types (https://op.europa.eu/en/web/eu-vocabularies/concept/-/resource?uri=http://data.europa.eu/snb/entitlement/25831c2)., 
     */
    public entitlement_concept $dcType;

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
     * Date on which the resource was changed.
     *
     * @var integer|null Unix timestamp representing the time of the last modification.
     */
    public ?int $modified = null;

    /**
     * The specification of the learning entitlement.
     */
    public ?entitlement_specification $specifiedBy = null;

    /**
     * The ESCO occupation or occupational category which the individual may access through the entitlement. If provided, the value must come from the ESCO classification's occupation pillar (http://data.europa.eu/esco/occupation)., An ESCO Occupation or Occupational class which the individual may access through the entitlement.
     *
     * @var concept[]|null
     */
    public ?array $limitOccupation = null;

    public function __construct(string $id, localized_string $title, entitlement_concept $dcType) {
        parent::__construct($id);
        $this->title = $title;
        $this->dcType = $dcType;
    }

    public function withModified(int $modified): self {
        $this->modified = $modified;
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

    public function withLimitOccupation(array $limitOccupation): self {
        foreach ($limitOccupation as $occupation) {
            if (!($occupation instanceof occupation_concept)) {
                throw new \InvalidArgumentException('The limitOccupation array must contain only occupation_concept objects');
            }
        }

        $this->limitOccupation = $limitOccupation;
        return $this;
    }

    public function getId(): string {
        return 'urn:epass:learningEntitlementSpecification:' . $this->id;
    }

    public function toArray(): array {
        $data = [
            'id' => $this->getId(),
            'type' => $this->type,
        ];

        $data['title'] = $this->title->toArray();

        if ($this->modified) {
            $data['modified'] = date('Y-m-d\TH:i:sP', $this->modified);
        }

        $data['dcType'] = $this->dcType->toArray();

        if ($this->description) {
            $data['description'] = $this->description->toArray();
        }

        if (!empty($this->entitledBy)) {
            $data['entitledBy'] = array_map(function ($entitlement) {
                return $entitlement->toArray();
            }, $this->entitledBy);
        }

        if (!empty($this->limitOccupation)) {
            $data['limitOccupation'] = array_map(function (occupation_concept $occupation) {
                return $occupation->toArray();
            }, $this->limitOccupation);
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
