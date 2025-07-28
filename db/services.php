<?php
$functions = [
    'block_quiz_percentile_get_percentile' => [
        'classname' => 'block_quiz_percentile_external',
        'methodname' => 'get_percentile',
        'classpath' => 'blocks/quiz_percentile/externallib.php',
        'description' => 'Get percentile for a user in a quiz',
        'type' => 'read',
        'ajax' => true,
    ],
];

$services = [
    'Quiz Percentile Service' => [
        'functions' => ['block_quiz_percentile_get_percentile'],
        'restrictedusers' => 0,
        'enabled' => 1,
        'shortname' => 'quizpercentileservice',
    ],
];
