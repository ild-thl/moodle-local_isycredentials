<?php

namespace local_isycredentials\credential;

defined('MOODLE_INTERNAL') || die();

use local_isycredentials\credential\concept\concept;
use local_isycredentials\credential\concept\concept_scheme;
use local_isycredentials\credential\concept\esco_skills_concept;
use local_isycredentials\credential\concept\dcf_skills_concept;
use local_isycredentials\credential\concept\skill_reuse_level_concept;

/**
 * Class learning_outcome
 * 
 * A statement regarding what a learner knows, understands and is able to do on completion of a learning process, which are defined in terms of knowledge, skills and responsibility and autonomy.
 * 
 * @see https://europa.eu/europass/elm-browser/documentation/rdf/ap/edc/documentation/edc-generic-no-cv_en.html#learning-outcome
 */
class learning_outcome extends base_entity {
    use additional_notes_trait;

    public string $type = 'LearningOutcome';

    /**
     * The title of the learning outcome.
     */
    public localized_string $title;

    /**
     * A link to an ESCO Skill., A link to an ESCO skill. If provided, the value must come from the ESCO classification's skill pillar (http://data.europa.eu/esco/skill).
     *
     * @var esco_skills_concept[]|null
     */
    public ?array $relatedESCOSkills;

    /**
     * A link to a related skill or the level of a related skill on a skill framework (except ESCO)., A link to a related skill or the level of a related skill on a skill framework (except ESCO). If provided, the value should come from a controlled vocabulary.
     *
     * @var concept[]|null
     */
    public ?array $relatedSkills;

    /**
     * The reusability level., An indication of how widely a knowledge, skill or competence concept can be applied. If provided, the value must come from the ESCO Skill reusability level list (http://publications.europa.eu/resource/dataset/skill-reuse-level).
     */
    public ?skill_reuse_level_concept $reusabilityLevel = null;

    public function __construct(?string $id, localized_string $title) {
        parent::__construct($id);
        $this->title = $title;
    }

    public static function fromMoodleCompetency(\stdClass $moodleCompetency): self {
        global $DB;

        $title = new localized_string($moodleCompetency->shortname);

        $relevantMoodleCompetencies = [$moodleCompetency];
        // Get children of competency, by getting all competencies with parentid = id
        $children = $DB->get_records('competency', ['parentid' => $moodleCompetency->id]);
        $relevantMoodleCompetencies = array_merge($relevantMoodleCompetencies, $children);

        $relatedESCOSkills = [];
        $relatedSkills = [];
        $unknownFrameworks = [];

        // get all unique competencyframeworkids from competencies that are not esco or dcf
        $unknownFrameworkIds = array_unique(array_map(function ($comp) {
            if (!str_contains($comp->idnumber, 'data.europa.eu/esco') && !str_contains($comp->idnumber, 'data.europa.eu/snb/dcf')) {
                return $comp->competencyframeworkid;
            }
        }, $relevantMoodleCompetencies));

        // get all competencyframeworks
        $unknownFrameworks = $DB->get_records_list('competency_framework', 'id', $unknownFrameworkIds);


        foreach ($relevantMoodleCompetencies as $comp) {
            // Include self if idnumber contains "data.europa.eu/esco"
            if (str_contains($comp->idnumber, 'data.europa.eu/esco')) {
                $relatedESCOSkills[] = esco_skills_concept::getById($comp->idnumber);
            } elseif (str_contains($comp->idnumber, 'data.europa.eu/snb/dcf')) {
                $relatedSkills[] = dcf_skills_concept::getById($comp->idnumber);
            } else {
                $framework = $unknownFrameworks[$comp->competencyframeworkid];
                $inScheme = new concept_scheme($framework->idnumber);
                $relatedSkills[] = new concept($comp->idnumber, new localized_string($comp->shortname), $inScheme);
            }
        }

        $outcome = (new learning_outcome(null, $title))
            ->withRelatedESCOSkills($relatedESCOSkills)
            ->withRelatedSkills($relatedSkills);

        if (!empty($moodleCompetency->description)) {
            $outcome->withAdditionalNotes([new note(new localized_string($moodleCompetency->description))]);
        }

        return $outcome;
    }

    public function withRelatedESCOSkills(array $relatedESCOSkills): self {
        if (empty($relatedESCOSkills)) {
            return $this;
        }
        foreach ($relatedESCOSkills as $relatedESCOSkill) {
            if (!($relatedESCOSkill instanceof esco_skills_concept)) {
                throw new \Exception('relatedESCOSkill must be an instance of concept');
            }
        }

        $this->relatedESCOSkills = $relatedESCOSkills;
        return $this;
    }

    public function withRelatedSkills(array $relatedSkills): self {
        if (empty($relatedSkills)) {
            return $this;
        }
        foreach ($relatedSkills as $relatedSkill) {
            if (!($relatedSkill instanceof concept)) {
                throw new \Exception('relatedSkill must be an instance of concept');
            }
        }

        $this->relatedSkills = $relatedSkills;
        return $this;
    }

    public function withReusabilityLevel(skill_reuse_level_concept $reusabilityLevel): self {
        $this->reusabilityLevel = $reusabilityLevel;
        return $this;
    }

    public function getId(): string {
        return 'urn:epass:LearningOutcome:' . $this->id;
    }

    public function toArray(): array {
        $data = [
            'id' => $this->getId(),
            'type' => $this->type,
        ];

        $data['title'] = $this->title->toArray();

        if (!empty($this->additionalNotes)) {
            $data['additionalNote'] = array_map(function (note $note) {
                return $note->toArray();
            }, $this->additionalNotes);
        }

        if (!empty($this->relatedESCOSkills)) {
            $data['relatedESCOSkills'] = array_map(function ($relatedESCOSkill) {
                return $relatedESCOSkill->toArray();
            }, $this->relatedESCOSkills);
        }

        if (!empty($this->relatedSkills)) {
            $data['relatedSkills'] = array_map(function ($relatedSkill) {
                return $relatedSkill->toArray();
            }, $this->relatedSkills);
        }

        if ($this->reusabilityLevel) {
            $data['reusabilityLevel'] = $this->reusabilityLevel->toArray();
        }

        return $data;
    }
}
