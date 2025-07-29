<?php


defined('MOODLE_INTERNAL') || die();

/** @var core_config $CFG */
/** @var core\record\tenant $tenant */
/** @var context_coursecat $categorycontext */

$settings = new admin_externalpage(
    'dlcourseflix_editor',
    new lang_string('pluginname','theme_dlcourseflix'),
    "$CFG->wwwroot/totara/tui/theme_settings.php?theme_name=dlcourseflix&tenant_id=$tenant->id",
    'totara/tui:themesettings',
    false,
    $categorycontext
);