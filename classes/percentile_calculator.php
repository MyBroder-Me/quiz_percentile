<?php
namespace block_quiz_percentile;

defined('MOODLE_INTERNAL') || die();

class percentile_calculator {
    public function calculate($cmid, $userid) {
        global $DB;

        if (!$cmid || !$userid) {
            return null;
        }

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

        $percentile = $DB->get_record_sql($sql, $params);

        if (!$percentile || !isset($percentile->final_percentile)) {
            return null;
        }

        return round($percentile->final_percentile, 0);
    }

}
