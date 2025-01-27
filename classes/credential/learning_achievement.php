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

    // TODO: Implement decription. Currently the description has to be set in the learning_achievement_specification or qualification class.

    /**
     * The title of the learning achievement.
     */
    public localized_string $title;

    /**
     * The awarding details of this claim., The awarding details of the set of statements made about an Agent in the context of learning and / or employment.
     */
    public awarding_process $awardedBy;

    /**
     * The measure demonstrating the estimated workload an individual is typically required to undertake to achieve a set of learning outcomes received for the acquisition of a set of knowledge and/or skills used with responsibility and autonomy., The credit points received for this learning achievement.
     *
     * @var credit_point|null
     */
    public ?credit_point $creditReceived = null;

    /**
     * Entitlements the owner has received as a result of this achievement.
     *
     * @var learning_entitlement[]|null
     */
    public ?array $entitlesTo = null;

    /**
     * Smaller units of achievement, which when combined make up this achievement., An association property, that defines a part/whole relationship between instances of the same class. A related resource that is included either physically or logically in the described resource.
     * 
     * @var learning_achievement[]|null
     */
    public ?array $hasPart = null;

    /**
     * Links a resource to an adms:Identifier class.
     * 
     * @var identifier[]|legal_identifier[]|null
     */
    public ?array $identifier = null;

    /**
     * Activities which contributed to (influenced) the acquisition of the learning outcomes that make up the achievement., Activities which contributed to the acquisition of the learning outcomes which make up the achievement.
     * 
     * @var learning_activity[]|null
     */
    public ?array $influencedBy = null;

    /**
     * A learning achievement, which this learning achievement is part of., An association property, that defines a part/whole relationship between instances of the same class. A related resource in which the described resource is physically or logically included.
     * 
     * @var learning_achievement[]|null
     */
    public ?array $isPartOf = null;

    // TODO: Implement learningOpportunity. Currently I see no reason to specify opportunities in the context of a learning credential.

    /**
     * An assessment which proves the acquisition of the learning outcomes which make up the achievement.
     * 
     * @var learning_assessment[]|null
     */
    public ?array $provenBy = null;

    /**
     * The Learning Achievement Specification that specifies the Learning Achievement., The specification of a learning process, e.g., the learning achievement is specified by a learning achievement specification. ranges and domains are restricted on application profile level
     * Can be a qualification or a learning achievement specification.
     * @TODO: Validate the following explanation of the different use cases of qualifications and learning achievement specifications. 
     * A qualification should be used if a competent authority determines that an individual has achieved learning outcomes to a given standard. A learning achievement specification should be used if the learning outcomes did not follow a given standard.
     * An example of a qualification are learning outcomes required by a diploma, a certificate, a degree, a badge, etc. An example of a learning achievement specification are learning outcomes acquired through other less standardised learning processes, such as individualised training courses, workshops, personal learning experiences, etc.
     */
    public qualification|learning_achievement_specification $specifiedBy;

    public function __construct(?string $id, localized_string $title, awarding_process $awardedBy, qualification|learning_achievement_specification $specifiedBy) {
        parent::__construct($id);
        $this->awardedBy = $awardedBy;
        $this->title = $title;
        $this->specifiedBy = $specifiedBy;
    }

    public static function fromBadge(\stdClass $badge, organisation|person $awarding_body): self {
        global $DB;

        $title = new localized_string($badge->name);
        $qualification = new qualification(null, $title);
        $awardingProcess = new awarding_process(null, $awarding_body);
        $achievement = new self(null, $title, $awardingProcess, $qualification);

        // Create the achievement claim for the credential based on the badge criteria
        // Currently  supports course completion and competency awarded criteria
        // TODO Add support for other criteria types
        $sql = "SELECT bcp.value, bc.criteriatype FROM {badge_criteria} bc
                JOIN {badge_criteria_param} bcp ON bc.id = bcp.critid
                WHERE bc.badgeid = :badgeid";

        $params = ['badgeid' => $badge->id];
        $criteriatargets = $DB->get_records_sql($sql, $params);

        if (empty($criteriatargets)) {
            throw new \Exception('No completed courses or competencies found for the badge.');
        }

        // Filter criteriatargets by type
        $courseids = [];
        foreach ($criteriatargets as $criteriatarget) {
            if ($criteriatarget->criteriatype == 5) {
                $courseids[] = $criteriatarget->value;
            }
        }

        if (!empty($courseids)) {
            $influencedBy = [];
            $courserecords = $DB->get_records_list('course', 'id', $courseids);
            foreach ($courserecords as $course) {
                $influencedBy[] = learning_activity::fromCourse(
                    count($influencedBy) + 1,
                    $course,
                    $awarding_body
                );
            }
            $achievement->withInfluencedBy($influencedBy);
        }

        // // Filter criteriatargets by type
        $compids = [];
        foreach ($criteriatargets as $criteriatarget) {
            if ($criteriatarget->criteriatype == 9) {
                $compids[] = $criteriatarget->value;
            }
        }

        if (!empty($courseids)) {
            // Also get course competencies from competency_coursecomp table
            $coursecompsqlresult = $DB->get_records_list('competency_coursecomp', 'courseid', $courseids);
            foreach ($coursecompsqlresult as $coursecomp) {
                $compids[] = $coursecomp->competencyid;
            }
        }

        if (!empty($compids)) {
            $outcomes = [];
            $competencies = $DB->get_records_list('competency', 'id', $compids);
            foreach ($competencies as $competency) {
                $outcomes[] = learning_outcome::fromMoodleCompetency($competency);
            }

            $qualification->withLearningOutcomes($outcomes);
        }

        // TODO: also use badge alignment somehow. Find out what the use case is for this.

        return $achievement;
    }

    public function withInfluencedBy(array $influencedBy): self {
        // Check if the array contains only learning_activity objects
        foreach ($influencedBy as $activity) {
            if (!($activity instanceof learning_activity)) {
                throw new \InvalidArgumentException('The influencedBy array must contain only learning_activity objects');
            }
        }
        $this->influencedBy = $influencedBy;
        return $this;
    }

    public function withProvenBy(array $provenBy): self {
        // Check if the array contains only learning_assessment objects
        foreach ($provenBy as $assessment) {
            if (!($assessment instanceof learning_assessment)) {
                throw new \InvalidArgumentException('The provenBy array must contain only learning_assessment objects');
            }
        }
        $this->provenBy = $provenBy;
        return $this;
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

        if ($this->identifier) {
            $data['identifier'] = $this->identifier;
        }

        if ($this->creditReceived) {
            $data['creditReceived'] = $this->creditReceived->toArray();
        }

        if (!empty($this->influencedBy)) {
            $data['influencedBy'] = array_map(function (learning_activity $activity) {
                return $activity->toArray();
            }, $this->influencedBy);
        }

        if (!empty($this->provenBy)) {
            $data['provenBy'] = array_map(function (learning_assessment $assessment) {
                return $assessment->toArray();
            }, $this->provenBy);
        }

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
