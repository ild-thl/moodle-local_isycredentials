<?php

namespace local_isycredentials\credential;

class awarding_process extends base_entity {
    public string $type = 'AwardingProcess';
    public array $awardingBody;

    public function __construct(string $id, array $awardingBody) {
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
            'awardingBody' => array_map(function (awarding_body $awarding_body) {
                return $awarding_body->toArray();
            }, $this->awardingBody),
        ];
    }
}
