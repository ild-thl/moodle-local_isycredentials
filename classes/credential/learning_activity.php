<?php

namespace local_isycredentials\credential;

defined('MOODLE_INTERNAL') || die();

class learning_activity extends base_entity {
    public string $type = 'LearningActivity';
    public localized_string $title;
    public localized_string $description;
    public awarding_process $awardedBy;
    public $specifiedBy;

    public function __construct(string $id, localized_string $title, localized_string $description, awarding_process $awardedBy, learning_activity_specification $specifiedBy) {
        parent::__construct($id);
        $this->title = $title;
        $this->description = $description;
        $this->awardedBy = $awardedBy;
        $this->specifiedBy = $specifiedBy;
    }

    public static function fromCourse(string $id, array $course, awarding_process $awardedBy): self {
        $title = new localized_string($course['fullname']);
        $description = new localized_string($course['summary']);
        $learningActivitySpec = new learning_activity_specification(
            $id,
            $title
        );
        $awardedBy = $awardedBy; // TODO Awarding Body could also be the teacher(s) of the course.

        return new self(
            $id,
            $title,
            $description,
            $awardedBy,
            $learningActivitySpec,
        );
    }

    public function getId(): string {
        return 'urn:epass:activity:' . $this->id;
    }

    public function toArray(): array {
        $data = [
            'id' => $this->getId(),
            'type' => $this->type,
            'awardedBy' => $this->awardedBy->toArray(),
            'specifiedBy' => $this->specifiedBy->toArray(),
            'description' => $this->description->toArray(),
            'title' => $this->title->toArray(),
        ];

        return $data;
    }
}
