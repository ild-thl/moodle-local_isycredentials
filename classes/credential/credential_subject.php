<?php

namespace local_isycredentials\credential;

defined('MOODLE_INTERNAL') || die();

/**
 * Class credential_subject
 * 
 * The person (subject) about which claims are made and who owns the credential.
 */
class credential_subject extends person {
    /**
     * A claim of the person. A person should at least have one claim., A set of statements made about an Agent in the context of learning and / or employment of a human being. A person should have at least one claim. It is strongly recommended to use the subclasses of the Claim directly here (e.g Learning Achievement, Learning Activity, Learning Assessment, Learning Entitlement) and only use the Claim superclass in the rare occasion when the claim provided is not covered by the existing subclasses.
     * 
     * @var learning_achievement[]|learning_activity[]|learning_assessment[]|learning_entitlement
     */
    public array $hasClaim;

    public function __construct(string $id, string $givenName, string $familyName, string $fullName, array $hasClaim) {
        parent::__construct($id, $givenName, $familyName, $fullName);
        // Check if the array contains only instances of learning_achievement, learning_activity, learning_assessment, learning_entitlement
        foreach ($hasClaim as $claim) {
            if (!($claim instanceof learning_achievement || $claim instanceof learning_activity || $claim instanceof learning_assessment || $claim instanceof learning_entitlement)) {
                throw new \Exception('The array hasClaim should only contain instances of learning_achievement, learning_activity, learning_assessment, learning_entitlement');
            }
        }
        $this->hasClaim = $hasClaim;
    }

    public function toArray(): array {
        $data = parent::toArray();
        $data['hasClaim'] = array_map(function ($claim) {
            return $claim->toArray();
        }, $this->hasClaim);
        return $data;
    }
}
