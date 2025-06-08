<?php
class block_quiz_percentile extends block_base {

    public function init() {
        $this->title = get_string('pluginname', 'block_quiz_percentile');
    }

    public function instance_allow_multiple() {
        return true;
    }

    public function applicable_formats() {
       return [
            'mod-quiz-view' => true,  // Solo disponible en la vista de cuestionarios
            'site' => false,          // No en la portada
            'course' => false,        // No en páginas de curso
            'mod' => false            // No en otros módulos
        ];
    }

    public function get_content() {
        global $USER, $PAGE, $DB;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->text = '';

        $cmid = optional_param('id', 0, PARAM_INT);
        if (!$cmid) {
            $this->content = new stdClass();
            $this->content->text = get_string('nocmid', 'block_quiz_percentile');
            return $this->content;
        }

        $userid = $USER->id;

        // Debugging: Log values in the PHP error log
        error_log("Debug Info: cmid = " . $cmid);
        error_log("Debug Info: userid = " . $userid);

    	$sql = "WITH target_quiz_name AS (
                    SELECT mq.name
                    FROM {course_modules} mcm
                    INNER JOIN {quiz} mq ON mq.id = mcm.instance
                    WHERE mcm.id = :cmid
                      AND mq.name != 'Términos de uso'
                      AND mq.course NOT IN (17, 2)
                      AND mq.id NOT IN (309, 203, 206, 79, 259, 260)
                ),
                related_quizzes AS (
                    SELECT q.id, q.name, q.course
                    FROM {quiz} q
                    INNER JOIN target_quiz_name tqn ON q.name = tqn.name
                ),
                user_grades AS (
                    SELECT
                        mqg.userid,
                        mqg.grade AS rounded_grade,
                        rq.*
                    FROM {quiz_grades} mqg
                    JOIN related_quizzes rq ON rq.id = mqg.quiz
                    JOIN {course_modules} mcm ON mcm.instance = rq.id
                    JOIN {course} c ON c.id = mcm.course
                    WHERE 1=1
                      AND c.category = 1
                      AND mcm.module = 17
                ),
                ranked AS (
                    SELECT
                        user_grades.userid as a,
                        PERCENT_RANK() OVER (ORDER BY rounded_grade) * 100 AS percentile,
                        user_grades.*
                    FROM user_grades
                ),
                total_count AS (
                    SELECT COUNT(*) AS qty FROM user_grades
                )
                SELECT
                    case
                            when r.percentile < 4 then r.percentile
                        when r.percentile between 4 and 30 then 30
                        else r.percentile
                    end AS final_percentile
                FROM
                    ranked r
                JOIN total_count tc ON
                    true
                WHERE
                    tc.qty > 24
                    and
                    r.userid = :userid;
            ";


        $params = [
            'cmid' => $cmid,
            'userid' => $userid
        ];

        $test = "reserved";

        $percentile = $DB->get_record_sql($sql, $params);
        if (!$percentile || !isset($percentile->final_percentile)) {
            $this->content->text = "Aún no hay resultados para mostrar.";
            return $this->content;
        }
        
        $percentile_final = round($percentile->final_percentile, 0);
        

        $this->content = new stdClass();
        $this->content->text = ''; // Asegura que es string
        
        if ($percentile_final !== null) {
            $this->content->text .= "Tu percentil en este cuestionario es: <strong>" . $percentile_final . "</strong>";
        }
        

        // Debugging: JavaScript logging
        $this->content->text .= "<script>
            console.log('test:', " . json_encode($test) . ");
            console.log('test:', " . json_encode($test) . ");
            console.log('test:', " . json_encode($test) . ");
        </script>";

        return $this->content;
    }
}
?>
