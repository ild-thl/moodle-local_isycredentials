<?php

namespace local_isycredentials\credential;

class learning_activity_specification extends base_entity {
    public string $type = 'LearningActivitySpecification';
    public array $title;

    public static function from(string $id,  array $title): self {
        $learningActivitySpecification = new learning_activity_specification($id);
        $learningActivitySpecification->title = $title;
        return $learningActivitySpecification;
    }
    public function title(array $title): self {
        $this->title = $title;
        return $this;
    }

    public function getId(): string {
        return 'urn:epass:learningActivitySpec:' . $this->id;
    }

    public function toArray(): array {
        return [
            'id' => $this->getId(),
            'type' => $this->type,
            'title' => $this->title
        ];
    }
}
