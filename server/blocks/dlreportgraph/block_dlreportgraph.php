<?php
use totara_competency\entity\competency_achievement;
require_once($CFG->dirroot . '/completion/completion_completion.php');

class block_dlreportgraph extends block_base {
    function init() {
        $this->title = get_string('pluginname', 'block_dlreportgraph');
    }

    function has_config()
    {
        return true;
    }

    function get_content() {
        global $USER, $OUTPUT;
        if ($this->content !== NULL) {
            return $this->content;
        }
        if (empty($this->content)) {
            $this->content = new stdClass();
        }

        $data = $this->get_data();

        if(!$data) {
            $this->content->text = "";
            return $this->content;
        }

        $component = new \totara_tui\output\component('dl/pages/ReportGraphblock', [
            'props' => json_encode([
                'competencies' => $data['competencies'],
                'courses' => $data['courses']
            ])
        ]);

        $this->content->text = $OUTPUT->render($component);

        return $this->content;
    }

    public function get_data() {
        global $DB, $USER;

        // ----- Datos de competencias -----
        $competency_sql = "SELECT 
            COUNT(CASE WHEN tca.proficient = 1 THEN 1 END) as count_completedcompetencies,
            COUNT(DISTINCT tcau.competency_id) as count_competencies
        FROM {totara_competency_assignment_users} tcau
        LEFT JOIN {totara_competency_achievement} tca ON tcau.competency_id = tca.competency_id AND tcau.user_id = tca.user_id
        INNER JOIN {comp} c ON tcau.competency_id = c.id
        WHERE tcau.user_id = :userid AND tca.status = :active_status";

        $params = [
            'userid' => $USER->id,
            'active_status' => competency_achievement::ACTIVE_ASSIGNMENT
        ];

        $competency_data = $DB->get_record_sql($competency_sql, $params);

        if ($competency_data) {
            $count_competencies_assigned = $competency_data->count_competencies;
            $count_competencies_completed = $competency_data->count_completedcompetencies;
            $count_competencies_notcompleted = $count_competencies_assigned - $count_competencies_completed;
        } else {
            $count_competencies_completed = 0;
            $count_competencies_notcompleted = 0;
        }

        if($count_competencies_completed == 0 && $count_competencies_notcompleted == 0) {
            return false;
        }
        // Para este ejemplo, usamos datos de prueba:
        $competencies = [
            'accredited'    => $count_competencies_completed,
            'notAccredited' => $count_competencies_notcompleted
        ];
        
        // ----- Datos de cursos -----       
        // require_once($CFG->dirroot . '/completion/completion_completion.php');
        $sql_timeend_field = '(SELECT CASE WHEN ue.timeend = 0 THEN 9999999999 ELSE ue.timeend END AS timeend
                FROM {enrol} e JOIN {user_enrolments} ue ON ue.enrolid = e.id 
                WHERE ue.userid = coursec.userid AND e.courseid = coursec.course 
                ORDER BY CASE WHEN ue.timeend = 0 THEN 9999999999 ELSE ue.timeend END DESC
                LIMIT 1)';
        $now = time();

        $courses_sql = "SELECT 
                        COUNT(*) AS countcourses,
                        SUM(CASE 
                            WHEN coursec.status = ".COMPLETION_STATUS_COMPLETE." OR coursec.status = ".COMPLETION_STATUS_COMPLETEVIARPL." THEN 1 ELSE 0
                            END) AS countcompleted,
                        SUM(CASE WHEN coursec.status = ". COMPLETION_STATUS_COMPLETE. " || coursec.status = ".COMPLETION_STATUS_COMPLETEVIARPL. " THEN 0
                            WHEN $now < $sql_timeend_field OR ($sql_timeend_field IS NULL AND coursec.status = ".COMPLETION_STATUS_NOTYETSTARTED.") THEN 1
                            END) AS countwithountinfo            
                        FROM {course} course
                        JOIN {comp_criteria} cc
                            ON cc.itemtype = 'coursecompletion' AND cc.linktype = 1 AND course.id = cc.iteminstance
                        JOIN {totara_competency_assignment_users} tcau ON tcau.competency_id = cc.competencyid
                        LEFT JOIN {course_completions} coursec ON course.id = coursec.course AND tcau.user_id = coursec.userid
                        WHERE tcau.user_id = :userid";
        
        $params = [
            'userid' => $USER->id,
        ];

        $courses_data = $DB->get_record_sql($courses_sql, $params);
        
        // Datos de prueba para el gráfico de cursos:
        $courses = [
            'failedByAttendance' => $courses_data->countcourses - ($courses_data->countwithountinfo + $courses_data->countcompleted),
            'approved'           => $courses_data->countcompleted,
            'noInformation'      => $courses_data->countwithountinfo
        ];
        
        return [
            'competencies' => $competencies,
            'courses'      => $courses
        ];
    }

    public function get_user_competencies_accreditation()
    {
        global $DB, $USER;

        $params = array('userid' => $USER->id);
        $sql = "SELECT 
                c.shortname AS competency_name,
                cpl.proficiency,
                cpl.grade,
                (cpl.proficiency / cpl.grade) * 100 AS completion_percentage,
                CASE 
                     WHEN cpl.proficiency >= cpl.grade THEN 'Acreditado'
                     ELSE 'Sin acreditar'
                END AS accreditation_status
            FROM {competency_usercomp} cu
            JOIN {competency} c ON cu.competencyid = c.id
            JOIN {competency_usercomp_plan} cpl ON cpl.usercompid = cu.id
            WHERE cu.userid =  :userid;";
        $competencies = $DB->get_records_sql($sql, $params);
        $competencies = $DB->get_records_sql($sql, $params);

        // Separar en dos arrays según el estado de acreditación
        $accredited = [];
        $notAccredited = [];
        foreach ($competencies as $record) {
            if ($record->accreditation_status == 'Acreditado') {
                $accredited[] = $record;
            } else {
                $notAccredited[] = $record;
            }
        }

        // Retornar los datos en el formato deseado
        return [
            'accredited' => $accredited,
            'notAccredited' => $notAccredited,
        ];
    }

}
