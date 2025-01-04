<?php

namespace local_isycredentials\credential;

class media_object extends base_entity {
    public string $type = 'MediaObject';
    public string $content;
    public array $contentEncoding = [
        "id" => "http://data.europa.eu/snb/encoding/6146cde7dd",
        "type" => "Concept",
        "inScheme" => [
            "id" => "http://data.europa.eu/snb/encoding/25831c2",
            "type" => "ConceptScheme"
        ],
        "prefLabel" => [
            "de" => [
                "base64"
            ],
            "en" => [
                "base64"
            ]
        ]
    ];
    public array $contentType;
    public static array $CONTENT_TYPE_PNG = [
        "id" => "http://publications.europa.eu/resource/authority/file-type/PNG",
        "type" => "Concept",
        "inScheme" => [
            "id" => "http://publications.europa.eu/resource/authority/file-type",
            "type" => "ConceptScheme"
        ],
        "prefLabel" => [
            "en" => [
                "PNG"
            ]
        ],
        "notation" => "file-type"
    ];

    public static array $CONTENT_TYPE_JPEG = [
        "id" => "http://publications.europa.eu/resource/authority/file-type/JPEG",
        "type" => "Concept",
        "inScheme" => [
            "id" => "http://publications.europa.eu/resource/authority/file-type",
            "type" => "ConceptScheme"
        ],
        "prefLabel" => [
            "en" => [
                "JPEG"
            ]
        ],
        "notation" => "file-type"
    ];

    public static function from(string $content, array $contentType): self {
        $mediaObject = new media_object();
        $mediaObject->content = $content;
        $mediaObject->contentType = $contentType;
        return $mediaObject;
    }
    public static function fromBadgeImage(object $badge): self {
        $fs = get_file_storage();
        $imagefile = $fs->get_file(\context_system::instance()->id, 'badges', 'badgeimage', $badge->id, '/', 'f3.png');
        $image_content = base64_encode($imagefile->get_content());
        $mime_type = $imagefile->get_mimetype();

        if ($mime_type === 'image/jpeg') {
            $content_type = media_object::$CONTENT_TYPE_JPEG;
        } elseif ($mime_type === 'image/png') {
            $content_type = media_object::$CONTENT_TYPE_PNG;
        } else {
            throw new \Exception('Unsupported image type: ' . $mime_type);
        }

        $mediaObject = new media_object();
        $mediaObject->content = $image_content;
        $mediaObject->contentType = $content_type;
        return $mediaObject;
    }

    public function getId(): string {
        return 'urn:epass:mediaObject:' . $this->id;
    }

    public function toArray(): array {
        $data = [
            'id' => $this->getId(),
            'type' => $this->type,
            'content' => $this->content,
            'contentEncoding' => $this->contentEncoding,
            'contentType' => $this->contentType,
        ];
        return $data;
    }
}
