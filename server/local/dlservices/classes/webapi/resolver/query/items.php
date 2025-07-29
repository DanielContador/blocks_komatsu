<?php

namespace local_dlservices\webapi\resolver\query;

require_once($CFG->dirroot . '/course/lib.php');

use core\webapi\execution_context;
use core\webapi\query_resolver;

class items extends query_resolver {
    /**
     * @inheritDoc
     * @throws \coding_exception
     */
    public static function resolve(array $args, execution_context $ec) {
        global $CFG, $DB;

        $spage = $args['spage'];
        if (empty($spage)) {
            $spage = 0;
        }

        $cat = $args['category'];
        if (empty($cat)) {
            $cat = 1;
        }

        $searchTerm = $args['searchTerm'];
        if (empty($searchTerm)) {
            $searchTerm = '';
        }

        $items = self::get_all_courses($searchTerm, $spage);

        // Fetch more views rank
        $moreviews_rank = self::get_moreviews_rank();

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
            $item->top = isset($moreviews_rank[$item->id]) ? $moreviews_rank[$item->id]->order_score : 0;
            $item->isNew = (time() - $item->timecreated) < (30 * 24 * 60 * 60);
            $item->itemType = 'course';

            $learningitems[] = $item;
        }

        return ['items' => $learningitems];
    }

    public static function get_all_courses($searchTerm = '', $limitfrom = 0, $get_count = false): array {
        global $DB, $CFG;

        #TODO: agregar filtro de categorias como variable de entrada
        $instance = new self();
        $categories = $instance->getAllCategories();
        $count = 0;
        $courses = [];

        $searchsql = '';
        $searchparams = [];
        if (!empty($searchTerm)) {
            $searchsql = "AND LOWER(c.fullname) LIKE LOWER(:search)";
            $searchparams = ['search' => "%$searchTerm%"];
        }

        foreach ($categories as $categoryId) {

            $params = ['categoryid'   => intval($categoryId),
                       'enrol_status' => ENROL_INSTANCE_ENABLED];

            $params = array_merge($params, $searchparams);

            $fields = ['id', 'shortname', 'fullname', 'startdate', 'enddate', 'timecreated'];

            $coursefields = 'c.' . join(',c.', $fields);

            // agregar filtro de categorias
            $sql = "SELECT DISTINCT $coursefields, 
                        ccats.name as category
                        FROM {course} c
                        JOIN {context} ctx ON ctx.instanceid = c.id AND ctx.contextlevel = " . CONTEXT_COURSE . "
                        JOIN {course_categories} ccats ON c.category = ccats.id
                        LEFT JOIN {enrol} e ON e.courseid = c.id
                        WHERE c.visible = 1 
                            AND ccats.visible = 1 
                            AND ccats.id = :categoryid
                            AND e.enrol = 'self' 
                            AND e.status = :enrol_status
                            AND ccats.issystem = 0
                            {$searchsql}
                        GROUP BY $coursefields 
                        ORDER BY c.timecreated DESC";

            if ($count == 0) {
                $courses = $DB->get_records_sql($sql, $params, $limitfrom, $CFG->historical_items_limitnum);
            } else {
                $courses = array_merge($courses, $DB->get_records_sql($sql, $params, $limitfrom, $CFG->historical_items_limitnum));
            }
            $count++;
        }
        return $courses;
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

        $course_with_most_enrollments = $DB->get_record_sql($course_with_most_enrollments_sql);
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

        $cache = \cache::make('local_dlservices', 'moreviewcourses');
        $courses = $cache->get('moreviewsrank');

        if (!$courses) {
            // Obtener los cursos desde la base de datos
            $courses = $DB->get_records_sql($sql, $params);

            // Guardar en caché por 24 horas (86400 segundos)
            $cache->set('moreviewsrank', $courses, 86400);
        }

        return $courses;
    }

    public function getAllCategories(): array {
        global $DB, $CFG;

        $fields = ['id'];

        $catfields = 'c.' . join(',c.', $fields);
        $sql = "SELECT DISTINCT $catfields 
                    FROM {course_categories} c
                    WHERE c.visible = 1 AND c.issystem = 0
                    ORDER BY c.name ASC";

        $categories = $DB->get_records_sql($sql, null, 0, $CFG->historical_items_limitnum);

        return array_keys($categories);

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
