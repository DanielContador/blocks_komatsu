<?php

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configcheckbox(
        'block_dlmylearning/showexpiringcourses',
        get_string('showexpiringcourses', 'block_dlmylearning'),
        get_string('showexpiringcoursesdesc', 'block_dlmylearning'),
        1
    ));
    $settings->add(new admin_setting_configcheckbox(
        'block_dlmylearning/showcourses',
        get_string('showcourses', 'block_dlmylearning'),
        get_string('showcoursesdesc', 'block_dlmylearning'),
        1
    ));
    $settings->add(new admin_setting_configcheckbox(
        'block_dlmylearning/showprograms',
        get_string('showprograms', 'block_dlmylearning'),
        get_string('showprogramsdesc', 'block_dlmylearning') ,
        1
    ));
}