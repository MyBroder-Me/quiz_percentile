<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . "/externallib.php");
require_once(__DIR__ . '/block_quiz_percentile.php');

class block_quiz_percentile_external extends external_api {

    public static function get_percentile_parameters() {
        return new external_function_parameters([
            'cmid' => new external_value(PARAM_INT, 'Course module ID'),
            'userid' => new external_value(PARAM_INT, 'User ID'),
        ]);
    }

    public static function get_percentile($cmid, $userid) {
        global $USER;

        self::validate_parameters(self::get_percentile_parameters(), ['cmid' => $cmid, 'userid' => $userid]);

        $block = new block_quiz_percentile();
        $percentile = $block->calculate_percentile($cmid, $userid);

        if ($percentile === null) {
            $percentile = 0;
        }

        return ['percentile' => $percentile];
    }

    public static function get_percentile_returns() {
        return new external_single_structure([
            'percentile' => new external_value(PARAM_INT, 'Percentile value'),
        ]);
    }
}
