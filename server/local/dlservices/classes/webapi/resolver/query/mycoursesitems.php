<?php

namespace local_dlservices\webapi\resolver\query;

require_once($CFG->dirroot . '/completion/completion_completion.php');

use core\webapi\execution_context;
use core\webapi\query_resolver;
use core\entity\user;
use local_dlservices\classes\entity\items as courses;
use PHPUnit\Exception;

class mycoursesitems extends query_resolver {
    /**
     * @inheritDoc
     * @throws \coding_exception
     */
    public static function resolve(array $args, execution_context $ec) {

        global $USER, $DB, $CFG, $PAGE, $COMPLETION_STATUS;

        $spage = $args['spage'];
        if (empty($spage)) {
            $spage = 0;
        }
        $limit = $args['limit'];
        if (empty($limit)) {
            $limit = 0;
        }
        $searchTerm = $args['searchTerm'];
        if (empty($searchTerm)) {
            $searchTerm = '';
        }

        $programscourses = $args['programscourses'];

        $limitfrom = $spage * $limit;

        // $searchTerm = "Cer";

        $items = self::get_all_coursesbyuser($USER->id, $searchTerm, $limitfrom, $limit, false, $programscourses);
        $learningitems = [];
        foreach ($items as $item) {
            if (!$status = $DB->get_field('course_completions', 'status', ['userid' => $USER->id, 'course' => $item->id])) {
                $status = null;
            }

            $renderer = $PAGE->get_renderer('totara_core');
            $hideifnotactive = false;
            $content = $renderer->export_course_progress_for_template($USER->id, $item->id, $status, $hideifnotactive);
            $course_percentage = $content->percent;

            if ($course_percentage == null || is_nan($course_percentage)) {
                $course_percentage = 0;
            }

            $string = $COMPLETION_STATUS[COMPLETION_STATUS_NOTYETSTARTED];
            if (array_key_exists((int) $status, $COMPLETION_STATUS)) {
                $string_aux = $COMPLETION_STATUS[(int) $status];
                if (!empty($string_aux)) {
                    $string = $string_aux;
                }
            }

            // Fetch more views rank
            $moreviews_rank = self::get_moreviews_rank();

            $item->status = get_string($string, 'completion');
            $item->progress = $course_percentage;
            $item->link = $CFG->wwwroot . '/course/view.php?id=' . $item->id;
            $item->imageUrl = course_get_image($item->id);
            $item->summary = strip_tags($item->summary);
            $item->top = isset($moreviews_rank[$item->id]) ? $moreviews_rank[$item->id]->order_score : 0;
            $item->isNew = (time() - $item->timecreated) < (30 * 24 * 60 * 60);
            $item->itemType = 'course';

            // Calculate duration
            $item->duration = "No disponible";
            $field_duration = $DB->get_record('course_info_field', ['shortname' => 'duracion']);
            if ($field_duration) {
                $get_duration = $DB->get_record('course_info_data',
                                                ['fieldid'  => isset($field_duration->id) ? $field_duration->id : null,
                                                 'courseid' => $item->id]);
                if ($get_duration) {
                    $item->duration = $get_duration->data;
                }
            }

            $learningitems[] = $item;
        }

        return ['items' => $learningitems];
    }

