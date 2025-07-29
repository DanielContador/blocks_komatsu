<?php
defined('MOODLE_INTERNAL') || die();

/**
 * This adds a new item to course settings.
 *
 * @param navigation_node $navigation The navigation node to extend
 * @param stdClass        $course The course object
 * @param context         $context The course context
 */
function local_dl_selfenrol_info_extend_navigation_course($navigation, $course, $context) {
    if (has_capability('moodle/course:update', $context)) { // If have the capability, then add the item
        $url = new moodle_url('/local/dl_selfenrol_info/add.php', ['courseid' => $course->id]);
        $pix = new pix_icon('i/settings', '');
        $navigation->add(get_string('selfenrolinfo', 'local_dl_selfenrol_info'), $url, navigation_node::TYPE_CUSTOM, null, null, $pix);
    }
}
