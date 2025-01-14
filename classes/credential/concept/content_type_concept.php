<?php

namespace local_isycredentials\credential\concept;

defined('MOODLE_INTERNAL') || die();

/**
 * Represents a content type concept scheme.
 * 
 * @see http://publications.europa.eu/resource/authority/file-type
 */
class content_type_concept extends concept_vocabulary {
    protected static function getSchemeId(): string {
        return "http://publications.europa.eu/resource/authority/file-type";
    }

    public static function PNG(): self {
        return self::getById('http://publications.europa.eu/resource/authority/file-type/PNG');
    }

    public static function JPEG(): self {
        return self::getById('http://publications.europa.eu/resource/authority/file-type/JPEG');
    }

    public static function PDF(): self {
        return self::getById('http://publications.europa.eu/resource/authority/file-type/PDF');
    }

    public static function SVG(): self {
        return self::getById('http://publications.europa.eu/resource/authority/file-type/SVG');
    }
}
