<?php

namespace local_isycredentials\credential;

defined('MOODLE_INTERNAL') || die();

/**
 * Trait supplementary_documents_trait
 * 
 * Public web documents containing additional documentation about the resource.
 */
trait supplementary_documents_trait {
    /**
     * A public web document containing additional documentation about the entity., A public web document containing additional documentation about the accreditation.
     * 
     * @var web_resource[]|null
     */
    public ?array $supplementaryDocuments = null;

    /**
     * Set the supplementary documents.
     *
     * @param web_ressource[] $supplementaryDocument
     * @return self
     */
    public function withSupplementaryDocument(array $supplementaryDocument): self {
        // Check if the array contains only web_resource objects
        foreach ($supplementaryDocument as $document) {
            if (!($document instanceof web_resource)) {
                throw new \InvalidArgumentException('The supplementaryDocument array must contain only web_resource objects');
            }
        }
        $this->supplementaryDocuments = $supplementaryDocument;
        return $this;
    }
}
