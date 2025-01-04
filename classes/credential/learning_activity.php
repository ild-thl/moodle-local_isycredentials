<?php

namespace local_isycredentials\credential;

class learning_activity extends base_entity {
    public string $type = 'LearningActivity';
    public array $title;
    public array $description;
    public awarding_process $awardedBy;
    public  $specifiedBy;


    public static function from(string $id, array $title, array $description, awarding_process $awardedBy, learning_activity_specification $specifiedBy): self {
        $learningActivity = new learning_activity($id);
        $learningActivity->title = $title;
        $learningActivity->description = $description;
        $learningActivity->awardedBy = $awardedBy;
        $learningActivity->specifiedBy = $specifiedBy;

        return $learningActivity;
    }

    public static function fromCourse(string $id, array $course, awarding_process $awardedBy): self {
        $learningActivity = new learning_activity($id);
        $learningActivity->title = ['de' => [$course['fullname']]];
        $learningActivity->description = ['de' => [$course['summary']]];
        $learningActivity->awardedBy = $awardedBy;
        $learningActivity->specifiedBy = learning_activity_specification::from(
            $id,
            $learningActivity->title
        );
        return $learningActivity;
    }

    public function getId(): string {
        return 'urn:epass:activity:' . $this->id;
    }

    public function toArray(): array {
        $data = [
            'id' => $this->getId(),
            'type' => $this->type,
            'awardedBy' => $this->awardedBy->toArray(),
            'description' => $this->description,
            'title' => $this->title,
            'specifiedBy' => $this->specifiedBy->toArray(),
        ];

        return $data;
    }
}
