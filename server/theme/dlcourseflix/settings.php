<?php

defined('MOODLE_INTERNAL') || die();

$settings = new admin_externalpage(
    'dlcourseflix_editor',
    get_string('pluginname', 'theme_dlcourseflix'),
    $CFG->wwwroot . '/theme/dlcourseflix/index.php',
    'totara/tui:themesettings'
);

if ($hassiteconfig) { 

    $ADMIN->add('appearance', new admin_externalpage('theme_dlcourseflix/aditionalsettings',
                get_string('dlcourseflixthemeextrasettings', 'theme_dlcourseflix'), "$CFG->wwwroot/theme/dlcourseflix/extrasettings.php", array('moodle/site:config')));

}