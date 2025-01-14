<?php

namespace local_isycredentials\credential;

defined('MOODLE_INTERNAL') || die();

use local_isycredentials\credential\concept\content_encoding_concept;
use local_isycredentials\credential\concept\content_type_concept;

/**
 * Class media_object
 * 
 * A media object., A digital file.
 * 
 * @see https://europa.eu/europass/elm-browser/documentation/rdf/ap/edc/documentation/edc-generic-no-cv_en.html#media-object
 */
class media_object extends base_entity {
    public string $type = 'MediaObject';

    /**
     * The binary data., The binary data of the media object.
     */
    public string $content;

    /**
     * The encoding used to encode the binary data. The provided value should come from the Encoding type list (http://publications.europa.eu/resource/dataset/encoding)., The encoding used to encode the binary data.
     */
    public content_encoding_concept $contentEncoding;

    /**
     * The content type of the media object. It should be provided using the Filetype Named Authority List., The type of the content of the digital file. The provided value should come from the Filetype Named Authority List (http://publications.europa.eu/resource/authority/file-type).
     */
    public content_type_concept $contentType;

    public function __construct(string $content, content_type_concept $contentType) {
        parent::__construct();
        $this->content = $content;
        $this->contentType = $contentType;
        $this->contentEncoding = content_encoding_concept::BASE64();
    }

    public static function fromBadgeImage(object $badge): self {
        $fs = get_file_storage();
        $imagefile = $fs->get_file(\context_system::instance()->id, 'badges', 'badgeimage', $badge->id, '/', 'f3.png');
        return self::fromStoredFile($imagefile);
    }

    public static function fromStoredFile(\stored_file $stored_file): self {
        $content = base64_encode($stored_file->get_content());
        $mime_type = $stored_file->get_mimetype();

        if ($mime_type === 'image/jpeg') {
            $content_type = content_type_concept::JPEG();
        } elseif ($mime_type === 'image/png') {
            $content_type = content_type_concept::PNG();
        } else {
            throw new \Exception('Unsupported image type: ' . $mime_type);
        }

        return new self($content, $content_type);
    }

    public function getId(): string {
        return 'urn:epass:mediaObject:' . $this->id;
    }

    public function toArray(): array {
        $data = [
            'id' => $this->getId(),
            'type' => $this->type,
        ];

        $data['content'] = $this->content;

        $data['contentEncoding'] = $this->contentEncoding->toArray();

        $data['contentType'] = $this->contentType->toArray();

        return $data;
    }
}
