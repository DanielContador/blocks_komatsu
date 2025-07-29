<?php
defined('MOODLE_INTERNAL') || die();

$capabilities = array(
    'theme/dlcourseflix:use' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'user' => CAP_ALLOW,
        ),
    ),
);
