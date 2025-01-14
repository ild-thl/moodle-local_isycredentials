<?php

namespace local_isycredentials\credential;

defined('MOODLE_INTERNAL') || die();

/**
 * Class grading_scheme
 * 
 * A set of criteria that measures varying levels of achievement.
 * 
 * @ see https://europa.eu/europass/elm-browser/documentation/rdf/ap/edc/documentation/edc-generic-no-cv_en.html#grading-scheme
 */
class grading_scheme extends base_entity {
    use supplementary_documents_trait;
    public string $type = 'GradingScheme';

    /**
     * A description of the grading scheme.
     */
    public localized_string $description;

    /**
     * The title of the grading scheme.
     */
    public localized_string $title;

    public function __construct(string $id, localized_string $description, localized_string $title) {
        parent::__construct($id);
        $this->description = $description;
        $this->title = $title;
    }

    public function getId(): string {
        return 'urn:epass:gradingScheme:' . $this->id;
    }

    public function toArray(): array {
        $data = [
            'id' => $this->getId(),
            'type' => $this->type,
        ];

        $data['description'] = $this->description->toArray();

        $data['title'] = $this->title->toArray();

        if (!empty($this->supplementaryDocuments)) {
            $data['supplementaryDocument'] = array_map(function (web_resource $document) {
                return $document->toArray();
            }, $this->supplementaryDocuments);
        }

        return $data;
    }
}
