<?php
require_once(__DIR__ . '/../../config.php');

$cmid = required_param('cmid', PARAM_INT);
require_login();
global $USER;
$userid = $USER->id;

$block = new block_quiz_percentile();
$percentile = $block->calculate_percentile($cmid, $userid);

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
