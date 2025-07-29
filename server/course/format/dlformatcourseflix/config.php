<?php
defined('MOODLE_INTERNAL') || die();

$plugin->component = 'format_dlformatcourseflix'; // Nombre del componente del plugin.
$plugin->version   = 2025021300;        // The current plugin version (Date: YYYYMMDDXX).
$plugin->requires  = 2017110800;        // Requires this Moodle version.

// Añade la configuración del renderer personalizado.
$THEME->rendererfactory = 'theme_overridden_renderer_factory';
