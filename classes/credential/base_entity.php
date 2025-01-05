<?php

namespace local_isycredentials\credential;

defined('MOODLE_INTERNAL') || die();

abstract class base_entity {
    public string $id;

    public function __construct(?string $id = null) {
        $this->id = $id ?? \core\uuid::generate();
    }

    abstract public function toArray(): array;

    public function getId(): string {
        return $this->id;
    }
}
