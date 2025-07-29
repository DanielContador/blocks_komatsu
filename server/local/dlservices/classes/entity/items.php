<?php
namespace local_dlservices\classes\entity;
use core_course\user_learning\item;

require_once( $CFG->dirroot.'/local/dlgraphqlservices/config.php');

class items extends item
{
    public static function get_all_courses($limitfrom = 0, $get_count=false): array
    {
        global $DB, $CFG;

        #TODO: agregar filtro de categorias como variable de entrada
        $categoryId = 5;

        $params = array('categoryid' => $categoryId);

        $fields = ['id',
            'shortname', 'fullname',
            'summary', 'category'
        ];

        $coursefields = 'c.' .join(',c.', $fields);

        // agregar filtro de categorias
        $sql = "SELECT DISTINCT $coursefields 
                    FROM {course} c
                    JOIN {context} ctx ON ctx.instanceid = c.id AND ctx.contextlevel = " . CONTEXT_COURSE . "
                    JOIN {course_categories} ccats ON c.category = ccats.id
                    WHERE c.visible = 1 AND ccats.visible = 1 AND ccats.id = :categoryid 
                    ORDER BY c.fullname DESC";

        // $params = array_merge($params, $all_programs_params);
        $courses = $DB->get_records_sql($sql, $params, $limitfrom, $CFG->historical_items_limitnum);

        return $courses;
    }
}