<?php
defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/blocks/quiz_percentile/classes/percentile_calculator.php');
use block_quiz_percentile\percentile_calculator;
class block_quiz_percentile extends block_base {

    public function init() {
        $this->title = get_string('pluginname', 'block_quiz_percentile');
    }

    public function instance_allow_multiple() {
        return true;
    }

    public function applicable_formats() {
       return [
            'mod-quiz-view' => true,
            'site' => false,
            'course' => false,
            'mod' => false
        ];
    }
    public function get_content() {
        global $USER;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();

        $cmid = optional_param('id', 0, PARAM_INT);
        if (!$cmid) {
            $this->content->text = get_string('nocmid', 'block_quiz_percentile');
            return $this->content;
        }

        $userid = $USER->id;
        $percentile = percentile_calculator::calculate($cmid, $userid);

        if ($percentile === null) {
            $this->content->text = "Aún no hay resultados para mostrar.";
        } else {
            $this->content->text = "Tu percentil en este cuestionario es: <strong>$percentile</strong>";
        }

        return $this->content;
    }
}