    public static function get_all_coursesbyuser($userid, $searchTerm = '', $limitfrom = 0, $limitnum, $get_count = false,
        $programscourses = false): array {
        global $DB, $CFG;

        if (empty($CFG->disable_visibility_maps)) {
            $visibility = \totara_core\visibility_controller::course()->sql_where_visible($userid, 'c');
            $visibilitysql = '';
            $visibilityparams = [];
            if (!$visibility->is_empty()) {
                $visibilitysql = ' AND ' . $visibility->get_sql();
                $visibilityparams = $visibility->get_params();
            }
        } else {
            [$visibilitysql, $visibilityparams] = totara_visibility_where($userid, 'c.id', 'c.visible', 'c.audiencevisible');
            $visibilitysql = 'AND ' . $visibilitysql;
        }

        $searchsql = '';
        $searchparams = [];
        if (!empty($searchTerm)) {
            $searchsql = "AND LOWER(c.fullname) LIKE LOWER(:search)";
            $searchparams = ['search' => "%$searchTerm%"];
        }

        // Parámetros para la consulta
        $params = ['userid' => $userid, 'site_id' => SITEID];
        $params['container_type'] = \container_course\course::get_type();
        $params = array_merge($params, $visibilityparams);
        $params = array_merge($params, $searchparams);

        // Campos a recuperar
        $fields = ['id', 'fullname', 'shortname', 'summary', 'startdate', 'enddate', 'timecreated'];
        $coursefields = 'c.' . join(',c.', $fields);

        // Totara: enforce tenant restrictions.
        if (!$user_context = \context_user::instance($userid, IGNORE_MISSING)) {
            return ([]);
        }
        $tenant_join = "";
        if (!empty($CFG->tenantsenabled)) {
            if ($user_context->tenantid) {
                if ($CFG->tenantsisolated) {
                    $tenant_join = "JOIN {context} ctx ON ctx.instanceid = e.courseid AND ctx.contextlevel = " . CONTEXT_COURSE .
                                   " AND ctx.tenantid = " . $user_context->tenantid;
                } else {
                    $tenant_join = "JOIN {context} ctx ON ctx.instanceid = e.courseid AND ctx.contextlevel = " . CONTEXT_COURSE .
                                   " AND (ctx.tenantid IS NULL OR ctx.tenantid = " . $user_context->tenantid . ")";
                }
            }
        }
        // Consulta SQL para obtener todos los cursos inscritos
        $sql = "SELECT DISTINCT $coursefields, ccats.name as category
            FROM {enrol} e
            JOIN {user_enrolments} ue ON ue.enrolid = e.id
            $tenant_join
            JOIN {course} c ON c.id = e.courseid
            JOIN {context} ctx ON ctx.instanceid = c.id AND ctx.contextlevel = " . CONTEXT_COURSE . "
            JOIN {course_categories} ccats ON c.category = ccats.id
            WHERE ue.userid = :userid
            AND c.id <> :site_id 
            AND c.containertype = :container_type
            {$searchsql}
            {$visibilitysql}
            ORDER BY c.timecreated DESC";

        // Obtener los cursos
        $coursesbyuser = $DB->get_records_sql($sql, $params, $limitfrom, $limitnum);

        //Obtener el id de los cursos para evitar repetir
        $idList = '';
        if (!empty($coursesbyuser)) {
            foreach ($coursesbyuser as $item) {
                $idList .= $item->id . " ,";
            }
        }
        $idList = rtrim($idList, ',');

        //Obtener los cursos de programa
        if ($programscourses) {
            // Obtener todos los programas en los que el usuario está inscrito.
            $sql = "SELECT p.id
            FROM {prog} p
            JOIN {prog_completion} pc ON pc.programid = p.id
            WHERE pc.userid = :userid";
            $params = ['userid' => $userid];

            $programs = $DB->get_records_sql($sql, $params);

            try {
                foreach ($programs as $prog) {
                    $progId = $prog->id;
                    $sql = "SELECT DISTINCT $coursefields, 'De Programa' as category
                                    FROM {course} c 
                                    JOIN {course_categories} ccats ON c.category = ccats.id
                                    WHERE c.id IN 
                                                 (SELECT pcc.courseid 
                                                  FROM ttr_prog_courseset_course pcc
                                                  JOIN {prog_courseset} pc ON pc.id = pcc.coursesetid AND pc.programid = $progId)
                                    AND c.format <> 'singleactivity'
                                    AND c.id NOT IN ($idList)";
                    $coursesbyuser = array_merge($coursesbyuser, $DB->get_records_sql($sql));

                    //Actualizar los iDs con los nuevos cursos
                    $idList = '';
                    if (!empty($coursesbyuser)) {
                        foreach ($coursesbyuser as $item) {
                            $idList .= $item->id . " ,";
                        }
                        $idList = rtrim($idList, ',');
                    }
                }
            } catch (Exception $e) {

            }
        }

        return $coursesbyuser;
    }

