<?php

namespace local_isycredentials\credential;

class awarding_process extends base_entity {
    public string $type = 'AwardingProcess';
    public array $awardingBody;

    public static function from(string $id, array $awardingBody): self {
        $awardingProcess = new awarding_process($id);
        $awardingProcess->awardingBody = $awardingBody;
        return $awardingProcess;
    }

    public function getId(): string {
        return 'urn:epass:awardingProcess:' . $this->id;
    }

    public function toArray(): array {
        return [
            'id' => $this->getId(),
            'type' => $this->type,
            'awardingBody' => array_map(function (Organisation $organisation) {
                return $organisation->toArray();
            }, $this->awardingBody),
        ];
    }
}
