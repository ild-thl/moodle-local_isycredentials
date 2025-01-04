<?php

namespace local_isycredentials\credential;

class note extends base_entity {
    public string $type = 'Note';
    public array $noteLiteral;

    public static function from(array $noteLiteral): self {
        $note = new note();
        $note->noteLiteral = $noteLiteral;
        return $note;
    }

    public function getId(): string {
        return 'urn:epass:note:' . $this->id;
    }

    public function toArray(): array {
        return [
            'id' => $this->getId(),
            'type' => $this->type,
            'noteLiteral' => $this->noteLiteral,
        ];
    }
}
