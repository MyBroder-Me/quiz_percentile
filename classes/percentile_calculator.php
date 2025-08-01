<?php
namespace block_quiz_percentile;

defined('MOODLE_INTERNAL') || die();

class percentile_calculator {
    public static function calculate($cmid, $userid) {
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
                        rq.name,
                        ROUND(AVG(mqg.grade), 5) AS rounded_grade
                    FROM mdl_quiz_grades mqg
                    JOIN related_quizzes rq ON rq.id = mqg.quiz
                    JOIN mdl_course_modules mcm ON mcm.instance = rq.id
                    JOIN mdl_course c ON c.id = mcm.course
                    WHERE
                        c.category = 1
                        AND mcm.module = 17
                    GROUP BY mqg.userid, rq.name
                ),
                ranked AS (
                    SELECT
                        ug.userid,
                        ug.name,
                        ug.rounded_grade,
                        PERCENT_RANK() OVER (PARTITION BY ug.name ORDER BY ug.rounded_grade) * 100 AS percentile
                    FROM user_grades ug
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
