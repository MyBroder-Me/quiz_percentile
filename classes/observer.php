<?php
defined('MOODLE_INTERNAL') || die();

class block_quiz_percentile_observer {

    public static function quiz_created(\core\event\course_module_created $event) {
        global $DB;

        // Obtén información del módulo para asegurarte de que es un cuestionario
        $cm = get_coursemodule_from_id(null, $event->objectid);
        if (!$cm || $cm->modname !== 'quiz') {
            return;
        }

        // Verifica si ya existe el bloque en el contexto
        $existing = $DB->get_records('block_instances', [
            'blockname' => 'quiz_percentile',
            'parentcontextid' => $event->contextid
        ]);

        if ($existing) {
            return; // Ya existe, no hacer nada
        }

        // Añadir el bloque manualmente a la tabla
        $blockinstance = new stdClass();
        $blockinstance->blockname = 'quiz_percentile';
        $blockinstance->parentcontextid = $event->contextid;
        $blockinstance->showinsubcontexts = 0;
        $blockinstance->pagetypepattern = 'mod-quiz-view';
        $blockinstance->defaultregion = 'side-pre'; // Puedes cambiarlo si usas otra región
        $blockinstance->defaultweight = 0;
        $blockinstance->configdata = ''; // Vacío por defecto
        $blockinstance->timecreated = time();
        $blockinstance->timemodified = time();

        $DB->insert_record('block_instances', $blockinstance);
    }
}
