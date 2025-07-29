<?php
use totara_competency\entity\competency_achievement;

class block_dlfrontuserinfo extends block_base {
    function is_empty() {
        $this->get_content();
        return (empty($this->content->text) && empty($this->content->footer));
    }

    function init() {
        $this->title = get_string('pluginname', 'block_dlfrontuserinfo');
        if (empty($this->config)) {
            $this->config = new stdClass();
        }

        if (empty($this->config->showbadges)) {
            $this->config->showbadges = 1;
        }
    }

    function has_config() {
        return true;
    }

    function get_content() {
        global $CFG, $DB, $OUTPUT, $PAGE, $USER;

        require_once($CFG->dirroot . '/totara/program/program.class.php');
        require_once("{$CFG->libdir}/completionlib.php");

        if ($this->content !== NULL) {
            return $this->content;
        }

        if ($this->config->showbadges == 0) {
            $not_badges = false;
        } else {
            $not_badges = true;
        }

        //Get userInfo object, param user->id
        $userinfo = $DB->get_record('user', array('id' => $USER->id));

        //Id user
        $iduser = $userinfo->id;

        //Email user logged
        $email = $userinfo->email;

        //name user logged
        $name = $userinfo->firstname . ' ' . $userinfo->lastname;

        //Link User edit profile
        $link_profile = $CFG->wwwroot . '/user/profile.php?id=' . $iduser . '';

        //Profile image user logged
        $user_picture = new user_picture($USER);
        $user_picture->size = 64;
        $picture = $user_picture->get_url($PAGE);

        $image_user = $OUTPUT->user_picture($USER, array('courseid' => SITEID, 'size' => 64));

        //Get enrolled courses user logged
        $enrollcourses = enrol_get_users_courses($USER->id);

        if (empty($enrollcourses)) {
            $count_courses_enrolled = 0;
        } else {
            $count_courses_enrolled = count($enrollcourses);
        }

        // Program Variables
        $count_programs_completed = 0;
        $count_programs_assigned = 0;

        $assignedprograms = prog_get_all_programs($USER->id, '', '', '', false, false, true, false);
        if(empty($assignedprograms)) {
            $count_programs_assigned = 0;
        }
        else {
            $count_programs_assigned = count($assignedprograms);
        }

        foreach($assignedprograms as $program) {
            if($program->status == STATUS_PROGRAM_COMPLETE)
            {
                $count_programs_completed++;
            }
        }

        // Calculate the number of completed and assigned competencies
        $competency_sql = "SELECT 
            COUNT(CASE WHEN tca.proficient = 1 THEN 1 END) as count_completedcompetencies,
            COUNT(DISTINCT tcau.competency_id) as count_competencies
        FROM {totara_competency_assignment_users} tcau
        LEFT JOIN {totara_competency_achievement} tca ON tcau.competency_id = tca.competency_id AND tcau.user_id = tca.user_id
        INNER JOIN {comp} c ON tcau.competency_id = c.id
        WHERE tcau.user_id = :userid AND tca.status = :active_status";

        $params = [
            'userid' => $USER->id,
            'active_status' => competency_achievement::ACTIVE_ASSIGNMENT
        ];

        $competency_data = $DB->get_record_sql($competency_sql, $params);

        if ($competency_data) {
            $count_competencies_assigned = $competency_data->count_competencies;
            $count_competencies_completed = $competency_data->count_completedcompetencies;
            $competencies_perform = $count_competencies_assigned > 0?round($count_competencies_completed*100 / $count_competencies_assigned):0;
        } else {
            $competencies_perform = 0;
        }

        $count_courses_completed = 0;
        foreach ($enrollcourses as $isenrroll) {
            //Get completion info, the entire course object is passed
            $cinfo = new completion_info($isenrroll);
            //Completion info course by userid
            $iscomplete = $cinfo->is_course_complete($USER->id);
            if ($iscomplete) {
                $count_courses_completed++;
            }
        }

        $myroles = '';
        if($this->getMyRole()){
            $userroles = $this->getMyRole();
            foreach ($userroles as $role) {
                $myroles .= ($role->name ?: $role->shortname) . ', ';
            }
            $myroles = rtrim($myroles, ', ');
        }

        //Array saved info, for render in template 
        $infouser[] = [
            "name_user" => $name,
            "email" => $email,
            "image_profile" => $picture,
            "courses_enrolled" => $count_courses_enrolled,
            "courses_completed" => $count_courses_completed,
            "link_profile" => $link_profile,
            "competencies_perform" => $competencies_perform,
            "programs_assigned" => $count_programs_assigned,
            "programs_completed" => $count_programs_completed,
            "all_my_roles" => $myroles
        ];

        $output = $OUTPUT->render_from_template('block_dlfrontuserinfo/dlfrontuserinfo', ["infouser" => $infouser]);
        if (empty($this->content)) {
            $this->content = new stdClass();
        }
        return $this->content->text = $output;

    }

    public function getAllMyPrograms(){
        global $DB, $USER;

        $sql = "SELECT p.id, p.fullname
           FROM {prog} p
           JOIN {prog_completion} pc ON pc.programid = p.id
           WHERE pc.userid = :userid";

        $params = ['userid' => $USER->id];

        $enrolledprograms = $DB->get_records_sql($sql, $params);
    }

    public function getAllMyCompletedPrograms(){
        global $DB, $USER;

        $sql = "SELECT p.id, p.fullname
        FROM {prog} p
        JOIN {prog_completion} pc ON pc.programid = p.id
        WHERE pc.userid = :userid AND pc.status = :status";

        $params = [
            'userid' => $USER->id,
            'status' => 1 // Aseg�rate de que este valor de estado corresponde al estado de "completado"
        ];

        $completedprograms = $DB->get_records_sql($sql, $params);
    }

    public function getMyRole()
    {
        global $DB, $USER;

        $sql = "SELECT ra.roleid, r.name, r.shortname
        FROM {role_assignments} ra
        JOIN {role} r ON ra.roleid = r.id
        WHERE ra.userid = :userid";

        $params = ['userid' => $USER->id];

        $userroles = $DB->get_records_sql($sql, $params);
    }
}
