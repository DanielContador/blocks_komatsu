<?php

namespace local_dlservices\webapi\resolver\query;

require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->libdir . '/enrollib.php');

use core\webapi\execution_context;
use core\webapi\query_resolver;

class moreviewsitems extends query_resolver {
    /**
     * @inheritDoc
     * @throws \coding_exception
     */
    public static function resolve(array $args, execution_context $ec) {

        global $CFG, $DB;

        $limit = $args['limit'];
        if (empty($limit)) {
            $limit = 3;
        }

        $recommended = $args['recommended'];
        if (empty($recommended)) {
            $recommended = false;
        }

        $limitfrom = $limit * $CFG->historical_items_limitnum;

        if ($recommended) {
            $items = self::get_recommended_moreviewcourses($limit, $limitfrom);
        } else {
            $items = self::get_all_moreviewcourses($limit, $limitfrom, $get_count = false);
        }

        $learningitems = [];
        foreach ($items as $item) {
            $item->link = $CFG->wwwroot . '/course/view.php?id=' . $item->id;
            $item->imageUrl = course_get_image($item->id);
            $instance = new self();
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

    public static function get_all_moreviewcourses($limit, $limitfrom = 0): array {
        global $DB, $CFG, $USER;

        // Definir los campos que se van a seleccionar
        $fields = ['id', 'fullname', 'shortname', 'startdate', 'enddate'];

        $coursefields = 'c.' . join(', c.', $fields); // Asegúrate de que haya un espacio después de la coma

        // Parámetros para la consulta SQL
        $limitvalue = (int) $limit;

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
        $params = [
            'enrol_status'      => ENROL_INSTANCE_ENABLED,
            'count_enrollments' => $count_enrollments,
            'year_in_seconds'   => $year_in_seconds];
        $sql = "SELECT DISTINCT $coursefields, ccats.name as category,
                    COUNT(ue.userid) AS total_users,
                    (
                        COUNT(ue.userid) / :count_enrollments
                    ) AS score
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
                    ORDER BY score DESC
                    LIMIT $limitvalue
                    ";

        // Obtener los cursos desde la base de datos
        //$courses = $DB->get_records_sql($sql, $params,$limitfrom, $CFG->historical_items_limitnum);
        // Intentar obtener de la caché
        //$cache = \cache::make('local_dlservices', 'moreviewcourses');
        $courses = $cache->get('moreviewcourses');

        if (!$courses) {
            // Obtener los cursos desde la base de datos
            $courses = $DB->get_records_sql($sql, $params, $limitfrom, $CFG->historical_items_limitnum);

            // Guardar en caché por 24 horas (86400 segundos)
            $cache->set('moreviewcourses', $courses, 86400);
        }

        return $courses;
    }

    public static function get_recommended_moreviewcourses($limit, $limitfrom = 0): array {
        global $DB, $CFG, $USER;

        // Definir los campos que se van a seleccionar
        $fields = ['id', 'fullname', 'shortname', 'startdate', 'enddate'];

        $coursefields = 'c.' . join(', c.', $fields); // Asegúrate de que haya un espacio después de la coma

        // Parámetros para la consulta SQL
        $limitvalue = (int) $limit;

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

        $course_with_most_enrollments = $DB->get_record_sql($course_with_most_enrollments_sql);
        $count_enrollments = $course_with_most_enrollments ? $course_with_most_enrollments->total_users : 1;

        // Get category with most enrollments for User
        $category_with_most_enrollments_sql = "SELECT ccats.id AS category_id, COUNT(ue.userid) AS total_users
                                                FROM {course_categories} ccats
                                                JOIN {course} c ON c.category = ccats.id
                                                JOIN {enrol} e ON e.courseid = c.id
                                                JOIN {user_enrolments} ue ON ue.enrolid = e.id
                                                WHERE c.visible = 1 AND ue.userid = :userid AND ccats.issystem = 0
                                                GROUP BY ccats.id
                                                ORDER BY total_users DESC
                                                LIMIT 1";
        $category_with_most_enrollments = $DB->get_record_sql($category_with_most_enrollments_sql, ['userid' => $USER->id]);
        $count_cat_enrollments = $category_with_most_enrollments ? $category_with_most_enrollments->total_users : 1;

        $year_in_seconds = 86400 * 365;

        // Consulta SQL corregida
        $params = ['limitvalue'            => $limitvalue,
                   'enrol_status'          => ENROL_INSTANCE_ENABLED,
                   'count_enrollments'     => $count_enrollments,
                   'count_cat_enrollments' => $count_cat_enrollments,
                   'current_user_id'       => $USER->id,
                   'year_in_seconds'       => $year_in_seconds];
        $sql = "SELECT DISTINCT $coursefields, ccats.name as category,
                    COUNT(ue.userid) AS total_users,
                    (   0.4 * COUNT(ue.userid) / :count_enrollments + 
                        0.6 * COALESCE((
                            SELECT COUNT(ue2.userid)
                            FROM {course} c2
                            JOIN {context} ctx2 ON ctx2.instanceid = c2.id AND ctx2.contextlevel = " . CONTEXT_COURSE . "
                            LEFT JOIN {enrol} e2 ON e2.courseid = c2.id
                            LEFT JOIN {user_enrolments} ue2 ON ue2.enrolid = e2.id
                            WHERE c2.category = ccats.id 
                              AND ue2.userid = :current_user_id
                            GROUP BY c2.category
                            HAVING COUNT(ue2.userid) > 0
                        ), 0) / :count_cat_enrollments
                    ) AS score
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
                    ORDER BY score DESC, c.timecreated
                    LIMIT $limitvalue
                    ";

        // Obtener los cursos desde la base de datos
        //$courses = $DB->get_records_sql($sql, $params, $limitfrom, $CFG->historical_items_limitnum);

        $userId = $USER->id;
        $cacheName = "recommendedcourses . $userId";

        $cache = \cache::make('local_dlservices', 'moreviewcourses');
        $courses = $cache->get($cacheName);

        if (!$courses) {
            // Obtener los cursos desde la base de datos
            $courses = $DB->get_records_sql($sql, $params, $limitfrom, $CFG->historical_items_limitnum);

            // Guardar en caché por 24 horas (86400 segundos)
            $cache->set($cacheName, $courses, 86400);
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
