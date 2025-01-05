<?php

namespace local_isycredentials\credential;

class learning_activity_specification extends base_entity {
    public string $type = 'LearningActivitySpecification';
    public localized_string $title;

    public function __construct(string $id,  localized_string $title) {
        parent::__construct($id);
        $this->title = $title;
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
            'title' => $this->title->toArray()
        ];
    }
}
