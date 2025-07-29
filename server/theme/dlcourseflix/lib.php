<?php
defined('MOODLE_INTERNAL') || die();

function theme_dlcourseflix_extend_navigation_category_settings($navigation, $context) {
    global $PAGE, $CFG, $DB;

    if (empty($CFG->tenantsenabled)) {
        return null;
    }

    if (!$context->tenantid) {
        return null;
    }

    if (!($context instanceof context_coursecat)) {
        return;
    }

    if ($PAGE->theme->name !== 'dlcourseflix') {
        return;
    }

    $tenant = $DB->get_record('tenant', ['categoryid' => $context->instanceid]);
    if (!$tenant) {
        return null;
    }

    // Leave when user does not have the right capabilities.
    $categorycontext = context_coursecat::instance($tenant->categoryid);
    if (!has_capability('totara/tui:themesettings', $categorycontext)) {
        return null;
    }

    $url = new moodle_url('/totara/tui/theme_settings.php',
        [
            'theme_name' => 'dlcourseflix',
            'tenant_id' => $tenant->id,
        ]
    );
    $node = navigation_node::create(
        get_string('pluginname', 'theme_dlcourseflix'),
        $url,
        navigation_node::NODETYPE_LEAF,
        null,
        'dlcourseflix_editor',
        new pix_icon('i/settings', '')
    );

    $appearance = $navigation->find('category_appearance', navigation_node::TYPE_CONTAINER);
    if (!$appearance) {
        $appearance = $navigation->add(
            get_string('appearance', 'admin'),
            null,
            navigation_node::TYPE_CONTAINER,
            null,
            'category_appearance'
        );
    }
    $appearance->add_node($node);

    if ($PAGE->url->compare($url, URL_MATCH_EXACT)) {
        $appearance->force_open();
        $node->make_active();
    }
}

/**
 * Serves any files associated with the theme settings.
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options
 * @return bool
 */
function theme_dlcourseflix_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    $fs = get_file_storage();
    if ($context->contextlevel == CONTEXT_SYSTEM && ($filearea === 'footerlogo') && ($file = $fs->get_file($context->id, 'theme_dlcourseflix', $filearea, $args[0], '/', $args[1]))) {
        send_stored_file($file, 0, 0, false);
        return true;
    }
    send_file_not_found();
}

// /**
//  * This adding a new item to course settings
//  *
//  * @param navigation_node $navigation The navigation node to extend
//  * @param stdClass        $course The course object
//  * @param context         $context The course context
//  */
// function theme_dlcourseflix_extend_navigation_course($navigation, $course, $context) {
//     if(has_capability('moodle/course:update', $context)) { // If have the capability, then add the item
//         global $USER;
//         $url = new moodle_url('/local/notifications/rules.php', ['courseid' => $course->id]);
//         $pix = new pix_icon('i/scales', '');
//         $navigation->add('Test url', $url, navigation_node::TYPE_CUSTOM, null, null, $pix);
//     }
// }
