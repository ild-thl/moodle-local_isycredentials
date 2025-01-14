<?php

namespace local_isycredentials\credential;

defined('MOODLE_INTERNAL') || die();

/**
 * Class awarding_process
 * 
 * The conditions under which a claim is made about an Agent. Such process entails an organisation making a Claim to an Agent based on a Specification. It is used to specify the organisation that awarded the Claim to the Agent, or the country or region where the Claim was made, and optionally the date., The process of an organisation making a Claim to person based on a Specification. It is used to specify the organisation that awarded the Claim to the individual, or the country or region where the Claim was made, and optionally the date.
 * 
 * @see https://europa.eu/europass/elm-browser/documentation/rdf/ap/edc/documentation/edc-generic-no-cv_en.html#awarding-process
 */
class awarding_process extends base_entity {
    public string $type = 'AwardingProcess';

    /**
     * The awarding body related to this awarding activity (i.e., the organisation that issues the qualification) Only in cases of co-awarding/co-graduation, where a qualification is issued to an individual by two or more organisations the cardinality is greater than 1., The awarding body related to this awarding activity (i.e., the organisation that issues the qualification). Only in cases of co-awarding/co-graduation, where a qualification is issued to an individual by two or more organisations, the cardinality is greater than 1.
     * The European Digital Credential model does allow more than one awarding body to be specified.
     */
    public organisation|person $awardingBody;

    public function __construct(?string $id = null, organisation|person $awardingBody) {
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
