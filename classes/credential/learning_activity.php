<?php

namespace local_isycredentials\credential;

defined('MOODLE_INTERNAL') || die();

/**
 * Class learning_activity
 * 
 * Any action, in process or completed, which may lead to the acquisition of knowledge, skills or responsibility and autonomy., Any process which leads to the acquisition of knowledge, skills or responsibility and autonomy.
 * 
 * @see https://europa.eu/europass/elm-browser/documentation/rdf/ap/edc/documentation/edc-generic-no-cv_en.html#learning-activity
 */
class learning_activity extends base_entity {
    use additional_notes_trait, supplementary_documents_trait;

    public string $type = 'LearningActivity';

    /**
     * The title of the learning activity.
     */
    public localized_string $title;

    /**
     * A description of the learning activity.
     */
    public ?localized_string $description = null;

    /**
     * The awarding details of this claim., The awarding details of the set of statements made about an Agent in the context of learning and / or employment.
     */
    public awarding_process $awardedBy;

    /**
     * The Learning Achievement Specification that specifies the Learning Achievement., The specification of a learning process, e.g., the learning achievement is specified by a learning achievement specification. ranges and domains are restricted on application profile level
     */
    public ?learning_activity_specification $specifiedBy = null;

    /**
     * An association property, that defines a part/whole relationship between instances of the same class. A related resource that is included either physically or logically in the described resource., Smaller units of achievement, which when combined make up this achievement.
     * 
     * @var organistaion[]|person[]|null
     */
    public ?array $directedBy = null;

    /**
     * An identifier for the entity., An identifier for the learning activity.
     */
    public ?identifier $identifier = null;

    /**
     * The level until which the learning activity has been completed. It is measured in %., The level until which any action which may lead to the acquisition of knowledge, skills or responsibility and autonomy has been completed. It is measured in %.
     */
    public ?int $levelOfCompletion = null;

    /**
     * The actual workload in number of hours the learner has spent engaged in the activity. This would include the number of hours in class, in group work, in practicals, as well as hours engaged in self-motivated study., The actual workload in number of hours the learner has spent engaged in the activity. This would include the number of hours in, in group work, in practicals, as well as hours engaged in self-motivated study.
     */
    public ?int $workload = null;

    /**
     * Temporal Coverage. Temporal characteristics of the resource.
     */
    public ?period_of_time $temporal = null;

    /**
     * The identifiable geographic place of the entity that is able to carry out actions., The location of a resource.
     */
    public ?location $location = null;

    /**
     * An association property, that defines a part/whole relationship between instances of the same class. A related resource that is included either physically or logically in the described resource., Smaller units of achievement, which when combined make up this achievement.
     * 
     * @var learning_activity[]|null
     */
    public ?array $hasPart = null;

    /**
     * An association property, that defines a part/whole relationship between instances of the same class. A related resource in which the described resource is physically or logically included., A learning achievement,which this learning achievement is part of.
     * 
     * @var learning_activity[]|null
     */
    public ?array $isPartOf = null;

    /**
     * Performing this activity contributed to the acquisition of these related learning achievements.
     *
     * @var learning_achievement[]|null
     */
    public ?array $influences = null;

    public function __construct(string $id, localized_string $title, awarding_process $awardedBy) {
        parent::__construct($id);
        $this->title = $title;
        $this->awardedBy = $awardedBy;
    }

    public static function fromCourse(string $id, \stdClass $course, organisation|person $awardedBy): self {
        $title = new localized_string($course->fullname);
        $learningActivitySpec = new learning_activity_specification(
            $id,
            $title
        );

        // TODO Awarding Body could also be the teacher(s) of the course.
        $awardedBy = new awarding_process(
            $id,
            $awardedBy
        );

        $activity = new self(
            $id,
            $title,
            $awardedBy
        );
        $activity->withSpecifiedBy($learningActivitySpec);

        if (!empty($course->summary)) {
            $activity->withDescription(new localized_string(html_to_text($course->summary)));
        }

        return $activity;
    }

    public function withDescription(localized_string $description): self {
        $this->description = $description;
        return $this;
    }

    public function withSpecifiedBy(learning_activity_specification $specifiedBy): self {
        $this->specifiedBy = $specifiedBy;
        return $this;
    }

    public function withDirectedBy(array $directedBy): self {
        // Check if the array contains only organisation or person objects
        foreach ($directedBy as $director) {
            if (!($director instanceof organisation) && !($director instanceof person)) {
                throw new \InvalidArgumentException('The directedBy array must contain only organisation or person objects');
            }
        }
        $this->directedBy = $directedBy;
        return $this;
    }

    public function withIdentifier(identifier $identifier): self {
        $this->identifier = $identifier;
        return $this;
    }

    public function withLevelOfCompletion(int $levelOfCompletion): self {
        // Check that the level of completion is between 0 and 100
        if ($levelOfCompletion < 0 || $levelOfCompletion > 100) {
            throw new \InvalidArgumentException('The level of completion must be between 0 and 100');
        }
        $this->levelOfCompletion = $levelOfCompletion;
        return $this;
    }

    public function withLocation(location $location): self {
        $this->location = $location;
        return $this;
    }

    public function withWorkload(int $workload): self {
        // Check that the workload is a positive integer
        if ($workload < 0) {
            throw new \InvalidArgumentException('The workload must be a positive integer');
        }
        $this->workload = $workload;
        return $this;
    }

    public function withTemporal(int $starDate, int $endDate): self {
        $this->temporal = new period_of_time($starDate, $endDate);

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

    public function withInfluences(array $influences): self {
        // Check if the array contains only learning_achievement objects
        foreach ($influences as $achievement) {
            if (!($achievement instanceof learning_achievement)) {
                throw new \InvalidArgumentException('The influences array must contain only learning_achievement objects');
            }
        }
        $this->influences = $influences;
        return $this;
    }

    public function getId(): string {
        return 'urn:epass:activity:' . $this->id;
    }

    public function toArray(): array {
        $data = [
            'id' => $this->getId(),
            'type' => $this->type,
        ];

        if ($this->identifier) {
            $data['identifier'] = $this->identifier->toArray();
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

        if ($this->levelOfCompletion) {
            $data['levelOfCompletion'] = $this->levelOfCompletion;
        }

        if ($this->workload) {
            $data['workload'] = 'PT' . $this->workload . 'H';
        }

        if ($this->location) {
            $data['location'] = $this->location->toArray();
        }

        if (!empty($this->directedBy)) {
            $data['directedBy'] = array_map(function ($director) {
                return $director->toArray();
            }, $this->directedBy);
        }

        $data['awardedBy'] = $this->awardedBy->toArray();

        if ($this->specifiedBy) {
            $data['specifiedBy'] = $this->specifiedBy->toArray();
        }

        if ($this->description) {
            $data['description'] = $this->description->toArray();
        }

        $data['title'] = $this->title->toArray();

        if (!empty($this->isPartOf)) {
            $data['isPartOf'] = array_map(function (learning_activity $activity) {
                return $activity->toArray();
            }, $this->isPartOf);
        }

        if (!empty($this->hasPart)) {
            $data['hasPart'] = array_map(function (learning_activity $activity) {
                return $activity->toArray();
            }, $this->hasPart);
        }

        if (!empty($this->influences)) {
            $data['influences'] = array_map(function (learning_achievement $achievement) {
                return $achievement->toArray();
            }, $this->influences);
        }

        if ($this->temporal) {
            $data['temporal'] = $this->temporal->toArray();
        }

        return $data;
    }
}
