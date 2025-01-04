<?php

defined('MOODLE_INTERNAL') || die();

$observers = array(
    array(
        'eventname' => 'badge_awarded',
        'callback' => 'local_isycredentials\observer::on_badge_awarded',
    ),
);
