<?php

namespace local_isycredentials;

interface signing_service_interface {
    /**
     * Signs a document.
     *
     * @param string $document The document to sign.
     * @return string The signed document data.
     * @throws \Exception If any error occurs during the signing process.
     */
    public function sign(string $document): string;

    /**
     * Validates the document to be signed.
     *
     * @param string $document The document to validate.
     * @throws \Exception If validation fails.
     */
    public function validate(string $document): void;
}
