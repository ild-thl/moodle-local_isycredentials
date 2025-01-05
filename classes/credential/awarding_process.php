<?php

namespace local_isycredentials\credential;

defined('MOODLE_INTERNAL') || die();

class awarding_process extends base_entity {
    public string $type = 'AwardingProcess';
    public awarding_body $awardingBody;

    public function __construct(?string $id = null, awarding_body $awardingBody) {
        parent::__construct($id);
        $this->awardingBody = $awardingBody;
    }

    public function getId(): string {
        return 'urn:epass:awardingProcess:' . $this->id;
    }

    public function toArray(): array {
        return [
            'id' => $this->getId(),
            'type' => $this->type,
            'awardingBody' => $this->awardingBody->toArray(),
        ];
    }
}
