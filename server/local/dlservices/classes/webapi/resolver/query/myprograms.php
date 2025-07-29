<?php

namespace local_dlservices\webapi\resolver\query;

require_once($CFG->dirroot . '/totara/program/program.class.php');

use core\webapi\execution_context;
use core\webapi\query_resolver;
use LightSaml\Model\Protocol\Status;

class myprograms extends query_resolver {
    /**
     * @inheritDoc
     * @throws \coding_exception
     */
    public static function resolve(array $args, execution_context $ec) {
        global $USER, $CFG, $OUTPUT;

        $spage = isset($args['spage']) ? $args['spage'] : 0;
        $limit = $args['limit'];
        if (empty($limit)) {
            $limit = 0;
        }

        $searchTerm = $args['searchTerm'];
        if (empty($searchTerm)) {
            $searchTerm = '';
        }

        $completedprograms = $args['completedprograms'];

        $limitfrom = $spage * $limit;

        $items = self::get_all_programsbyuser($USER->id, $searchTerm, $limitfrom, $limit, false, $completedprograms);

        $learningitems = [];
        foreach ($items as $item) {
            $item->link = $CFG->wwwroot . '/totara/program/view.php?id=' . $item->id;
            $program_instance = new \program($item->id);
            $imageprogram = $program_instance->get_image();
            $item->imageUrl = $imageprogram;
            $item->summary = strip_tags($item->summary);
            $item->duration = '';
            $item->isNew = (time() - $item->timecreated) < (30 * 24 * 60 * 60);
            $item->itemType = 'program';
            //Get percentage of program completion
            $program_percentage = round(totara_program_get_user_percentage_complete($item->id, $USER->id));

            if ($program_percentage == null || is_nan($program_percentage)) {
                $program_percentage = 0;
            }
            $item->progress = $program_percentage;
            // Program status
            if ($item->status == STATUS_PROGRAM_INCOMPLETE) {
                $item->status = get_string('incomplete', 'totara_program');
            } else if ($item->status == STATUS_PROGRAM_COMPLETE) {
                $item->status = get_string('complete', 'totara_program');
            } else {
                $item->status = get_string('error:invalidstatus', 'totara_program');
            }

            $learningitems[] = $item;
        }

        return ['programs' => $learningitems];
    }

    public static function get_all_programsbyuser($userid, $searchTerm = '', $limitfrom = 0, $limitnum, $get_count = false,
        $completedprograms = false): array {
        global $DB, $CFG;

        $params = ['userid' => $userid];
        $fields = ['id', 'fullname', 'shortname', 'category', 'summary', 'timecreated'];
        $programfields = 'p.' . join(',p.', $fields);

        //Mostrar o no programas completos
        if ($completedprograms) {
            $wherePC = "";
        } else {
            $wherePC = " AND pc.status <> " . STATUS_PROGRAM_COMPLETE;
        }

        $searchsql = '';
        $searchparams = [];
        if (!empty($searchTerm)) {
            $searchsql = "AND LOWER(p.fullname) LIKE LOWER(:search)";
            $searchparams = ['search' => "%$searchTerm%"];
        }
        $params = array_merge($params, $searchparams);

        $sql = "SELECT DISTINCT $programfields, ccats.name as category, pc.status as status
                FROM {prog_user_assignment} upe
                JOIN {prog} p ON p.id = upe.programid
                JOIN {prog_completion} pc ON pc.programid = p.id AND pc.userid = upe.userid
                JOIN {course_categories} ccats ON p.category = ccats.id
                WHERE upe.userid = :userid
                    AND p.visible = 1
                    AND p.certifid IS NULL 
                    AND upe.exceptionstatus != 2
                    AND pc.coursesetid = 0
                    $wherePC
                    {$searchsql}
                ORDER BY p.fullname";

        $programsbyuser = $DB->get_records_sql($sql, $params, $limitfrom, $limitnum);

        return $programsbyuser;
    }
}
