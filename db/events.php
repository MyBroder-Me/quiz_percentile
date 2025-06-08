<?php

defined('MOODLE_INTERNAL') || die();

$observers = [
    [
        'eventname' => '\core\event\course_module_created',
        'callback' => 'block_quiz_percentile_observer::quiz_created',
        'priority' => 9999,
        'internal' => false,
    ],
];
