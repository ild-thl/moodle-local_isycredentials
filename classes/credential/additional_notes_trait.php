<?php

namespace local_isycredentials\credential;

defined('MOODLE_INTERNAL') || die();

/**
 * Trait additional_notes_trait
 * 
 * Additional free text note for further information about the resource.
 */
trait additional_notes_trait {
    /**
     * Additional notes about the learning activity.
     * 
     * @var note[]|null
     */
    public ?array $additionalNotes = null;


    /**
     * Set the additional notes.
     *
     * @param note[] $additionalNotes
     * @return self
     */
    public function withAdditionalNotes(array $additionalNotes): self {
        // Check if the array contains only note objects
        foreach ($additionalNotes as $note) {
            if (!($note instanceof note)) {
                throw new \InvalidArgumentException('The additionalNotes array must contain only note objects');
            }
        }
        $this->additionalNotes = $additionalNotes;
        return $this;
    }
}
