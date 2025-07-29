<?php
/*
*/

defined('MOODLE_INTERNAL') || die;
require_once($CFG->dirroot . "/totara/program/renderer.php");
require_once($CFG->dirroot . '/totara/program/program.class.php');

class theme_dlcourseflix_totara_program_renderer extends totara_program_renderer {

    public function dl_display_program(&$program, $userid) {
        global $USER, $OUTPUT, $DB, $CFG, $PAGE;
        $program_description = strip_tags($program->summary);

        //Get image program
        $program_image = $program->get_image();

        //Get name of program
        $program_fullname = $program->fullname;

        //Get percentage of program completion
        $program_percentage = round(totara_program_get_user_percentage_complete($program->id, $userid));

        if ($program_percentage == null || is_nan($program_percentage)) {
            $program_percentage = 0;
        }

        // Bool is certification
        $iscertif = (isset($program->certifid) && $program->certifid > 0) ? true : false;

        // Certification Program completion 
        if ($iscertif) {
            [$certif_completion, $prog_completion] = certif_load_completion($program->id, $userid, false);
        } else {
            $prog_completion = prog_load_completion($program->id, $userid, false);
        }

        // Calculate program assignment date
        $sql = "SELECT pa.id, pa.programid ,pa.assignmenttype,pa.assignmenttypeid, pua.timeassigned
                  FROM {prog_assignment} pa
                  JOIN {prog_user_assignment} pua ON pua.assignmentid = pa.id
                 WHERE pua.userid = :userid
                   AND pua.programid = :programid";

        $params = [
            'userid' => $USER->id,
            'programid' => $program->id,
        ];

        $user_assignments = $DB->get_records_sql($sql, $params);

        $mounts = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio', 7 => 'Julio',
            8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
        ];

        if (!empty($user_assignments)) {
            $ua = array_values($user_assignments)[0];
            $day = date('d', $ua->timeassigned);
            $mount = date('n', $ua->timeassigned);
            $year = date('Y', $ua->timeassigned);
            // if assignment type is by audience take audiece start date
            if ($ua->assignmenttype == ASSIGNTYPE_COHORT) {
                $cohort = $DB->get_record('cohort', ['id' => $ua->assignmenttypeid]);
                if (!empty($cohort) && $cohort->startdate > $ua->timeassigned) {
                    $day = date('d', $cohort->startdate);
                    $mount = date('n', $cohort->startdate);
                    $year = date('Y', $cohort->startdate);
                }
            }

            $startdatestr = $day . ' de ' . $mounts[$mount] . ' del ' . $year;

        } else {
            $startdatestr = '-';
        }

        //Program due date
        if ($prog_completion->timedue > 0) {
            $day = date('d', $prog_completion->timedue);
            $mount = date('n', $prog_completion->timedue);
            $year = date('Y', $prog_completion->timedue);
            $duedatestr = $day . ' de ' . $mounts[$mount] . ' del ' . $year;
        } else {
            $duedatestr = 'No se ha configurado la fecha de caducidad';
        }

        //Course sets
        if ($iscertif) {
            $certifstate = certif_get_completion_state($certif_completion);
            if (is_siteadmin() || !$certif_completion || $certifstate == CERTIFCOMPLETIONSTATE_CERTIFIED) {
                $cert_groups = $program->content->get_courseset_groups(CERTIFPATH_CERT);
                $resert_groups = $program->content->get_courseset_groups(CERTIFPATH_RECERT);
            } else {
                if ($certif_completion->certifpath == CERTIFPATH_CERT) {
                    $courseset_groups = $program->content->get_courseset_groups(CERTIFPATH_CERT);
                } else {
                    $courseset_groups = $program->content->get_courseset_groups(CERTIFPATH_RECERT);
                }
            }
        } else {
            $courseset_groups = $program->content->get_courseset_groups(CERTIFPATH_STD);
        }

        $courseSets = [];
        $number = 1;

