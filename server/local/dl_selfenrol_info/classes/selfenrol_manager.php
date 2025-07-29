<?php
namespace local_dl_selfenrol_info;

defined('MOODLE_INTERNAL') || die();

class selfenrol_manager {
    /**
     * Get the 'whatlearn' data for a given course.
     *
     * @param int $courseid
     * @return string|null
     */
    public static function get_whatlearn_data($courseid) {
        global $DB;
        $record = $DB->get_record('dl_selfenrol_info_data', ['courseid' => $courseid, 'shortname' => 'whatlearn']);
        return $record ? $record->data : null;
    }

    /**
     * Get the 'content' data for a given course.
     *
     * @param int $courseid
     * @return array|null
     */
    public static function get_content_data($courseid) {
        global $DB;
        $record = $DB->get_record('dl_selfenrol_info_data', ['courseid' => $courseid, 'shortname' => 'content']);
        return $record ? json_decode($record->data, true) : null;
    }

    /**
     * Save or update the 'whatlearn' data for a given course.
     *
     * @param int $courseid
     * @param string $data
     */
    public static function save_whatlearn_data($courseid, $data) {
        global $DB;
        $record = $DB->get_record('dl_selfenrol_info_data', ['courseid' => $courseid, 'shortname' => 'whatlearn']);
        if ($record) {
            $record->data = $data;
            $DB->update_record('dl_selfenrol_info_data', $record);
        } else {
            $record = new \stdClass();
            $record->shortname = 'whatlearn';
            $record->fieldtype = FIELD_TYPE_TEXT;
            $record->courseid = $courseid;
            $record->data = $data;
            $DB->insert_record('dl_selfenrol_info_data', $record);
        }
    }

    /**
     * Save or update the 'content' data for a given course.
     *
     * @param int $courseid
     * @param array $data
     */
    public static function save_content_data($courseid, $data) {
        global $DB;
        $record = $DB->get_record('dl_selfenrol_info_data', ['courseid' => $courseid, 'shortname' => 'content']);
        if ($record) {
            $record->data = json_encode($data);
            $DB->update_record('dl_selfenrol_info_data', $record);
        } else {
            $record = new \stdClass();
            $record->shortname = 'content';
            $record->fieldtype = FIELD_TYPE_MULTIPLE_TEXT;
            $record->courseid = $courseid;
            $record->data = json_encode($data);
            $DB->insert_record('dl_selfenrol_info_data', $record);
        }
    }
}
