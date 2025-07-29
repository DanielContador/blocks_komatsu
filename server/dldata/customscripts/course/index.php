<?php
global $OUTPUT;
defined('MOODLE_INTERNAL') || die();
require_once(__DIR__ . '/../../../../config.php');

use theme_dlcourseflix\controllers\course_index;

$viewtype = optional_param('viewtype', 'course', PARAM_TEXT);
$categoryid = optional_param('categoryid', 0, PARAM_INT);

if ($CFG->forcelogin) {
    require_login();
}

$PAGE->set_context(\context_system::instance());

$PAGE->set_url('/course/index.php', array('viewtype' => $viewtype, 'categoryid' => $categoryid));

$PAGE->set_pagelayout('coursecategory');
// $PAGE->set_pagetype('course-index');
$pagetitle = get_string('mycourses', 'theme_dlcourseflix'); // Asegurarse de que la cadena de texto esté en el idioma correcto

// $PAGE->navbar->remove(-1);
$PAGE->navbar->add($pagetitle);
$PAGE->set_title($pagetitle);
$PAGE->set_heading($SITE->fullname);

echo $OUTPUT->header();
echo $OUTPUT->footer();

//(new course_index())->process();
exit;