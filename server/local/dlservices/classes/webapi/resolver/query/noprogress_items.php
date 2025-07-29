<?php

namespace local_dlservices\webapi\resolver\query;

use COM;
use core\webapi\execution_context;
use core\webapi\query_resolver;
use core\entity\user;
use local_dlservices\webapi\resolver\query\items as courses;

class noprogress_items extends query_resolver {
    /**
     * @inheritDoc
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function resolve(array $args, execution_context $ec) {

        global $USER, $DB, $CFG, $PAGE;

        $spage = $args['spage'];
        if (empty($spage)) {
            $spage = 0;
        }

        $limitfrom = $spage * $CFG->historical_items_limitnum;

        $items = self::get_all_coursesbyuser($USER->id, $limitfrom);
        $moreViews = courses::get_moreviews_rank();
        $learningitems = [];
        foreach ($items as $item) {
            if (!$status = $DB->get_field('course_completions', 'status', ['userid' => $USER->id, 'course' => $item->id])) {
                $status = null;
            }
            if ($status != COMPLETION_STATUS_NOTYETSTARTED && $status != COMPLETION_STATUS_INPROGRESS) {
                continue;
            }

            $renderer = $PAGE->get_renderer('totara_core');
            $hideifnotactive = false;
            $content = $renderer->export_course_progress_for_template($USER->id, $item->id, $status, $hideifnotactive);
            $course_percentage = $content->percent;

            if ($course_percentage == null || is_nan($course_percentage)) {
                $course_percentage = 0;
            }

            if ($status == COMPLETION_STATUS_INPROGRESS && $course_percentage > 0) {
                continue;
            }

            $item->progress = $course_percentage;
            $item->link = $CFG->wwwroot . '/course/view.php?id=' . $item->id;
            $item->imageUrl = course_get_image($item->id);
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
            $item->gifImage = course_get_image($item->id);
            $gifCourse = $DB->get_record('course_info_field', ['shortname' => 'gifcourse']);
            if ($gifCourse) {
                $getGif = $DB->get_record('course_info_data',
                                          ['fieldid'  => $gifCourse->id ?? null,
                                           'courseid' => $item->id]);
                if ($getGif) {
                    $fs = get_file_storage();
                    $sqlFile = "SELECT * FROM {files}
                                WHERE contextid = 1
                                AND component = 'totara_customfield'
                                AND filearea = 'course_filemgr'
                                AND itemid = :itemid
                                AND source IS NOT NULL
                                LIMIT 1";
                    $params = array('itemid' => $getGif->data);
                    $sqlFile = $DB->get_record_sql($sqlFile, $params);
                    $file = $fs->get_file_by_hash($sqlFile->pathnamehash);
                    if ($file) {
                        $url = \moodle_url::make_pluginfile_url(
                            $file->get_contextid(),
                            $file->get_component(),
                            $file->get_filearea(),
                            $file->get_itemid(),
                            $file->get_filepath(),
                            $file->get_filename()
                        );
                        $item->gifImage = $url;
                    }
                }
            }
            $item->top = isset($moreViews[$item->id]) ? $moreViews[$item->id]->order_score : 0;
            $item->recent = (time() - $item->startdate ?? 0) < 2592000;

            $learningitems[] = $item;

        }

        return ['items' => $learningitems];
    }

    public static function get_all_coursesbyuser($userid, $limitfrom = 0, $get_count = false): array {
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

        // Parámetros para la consulta
        $params = ['userid' => $userid, 'site_id' => SITEID];
        $params['container_type'] = \container_course\course::get_type();
        $params = array_merge($params, $visibilityparams);

        // Campos a recuperar
        $fields = ['id', 'fullname', 'shortname', 'startdate', 'enddate'];
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
            {$visibilitysql}
            ORDER BY c.timecreated";

        // Obtener los cursos
        $coursesbyuser = $DB->get_records_sql($sql, $params, $limitfrom, $CFG->historical_items_limitnum);
        return $coursesbyuser;
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
