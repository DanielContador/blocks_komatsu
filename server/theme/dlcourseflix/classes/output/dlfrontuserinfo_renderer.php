<?php

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot. "/lib/outputrenderers.php" );

class block_dlfrontuserinfo_renderer extends plugin_renderer_base{
    public function dl_frontuserinfo() {

        return $this->get_content();
    }

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
            $count_enrroll = 0;
        } else {
            $count_enrroll = count($enrollcourses);
        }

        $allmyprograms = $this->getAllMyPrograms()? count($this->getAllMyPrograms()) : 0;
        $allmycompletedPrograms = $this->getAllMyCompletedPrograms()? count($this->getAllMyCompletedPrograms()): 0;

        $activities_complete = 0;
        $count_incomplete = 0;
        foreach ($enrollcourses as $isenrroll) {
            //Get completion info, the entire course object is passed
            $cinfo = new completion_info($isenrroll);
            //Completion info course by userid
            $iscomplete = $cinfo->is_course_complete($USER->id);
            if ($iscomplete) {
                $activities_complete++;
            } else {
                $count_incomplete++;
            }
        }

        //Get badges by userid param, badges related with table badge and badge_issued
        $badges = $DB->get_records_sql("SELECT
                bi.uniquehash,
                bi.dateissued,
                bi.dateexpire,
                bi.id as issuedid,
                bi.visible,
                u.email,
                b.*
            FROM
                {badge} b,
                {badge_issued} bi,
                {user} u
            WHERE b.id = bi.badgeid
                AND u.id = bi.userid
                AND bi.userid = :userid", ['userid' => $USER->id]);

        $count_badges = 0;

        if (empty($badges)) {
            $badgesimg = [];
        } else {
            foreach ($badges as $badge) {
                $context = ($badge->type == BADGE_TYPE_SITE) ? context_system::instance() : context_course::instance($badge->courseid);
                $bname = $badge->name;
                $imageurl = file_encode_url("$CFG->wwwroot/pluginfile.php", '/' . $context->id . '/' . 'badges' . '/' . 'badgeimage' . '/' . $badge->id . '/' . 'f1');
                $count_badges++;
                $badgesimg[] = ["get_badges" => $imageurl, "name_badge" => $bname];
                $last_badges = array_slice($badgesimg, -4);

            }
        }

        if ($count_badges > 4) {
            $morebadges = $CFG->wwwroot . '/badges/mybadges.php';
            $has_more = true;
        } else {
            $has_more = false;
        }

        $myroles = 'Técnico de Soporte';
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
            "badges" => $last_badges ?? array(),
            "image_profile" => $picture,
            "courses_enrroll" => $count_enrroll,
            "courses_completed" => $activities_complete,
            "courses_incomplete" => $count_incomplete,
            "link_profile" => $link_profile,
            "more_badges" => $morebadges ?? '',
            "has_more" => $has_more,
            "not_badges" => $not_badges,
            "all_my_programas" => $allmyprograms,
            "all_my_completed_programas" => $allmycompletedPrograms,
            "all_my_roles" => $myroles
        ];

        $output = $OUTPUT->render_from_template('block_dlfrontuserinfo/dlfrontuserinfo', ["infouser" => $infouser]);

        return $output;

    }

    public function getAllMyPrograms(){
        global $DB, $USER;

        $sql = "SELECT p.id, p.fullname
           FROM {prog} p
           JOIN {prog_completion} pc ON pc.programid = p.id
           WHERE pc.userid = :userid";

        $params = ['userid' => $USER->id];

        return $enrolledprograms = $DB->get_records_sql($sql, $params);
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

        return $completedprograms = $DB->get_records_sql($sql, $params);
    }

    public function getMyRole()
    {
        global $DB, $USER;

        $sql = "SELECT ra.roleid, r.name, r.shortname
        FROM {role_assignments} ra
        JOIN {role} r ON ra.roleid = r.id
        WHERE ra.userid = :userid";

        $params = ['userid' => $USER->id];

        return $userroles = $DB->get_records_sql($sql, $params);
    }
}