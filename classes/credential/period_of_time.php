<?php

namespace local_isycredentials\credential;

defined('MOODLE_INTERNAL') || die();

/**
 * Class period_of_time
 * 
 * An interval of time that is named or defined by its start and end dates.
 * 
 * @see https://europa.eu/europass/elm-browser/documentation/rdf/ap/edc/documentation/edc-generic-no-cv_en.html#period-of-time
 */
class period_of_time extends base_entity {
    public string $type = 'PeriodOfTime';

    /**
     * The date from when the activities take/took place., The start date of a period.
     * 
     * @var int A Unix timestamp representing the start date of the period.
     */
    public int $startDate;

    /**
     * The date until when the activities take/took place., The end date of a period.
     * 
     * @var int A Unix timestamp representing the end date of the period.
     */
    public int $endDate;

    /**
     * Constructor
     *
     * @param int $startDate A Unix timestamp representing the start date of the period.
     * @param int $endDate A Unix timestamp representing the end date of the period.
     */
    public function __construct(int $startDate, int $endDate) {
        parent::__construct();
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function getId(): string {
        return 'urn:epass:period:' . $this->id;
    }

    public function toArray(): array {
        $data = [
            'id' => $this->getId(),
            'type' => $this->type,
        ];

        $data['endDate'] = date('Y-m-d\TH:i:sP', $this->endDate);
        $data['starDate'] = date('Y-m-d\TH:i:sP', $this->startDate);

        return $data;
    }
}
