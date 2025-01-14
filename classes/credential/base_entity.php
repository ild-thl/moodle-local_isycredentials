<?php

namespace local_isycredentials\credential;

defined('MOODLE_INTERNAL') || die();

/**
 * Base class for all european digital credential entities.
 * 
 * @see https://europa.eu/europass/elm-browser/homepage/edc-generic-no-cv_en.html
 */
abstract class base_entity {
    /**
     * The identifier of the resource.
     *
     * @var string
     */
    public string $id;

    /**
     * Constructor.
     *
     * @param string|null $id
     */
    public function __construct(?string $id = null) {
        $this->id = $id ?? \core\uuid::generate();
    }

    /**
     * Convert the entity to an array. This array can be used to generate JSON-LD.
     *
     * @return array
     */
    abstract public function toArray(): array;

    /**
     * Get the identifier of the entity. This method might be overridden by subclasses to modify the identifier format to match the type of entity.
     *
     * @return string
     */
    public function getId(): string {
        return $this->id;
    }
}
