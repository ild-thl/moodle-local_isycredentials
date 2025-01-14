<?php

namespace local_isycredentials\credential;

defined('MOODLE_INTERNAL') || die();

/**
 * Class identifier
 * 
 * A character string used to identify a resource. An identifier is a character string used to uniquely identify one instance of an object within an identification scheme that is managed by an agency. The string itself only has meaning if it is contextualised., A character string that identifies either a unique object or a unique class of objects.
 * 
 * @see https://europa.eu/europass/elm-browser/documentation/rdf/ap/edc/documentation/edc-generic-no-cv_en.html#identifier
 */
class identifier extends base_entity {
    public string $type = 'Identifier';

    /**
     * The content string which is the identifier. This property is used to assign a notation as a typed literal.
     */
    public string $notation;

    /**
     * The name of the identifier scheme.
     */
    public string $schemeName;

    public function __construct(string $id, string $notation, string $schemeName) {
        parent::__construct($id);
        $this->notation = $notation;
        $this->schemeName = $schemeName;
    }

    public function getId(): string {
        return 'urn:epass:identifier:' . $this->id;
    }

    public function toArray(): array {
        return [
            'id' => $this->getId(),
            'type' => $this->type,
            'notation' => $this->notation,
            'schemeName' => $this->schemeName,
        ];
    }
}
