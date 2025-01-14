<?php

namespace local_isycredentials\credential;

defined('MOODLE_INTERNAL') || die();

use local_isycredentials\credential\concept\entitlement_concept;

/**
 * Class learning_achievement_specification
 * 
 * A description of what a person may learn using the opportunity, expressed as learning outcomes. A specification of learning achievement., Description of a set of knowledge and/or skills used with responsibility and autonomy, which may be acquired.
 * 
 * @see https://europa.eu/europass/elm-browser/documentation/rdf/ap/edc/documentation/edc-generic-no-cv_en.html#learning-achievement-specification
 */
class learning_achievement_specification extends base_entity {
    public string $type = 'LearningAchievementSpecification';

    /**
     * The title of the learning achievement specification.
     */
    public localized_string $title;

    /**
     * The type of learning achievement. If provided, the value must come from the list of Learning entitlement types (https://op.europa.eu/en/web/eu-vocabularies/concept/-/resource?uri=http://data.europa.eu/snb/entitlement/25831c2)., 
     */
    public ?entitlement_concept $dcType = null;

    /**
     * Date on which the resource was last changed.
     *
     * @var integer|null Unix timestamp representing the date and time of the last modification.
     */
    public ?int $modified = null;

    /**
     * A description of the learning achievement specification.
     */
    public ?localized_string $description = null;

    public function __construct(string $id, localized_string $title) {
        parent::__construct($id);
        $this->title = $title;
    }

    public function withModified(int $modified): self {
        $this->modified = $modified;
        return $this;
    }

    public function withDescription(localized_string $description): self {
        $this->description = $description;
        return $this;
    }

    public function getId(): string {
        return 'urn:epass:learningAchievementSpec:' . $this->id;
    }

    public function toArray(): array {
        $data = [
            'id' => $this->getId(),
            'type' => $this->type,
        ];

        if ($this->modified) {
            $data['modified'] = date('Y-m-d\TH:i:sP', $this->modified);
        }

        $data['dcType'] = $this->dcType->toArray();

        if ($this->description) {
            $data['description'] = $this->description->toArray();
        }

        return $data;
    }
}
