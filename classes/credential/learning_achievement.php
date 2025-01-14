<?php

namespace local_isycredentials\credential;

defined('MOODLE_INTERNAL') || die();

/**
 * Class learning_achievement
 * 
 * The acquisition of a set of knowledge and/or skills used with responsibility and autonomy., The acquisition of knowledge, skills or responsibility and autonomy. A recognised and/or awarded set of learning outcomes of an individual.
 * 
 * @see https://europa.eu/europass/elm-browser/documentation/rdf/ap/edc/documentation/edc-generic-no-cv_en.html#learning-achievement
 */
class learning_achievement extends base_entity {
    use additional_notes_trait, supplementary_documents_trait;

    public string $type = 'LearningAchievement';

    /**
     * The awarding details of this claim., The awarding details of the set of statements made about an Agent in the context of learning and / or employment.
     */
    public awarding_process $awardedBy;

    /**
     * The title of the learning achievement.
     */
    public localized_string $title;

    /**
     * Activities which contributed to (influenced) the acquisition of the learning outcomes that make up the achievement., Activities which contributed to the acquisition of the learning outcomes which make up the achievement.
     * 
     * @var learning_activity[]
     */
    public array $influencedBy;

    /**
     * An assessment which proves the acquisition of the learning outcomes which make up the achievement.
     * 
     * @var learning_assessment[]
     */
    public array $provenBy;

    /**
     * The Learning Achievement Specification that specifies the Learning Achievement., The specification of a learning process, e.g., the learning achievement is specified by a learning achievement specification. ranges and domains are restricted on application profile level
     */
    public qualification|learning_achievement_specification $specifiedBy;

    /**
     * Smaller units of achievement, which when combined make up this achievement., An association property, that defines a part/whole relationship between instances of the same class. A related resource that is included either physically or logically in the described resource.
     * 
     * @var learning_achievement[]|null
     */
    public ?array $hasPart = null;

    /**
     * A learning achievement, which this learning achievement is part of., An association property, that defines a part/whole relationship between instances of the same class. A related resource in which the described resource is physically or logically included.
     * 
     * @var learning_achievement[]|null
     */
    public ?array $isPartOf = null;

    /**
     * Entitlements the owner has received as a result of this achievement.
     *
     * @var learning_entitlement[]|null
     */
    public ?array $entitlesTo = null;

    /**
     * The measure demonstrating the estimated workload an individual is typically required to undertake to achieve a set of learning outcomes received for the acquisition of a set of knowledge and/or skills used with responsibility and autonomy., The credit points received for this learning achievement.
     *
     * @var credit_point|null
     */
    public ?credit_point $creditReceived = null;

    public function __construct(string $id, awarding_process $awardedBy, localized_string $title, array $influencedBy, array $provenBy, qualification $specifiedBy) {
        parent::__construct($id);
        $this->awardedBy = $awardedBy;
        $this->title = $title;
        $this->influencedBy = $influencedBy;
        $this->provenBy = $provenBy;
        $this->specifiedBy = $specifiedBy;
    }

    public function withHasPart(array $hasPart): self {
        // Check if the array contains only learning_achievement objects
        foreach ($hasPart as $achievement) {
            if (!($achievement instanceof learning_achievement)) {
                throw new \InvalidArgumentException('The hasPart array must contain only learning_achievement objects');
            }
        }
        $this->hasPart = $hasPart;
        return $this;
    }

    public function withIsPartOf(array $isPartOf): self {
        // Check if the array contains only learning_achievement objects
        foreach ($isPartOf as $achievement) {
            if (!($achievement instanceof learning_achievement)) {
                throw new \InvalidArgumentException('The isPartOf array must contain only learning_achievement objects');
            }
        }
        $this->isPartOf = $isPartOf;
        return $this;
    }

    public function withEntitlesTo(array $entitlesTo): self {
        // Check if the array contains only learning_entitlement objects
        foreach ($entitlesTo as $entitlement) {
            if (!($entitlement instanceof learning_entitlement)) {
                throw new \InvalidArgumentException('The entitlesTo array must contain only learning_entitlement objects');
            }
        }
        $this->entitlesTo = $entitlesTo;
        return $this;
    }

    public function withCreditReceived(credit_point $creditReceived): self {
        $this->creditReceived = $creditReceived;
        return $this;
    }

    public function getId(): string {
        return 'urn:epass:learningAchievement:' . $this->id;
    }

    public function toArray(): array {
        $data = [
            'id' => $this->getId(),
            'type' => $this->type,
        ];

        $data['awardedBy'] = $this->awardedBy->toArray();

        $data['title'] = $this->title->toArray();

        if ($this->creditReceived) {
            $data['creditReceived'] = $this->creditReceived->toArray();
        }

        $data['influencedBy'] = array_map(function (learning_activity $activity) {
            return $activity->toArray();
        }, $this->influencedBy);

        $data['provenBy'] = array_map(function (learning_assessment $assessment) {
            return $assessment->toArray();
        }, $this->provenBy);

        $data['specifiedBy'] = $this->specifiedBy->toArray();

        if (!empty($this->entitlesTo)) {
            $data['entitlesTo'] = array_map(function (learning_entitlement $entitlement) {
                return $entitlement->toArray();
            }, $this->entitlesTo);
        }

        if (!empty($this->isPartOf)) {
            $data['isPartOf'] = array_map(function (learning_achievement $achievement) {
                return $achievement->toArray();
            }, $this->isPartOf);
        }

        if (!empty($this->hasPart)) {
            $data['hasPart'] = array_map(function (learning_achievement $achievement) {
                return $achievement->toArray();
            }, $this->hasPart);
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