        if ($iscertif) {
            foreach ($cert_groups as $courseSetGroup) {
                foreach ($courseSetGroup as $courseset) {

                    switch ($courseset->nextsetoperator) {
                        case NEXTSETOPERATOR_THEN:
                            $courseset->nextoperator = 'LUEGO';
                            break;
                        case NEXTSETOPERATOR_OR:
                            $courseset->nextoperator = 'O';
                            break;
                        case NEXTSETOPERATOR_AND:
                            $courseset->nextoperator = 'Y';
                            break;
                        default:
                            $courseset->nextoperator = '';
                            break;
                    }

                    $courseSets[] = [
                        'label' => $courseset->label,
                        'number' => $number,
                        'isfirstset' => $courseset->isfirstset ?? false,
                        'nextoperator' => $courseset->nextoperator,
                        'courses' => $courseset->get_courses(),
                    ];
                    $number++;
                }
            }

            foreach ($resert_groups as $courseSetGroup) {
                foreach ($courseSetGroup as $courseset) {

                    switch ($courseset->nextsetoperator) {
                        case NEXTSETOPERATOR_THEN:
                            $courseset->nextoperator = 'LUEGO';
                            break;
                        case NEXTSETOPERATOR_OR:
                            $courseset->nextoperator = 'O';
                            break;
                        case NEXTSETOPERATOR_AND:
                            $courseset->nextoperator = 'Y';
                            break;
                        default:
                            $courseset->nextoperator = '';
                            break;
                    }

                    $courseSets[] = [
                        'label' => $courseset->label,
                        'number' => $number,
                        'isfirstset' => $courseset->isfirstset ?? false,
                        'nextoperator' => $courseset->nextoperator,
                        'courses' => $courseset->get_courses(),
                    ];
                    $number++;
                }
            }
        } else {
            foreach ($courseset_groups as $courseSetGroup) {
                foreach ($courseSetGroup as $courseset) {

                    switch ($courseset->nextsetoperator) {
                        case NEXTSETOPERATOR_THEN:
                            $courseset->nextoperator = 'LUEGO';
                            break;
                        case NEXTSETOPERATOR_OR:
                            $courseset->nextoperator = 'O';
                            break;
                        case NEXTSETOPERATOR_AND:
                            $courseset->nextoperator = 'Y';
                            break;
                        default:
                            $courseset->nextoperator = '';
                            break;
                    }

                    $courseSets[] = [
                        'label' => $courseset->label,
                        'number' => $number,
                        'isfirstset' => $courseset->isfirstset ?? false,
                        'nextoperator' => $courseset->nextoperator,
                        'courses' => $courseset->get_courses(),
                    ];
                    $number++;
                }
            }
        }

        foreach ($courseSets as $key => $courseSet) {
            if ($courseSet['isfirstset']) {
                $prevOperator = $courseSet['nextoperator'];
            } else {
                $currentOperator = $courseSet['nextoperator'];
                $courseSet['nextoperator'] = $prevOperator;
                $courseSets[$key] = $courseSet;
                $prevOperator = $currentOperator;
            }

            foreach ($courseSet['courses'] as $course) {

                if (!$status = $DB->get_field('course_completions', 'status', ['userid' => $USER->id, 'course' => $course->id])) {
                    $status = null;
                }

                // User can enter course?
                if ($program->can_enter_course($USER->id, $course->id) || is_siteadmin()) {
                    $userAllowed = true;
                } else {
                    $userAllowed = false;
                }

                $imgcourse = course_get_image($course);
                $fullname = $course->fullname;
                $summary = strip_tags($course->summary);
                $link = new moodle_url('/course/view.php', ['id' => $course->id]);

                if ($status == null || $status == COMPLETION_STATUS_NOTYETSTARTED) {
                    $status = 'No iniciado';
                } else if ($status == COMPLETION_STATUS_INPROGRESS) {
                    $status = 'En progreso';
                } else if ($status == COMPLETION_STATUS_COMPLETE || $status == COMPLETION_STATUS_COMPLETEVIARPL) {
                    $status = 'Completado';
                }

                $completion = new completion_completion(['userid' => $USER->id, 'course' => $course->id]);

                if ($completion->is_complete()) {
                    $percent = 100;
                } else {
                    $percent = $completion->get_percentagecomplete();
                }

                $course = [
                    'fullname' => $fullname,
                    'summary' => $summary,
                    'status' => $status,
                    'percent' => $percent,
                    'image' => $imgcourse,
                    'link' => $link,
                    'userAllowed' => $userAllowed,
                ];

                $courseSets[$key]['coursesArray'][] = $course;

            }
        }

        $programContext = new stdClass();
        $programContext->programImage = $program_image;
        $programContext->fullName = $program->fullname ?? 'Programa';
        $programContext->startDate = $startdatestr;
        $programContext->dueDate = $duedatestr;
        $programContext->programPercent = $program_percentage;
        $programContext->courseSets = $courseSets;

        $out = $PAGE->get_renderer('core')->render_from_template('theme_dlcourseflix/totara_program', $programContext);
        return $out;
    }

}