    public static function get_moreviews_rank($count = 10): array {
        global $DB;

        // Definir los campos que se van a seleccionar
        $fields = ['id', 'fullname', 'shortname', 'startdate', 'enddate'];

        $coursefields = 'c.' . join(', c.', $fields); // Asegúrate de que haya un espacio después de la coma

        // Obtener el curso con más usuarios inscritos
        $course_with_most_enrollments_sql = "SELECT c.id, COUNT(ue.userid) AS total_users
                                             FROM {course} c
                                             INNER JOIN {enrol} e ON e.courseid = c.id
                                             INNER JOIN {user_enrolments} ue ON ue.enrolid = e.id
                                             WHERE c.visible = 1
                                             AND e.enrol = 'self'
                                             GROUP BY c.id
                                             ORDER BY total_users DESC
                                             LIMIT 1";

        //$course_with_most_enrollments = $DB->get_record_sql($course_with_most_enrollments_sql);

        $cache = \cache::make('local_dlservices', 'moreviewcourses');
        $course_with_most_enrollments = $cache->get('course_with_most_enrollments_sql');

        if (!$course_with_most_enrollments) {
            // Obtener los cursos desde la base de datos
            $course_with_most_enrollments = $DB->get_record_sql($course_with_most_enrollments_sql);

            // Guardar en caché por 24 horas (86400 segundos)
            $cache->set('course_with_most_enrollments_sql', $course_with_most_enrollments, 86400);
        }

        $count_enrollments = $course_with_most_enrollments ? $course_with_most_enrollments->total_users : 1;

        $year_in_seconds = 86400 * 365;

        // Consulta SQL corregida
        $params = ['enrol_status'      => ENROL_INSTANCE_ENABLED,
                   'count_enrollments' => $count_enrollments,
                   'year_in_seconds'   => $year_in_seconds];
        $sql = "SELECT DISTINCT $coursefields, ccats.name as category,
                    ROW_NUMBER() OVER (
                        ORDER BY 
                        (COUNT(ue.userid) / :count_enrollments) DESC
                    ) AS order_score
                    FROM {course} c
                    JOIN {course_categories} ccats ON c.category = ccats.id
                    JOIN {context} ctx ON ctx.instanceid = c.id AND ctx.contextlevel = " . CONTEXT_COURSE . "
                    LEFT JOIN {enrol} e ON e.courseid = c.id
                    LEFT JOIN {user_enrolments} ue ON ue.enrolid = e.id
                    WHERE c.visible = 1 
                        AND e.enrol = 'self' 
                        AND e.status = :enrol_status 
                        AND ccats.issystem = 0
                    GROUP BY $coursefields
                    LIMIT $count";

        // Obtener los cursos desde la base de datos
        //$courses = $DB->get_records_sql($sql, $params);

        //$cache = \cache::make('local_dlservices', 'moreviewcourses');
        $courses = $cache->get('moreviewsrank');

        if (!$courses) {
            // Obtener los cursos desde la base de datos
            $courses = $DB->get_records_sql($sql, $params);

            // Guardar en caché por 24 horas (86400 segundos)
            $cache->set('moreviewsrank', $courses, 86400);
        }

        return $courses;
    }

    public function formatDuration($startdate, $enddate): string {
        if (!$startdate || !$enddate) {
            return 'Duración no disponible';
        }

        $seconds = $enddate - $startdate;

        if ($seconds <= 0) {
            return 'Duración inválida';
        }

        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);

        $duration = '';

        if ($days > 0) {
            $duration .= $days . ($days == 1 ? ' día ' : ' días ');
        }
        if ($hours > 0) {
            $duration .= $hours . 'h ';
        }
        if ($minutes > 0) {
            $duration .= $minutes . 'm';
        }

        return trim($duration);
    }
}
