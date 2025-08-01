<?php
require_once(__DIR__ . '/../../config.php');
$PAGE->set_pagelayout('embedded');
require_once($CFG->dirroot.'/blocks/quiz_percentile/classes/percentile_calculator.php');

use block_quiz_percentile\percentile_calculator;

$cmid = required_param('cmid', PARAM_INT);
require_login();
global $USER;
$userid = $USER->id;

$percentile = percentile_calculator::calculate($cmid, $userid);

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/blocks/quiz_percentile/viewpercentile.php', ['cmid' => $cmid]));
$PAGE->set_title("Percentil del Quiz");
$PAGE->set_heading("Percentil del Quiz");

echo $OUTPUT->header();

if ($percentile === null) {
    echo html_writer::tag('p', "Aún no hay resultados para mostrar.");
} else {
    echo html_writer::tag('p', "Tu percentil en este cuestionario es: <strong>$percentile</strong>");
}

echo $OUTPUT->footer();
