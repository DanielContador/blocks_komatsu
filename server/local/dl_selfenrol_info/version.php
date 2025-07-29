<?php

defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2024020301;       // The current module version (Date: YYYYMMDDXX)
$plugin->requires  = 2017111309;    // Requires this Moodle version
$plugin->component = 'local_dl_selfenrol_info';   // Full name of the plugin (used for diagnostics)
$plugin->dependencies = array(
    'theme_dlcourseflix' => 2024112501, // This plugin depends on theme_dlcourseflix
);
