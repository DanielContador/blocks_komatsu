<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Renderer for outputting the topics course format.
 *
 * @package format_dlformatcourseflix
 * @copyright 2012 Dan Poltawski
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since Moodle 2.3
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/format/renderer.php');
require_once("{$CFG->libdir}/completionlib.php");
require_once($CFG->dirroot . '/totara/program/program_assignments.class.php');

/**
 * Basic renderer for topics format.
 *
 * @copyright 2012 Dan Poltawski
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class format_dlformatcourseflix_renderer extends format_section_renderer_base
{
    /** @var contains instance of core course renderer */
    protected $courserenderer;

    /**
     * Constructor method, calls the parent constructor
     *
     * @param moodle_page $page
     * @param string $target one of rendering target constants
     */
    public function __construct(moodle_page $page, $target)
    {
        parent::__construct($page, $target);

        // Since format_topics_renderer::section_edit_controls() only displays the 'Set current section' control when editing mode is on
        // we need to be sure that the link 'Turn editing mode on' is available for a user who does not have any other managing capability.
        $page->set_other_editing_capability('moodle/course:setcurrentsection');
        $this->courserenderer = $this->page->get_renderer('core', 'course');
    }

    /**
     * Generate the starting container html for a list of sections
     * @return string HTML to output.
     */
    protected function start_section_list()
    {
        
        global $COURSE, $DB, $USER, $PAGE;

        $imgcourse = course_get_image($COURSE);
        $fullname = $COURSE->fullname;

        $field_duration = $DB->get_record('course_info_field', array('shortname' => 'LessonDuration'));
        $get_duration = $DB->get_record('course_info_data', array('fieldid' => isset($field_duration->id) ? $field_duration->id : null, 'courseid' => $COURSE->id));

        $lesson = isset($get_duration->data) ? $get_duration->data : null;

        $enrols = $DB->get_records_sql("SELECT ue.*, e.enrol as typeenrol FROM {user_enrolments} ue
                                        INNER JOIN {enrol} e ON (ue.enrolid = e.id AND ue.userid = :userid)
                                        WHERE e.courseid = :courseid", ['userid' => $USER->id, 'courseid' => $COURSE->id]);
        foreach ($enrols as $enrol) {
            // $startdate_enrol = $enrol->timestart;
            // $enddate_enrol =  $enrol->timeend;

            if ($enrol->typeenrol == 'totara_program') {
                // Get program assigned
                $program_data = $DB->get_record_sql("SELECT p.*, pa.assignmenttype, pa.assignmenttypeid, pua.timeassigned FROM {prog} p
                                                INNER JOIN {prog_user_assignment} pua ON (p.id = pua.programid AND pua.userid = :userid)
                                                INNER JOIN {prog_assignment} pa ON pua.assignmentid = pa.id
                                                INNER JOIN {prog_courseset} pcs ON p.id = pcs.programid
                                                INNER JOIN {prog_courseset_course} pcs_course ON pcs.id = pcs_course.coursesetid
                                                WHERE pcs_course.courseid = :courseid 
                                                ORDER BY pua.timeassigned DESC", ['userid' => $USER->id, 'courseid' => $COURSE->id]);

                if (!empty($program_data)) {
                    $startdate_enrol = $program_data->timeassigned;
                    // if assignment type is by audience take audiece start date
                    if ($program_data->assignmenttype == ASSIGNTYPE_COHORT) {
                        $cohort = $DB->get_record('cohort', array('id' => $program_data->assignmenttypeid));
                        if (!empty($cohort) && $cohort->startdate > $program_data->timeassigned) {
                            $startdate_enrol = $cohort->startdate;
                        }
                    }
                }

                $prog_completion = prog_load_completion($program_data->id, $USER->id, false);
                if ($prog_completion->timedue > 0) {
                    $enddate_enrol = $prog_completion->timedue;
                }
            } else {
                $startdate_enrol = $enrol->timestart;
                $enddate_enrol = $enrol->timeend;
            }
        }
        if (isset($startdate_enrol))
            if ($startdate_enrol == "" || $startdate_enrol == 0) {
                $startdate = get_string('date_notissued', 'format_dlformatcourseflix');
            } else {
                $meses = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
                $startdate = date('d', $startdate_enrol) . " " . $meses[date('m', $startdate_enrol) - 1] . " " . date('Y', $startdate_enrol);
            }

        if (isset($enddate_enrol))
            if ($enddate_enrol == "" || $enddate_enrol == 0) {
                $enddate = get_string('date_notissued', 'format_dlformatcourseflix');
            } else {
                $meses = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
                $enddate = date('d', $enddate_enrol) . " " . $meses[date('m', $enddate_enrol) - 1] . " " . date('Y', $enddate_enrol);
            }

        $date_assigned = get_string('date_assigned', 'format_dlformatcourseflix');
        $due_date = get_string('due_date', 'format_dlformatcourseflix');
        $lesson_duration = get_string('lesson_duration', 'format_dlformatcourseflix');
        $summary_title = get_string('summary_title', 'format_dlformatcourseflix');

        if (!$status = $DB->get_field('course_completions', 'status', array('userid' => $USER->id, 'course' => $COURSE->id))) {
            $status = null;
        }

        //Get percentage of completion Course
        $renderer = $PAGE->get_renderer('totara_core');
        $hideifnotactive = false;
        $content = $renderer->export_course_progress_for_template($USER->id, $COURSE->id, $status, $hideifnotactive);

        $course_percentage = isset($content->percent) ? $content->percent : 0;

        if ($course_percentage == null || is_nan($course_percentage)) {
            $course_percentage = 0;
        }
        //Info del curso

        $out = '';
        $out .= html_writer::start_tag('div', array('class' => 'course-wrapper'));
        $out .= html_writer::start_tag('div', array('class' => 'info-course'));

        $out .= html_writer::start_tag('div', array('class' => 'image-course-holder'));
        $out .= html_writer::tag('img', '', array('class' => 'image-course', 'src' => $imgcourse));
        $out .= html_writer::start_tag('div', array('class'=>'dl-gradient-overlay'));
        $out .= html_writer::end_tag('div');

        $out .= html_writer::end_tag('div');

        $out .= html_writer::start_tag('div', array('class' => 'description-course'));

        $out .= html_writer::tag('span', $fullname, array('class' => 'fullname-course'));
        $summary = $COURSE->summary;


        //Summary
        if ($summary != "") {
            $out .= html_writer::start_tag('div', array('class' => 'summary'));
            $out .= html_writer::tag('span', $summary, array('class' => 'summary-course text-font-size'));
            $out .= html_writer::end_tag('div');
        }

        $left_percentage = (float)$course_percentage-2;
        
        $out .= html_writer::start_tag('div', array('class' => 'group-wrapper'));
        $out .= html_writer::start_tag('div', array('class' => 'group-2'));
        $out .= html_writer::start_tag('div', array('class' => 'progress-bar'));
        $out .= html_writer::start_tag('div', array('class' => 'bar-circle'));
        $out .= html_writer::tag('div', '', array('style' => 'width:' . $course_percentage . '%;', 'class' => 'active-bar', 'role' => 'progressbar', 'aria-valuenow' => $course_percentage, 'aria-valuemin' => "0", 'aria-valuemax' => "100"));
        $out .= html_writer::end_tag('div');
        $out .= html_writer::start_tag('div', array('class' => 'text-BG', 'style'=>'left:'.$left_percentage.'%;'));
        $out .= html_writer::start_tag('div', array('class' => 'text'));
        $out .= html_writer::tag('div', $course_percentage . '%', array('class'=>'text-wrapper-7'));
        $out .= html_writer::end_tag('div');
        $out .= html_writer::tag('img', '', array('class' => 'polygon', 'src'=>$CFG->dirroot.'/theme/dlcourseflix/pix/program/polygon.svg'));
        $out .= html_writer::end_tag('div');

        $out .= html_writer::end_tag('div');



        $out .= html_writer::end_tag('div');
        $out .= html_writer::end_tag('div');

        $out .= html_writer::end_tag('div');
        $out .= html_writer::end_tag('div');

        $out .= html_writer::end_tag('div');
        //Info del curso

        $out .= html_writer::start_tag('ul', array('class' => 'topics'));
        return $out;
    }

    /**
     * Generate the closing container html for a list of sections
     * @return string HTML to output.
     */
    protected function end_section_list()
    {
        return html_writer::end_tag('ul');
    }

    /**
     * Generate the title for this section page
     * @return string the page title
     */
    protected function page_title()
    {
        return get_string('topicoutline');
    }

    /**
     * Generate the section title, wraps it in a link to the section page if page is to be displayed on a separate page
     *
     * @param stdClass $section The course_section entry from DB
     * @param stdClass $course The course entry from DB
     * @return string HTML to output.
     */
    public function section_title($section, $course)
    {
        return $this->render(course_get_format($course)->inplace_editable_render_section_name($section));
    }

    /**
     * Generate the section title to be displayed on the section page, without a link
     *
     * @param stdClass $section The course_section entry from DB
     * @param stdClass $course The course entry from DB
     * @return string HTML to output.
     */
    public function section_title_without_link($section, $course)
    {
        return $this->render(course_get_format($course)->inplace_editable_render_section_name($section, false));
    }

    /**
     * Generate the edit control items of a section
     *
     * @param stdClass $course The course entry from DB
     * @param stdClass $section The course_section entry from DB
     * @param bool $onsectionpage true if being printed on a section page
     * @return array of edit control items
     */
    protected function section_edit_control_items($course, $section, $onsectionpage = false) {
        global $PAGE;

        if (!$PAGE->user_is_editing()) {
            return array();
        }

        $coursecontext = context_course::instance($course->id);
        $numsections = course_get_format($course)->get_last_section_number();
        $isstealth = $section->section > $numsections;

        if ($onsectionpage) {
            $baseurl = course_get_url($course, $section->section);
        } else {
            $baseurl = course_get_url($course);
        }
        $baseurl->param('sesskey', sesskey());

        $controls = array();

        if (!$isstealth && has_capability('moodle/course:update', $coursecontext)) {
            if ($section->section > 0
                && get_string_manager()->string_exists('editsection', 'format_'.$course->format)) {
                $streditsection = get_string('editsection', 'format_'.$course->format);
            } else {
                $streditsection = get_string('editsection');
            }

            $sectionreturn = $onsectionpage ? $section->section : 0;
            $controls['edit'] = array(
                'url'   => new moodle_url('/course/editsection.php', array('id' => $section->id, 'sr' => $sectionreturn)),
                'icon' => 'i/settings',
                'name' => $streditsection,
                'pixattr' => array('class' => '', 'alt' => $streditsection),
                'attr' => array('class' => 'icon edit', 'title' => $streditsection));
        }

        if ($section->section) {
            $url = clone($baseurl);
            if (!$isstealth) {
                if (has_capability('moodle/course:sectionvisibility', $coursecontext)) {
                    if ($section->visible) { // Show the hide/show eye.
                        $strhidefromothers = get_string('hidefromothers', 'format_'.$course->format);
                        $url->param('hide', $section->section);
                        $controls['visiblity'] = array(
                            'url' => $url,
                            'icon' => 'i/hide',
                            'name' => $strhidefromothers,
                            'pixattr' => array('class' => '', 'alt' => $strhidefromothers),
                            'attr' => array('class' => 'icon editing_showhide', 'title' => $strhidefromothers));
                    } else {
                        $strshowfromothers = get_string('showfromothers', 'format_'.$course->format);
                        $url->param('show',  $section->section);
                        $controls['visiblity'] = array(
                            'url' => $url,
                            'icon' => 'i/show',
                            'name' => $strshowfromothers,
                            'pixattr' => array('class' => '', 'alt' => $strshowfromothers),
                            'attr' => array('class' => 'icon editing_showhide', 'title' => $strshowfromothers));
                    }
                }

                if (!$onsectionpage) {
                    if (has_capability('moodle/course:movesections', $coursecontext)) {
                        $url = clone($baseurl);
                        if ($section->section > 1) { // Add a arrow to move section up.
                            $url->param('section', $section->section);
                            $url->param('move', -1);
                            $strmoveup = get_string('moveup');
                            $controls['moveup'] = array(
                                'url' => $url,
                                'icon' => 'i/up',
                                'name' => $strmoveup,
                                'pixattr' => array('class' => '', 'alt' => $strmoveup),
                                'attr' => array('class' => 'icon moveup', 'title' => $strmoveup));
                        }

                        $url = clone($baseurl);
                        if ($section->section < $numsections) { // Add a arrow to move section down.
                            $url->param('section', $section->section);
                            $url->param('move', 1);
                            $strmovedown = get_string('movedown');
                            $controls['movedown'] = array(
                                'url' => $url,
                                'icon' => 'i/down',
                                'name' => $strmovedown,
                                'pixattr' => array('class' => '', 'alt' => $strmovedown),
                                'attr' => array('class' => 'icon movedown', 'title' => $strmovedown));
                        }
                    }
                }
            }

            if (course_can_delete_section($course, $section)) {
                if (get_string_manager()->string_exists('deletesection', 'format_'.$course->format)) {
                    $strdelete = get_string('deletesection', 'format_'.$course->format);
                } else {
                    $strdelete = get_string('deletesection');
                }
                $url = new moodle_url('/course/editsection.php', array(
                    'id' => $section->id,
                    'sr' => $onsectionpage ? $section->section : 0,
                    'delete' => 1));
                $controls['delete'] = array(
                    'url' => $url,
                    'icon' => 'i/delete',
                    'name' => $strdelete,
                    'pixattr' => array('class' => '', 'alt' => $strdelete),
                    'attr' => array('class' => 'icon delete', 'title' => $strdelete));
            }
        }

        return $controls;
    }

    /**
     * Generate the display of the header part of a section before
     * course modules are included
     *
     * @param stdClass $section The course_section entry from DB
     * @param stdClass $course The course entry from DB
     * @param bool $onsectionpage true if being printed on a single-section page
     * @param int $sectionreturn The section to return to after an action
     * @return string HTML to output.
     */
    protected function section_header($section, $course, $onsectionpage, $sectionreturn=null) {
        global $PAGE;

        $o = '';
        $currenttext = '';
        $sectionstyle = '';

        if ($section->section != 0) {
            // Only in the non-general sections.
            if (!$section->visible) {
                $sectionstyle = ' hidden';
            } else if (course_get_format($course)->is_section_current($section)) {
                $sectionstyle = ' current';
            }
        }

        $o .= html_writer::start_tag(
            'li',
            array(
                'id' => 'section-' . $section->section,
                'class' => 'section main clearfix' . $sectionstyle,
                'aria-label' => get_section_name($course, $section)
            )
        );

        // Create a span that contains the section title to be used to create the keyboard section move menu.
        $o .= html_writer::tag('span', get_section_name($course, $section), array('class' => 'hidden sectionname'));

        $leftcontent = $this->section_left_content($section, $course, $onsectionpage);
        $o.= html_writer::tag('div', $leftcontent, array('class' => 'left side'));

        $rightcontent = $this->section_right_content($section, $course, $onsectionpage);
        $o.= html_writer::tag('div', $rightcontent, array('class' => 'right side'));
        $o.= html_writer::start_tag('div', array('class' => 'content'));

        // When not on a section page, we display the section titles except the general section if null
        $hasnamenotsecpg = (!$onsectionpage && ($section->section != 0 || !is_null($section->name)));

        // When on a section page, we only display the general section title, if title is not the default one
        $hasnamesecpg = ($onsectionpage && ($section->section == 0 && !is_null($section->name)));

        $classes = ' accesshide';
        if ($hasnamenotsecpg || $hasnamesecpg) {
            $classes = '';
        }
        $sectionname = html_writer::tag('span', $this->section_title($section, $course), array('data-movetext' => 'true'));
        // $o.= $this->output->heading($sectionname, 3, 'sectionname' . $classes);
        $o.= html_writer::start_tag('div', array('class' => 'sectionname'));
            $o.= $this->output->heading($sectionname, 3, $classes);
            $o.= $this->output->flex_icon('theme_dlcourseflix|expanded', array('classes' => 'button-show-section'));
        $o.= html_writer::end_tag('div');

        $o.= html_writer::start_tag('div', array('class' => 'summary'));
        $o.= $this->format_summary_text($section);
        $o.= html_writer::end_tag('div');

        $context = context_course::instance($course->id);
        $o .= $this->section_availability_message($section,
            has_capability('moodle/course:viewhiddensections', $context));

        return $o;
    }
}
