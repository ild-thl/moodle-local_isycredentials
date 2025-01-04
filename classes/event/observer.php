<?php

namespace local_isycredentials\event;

defined('MOODLE_INTERNAL') || die();

use core\event\badge_awarded;

require_once(__DIR__ . '/../../lib.php');

class observer {
    public static function on_badge_awarded(badge_awarded $event) {
        $credential = local_isycredentials_create_credential_from_badge($event->badgeid, $event->userid);
        local_isycredentials_sign_document($credential);
    }
}
