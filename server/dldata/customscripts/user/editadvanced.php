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
 * Allows you to edit a users profile
 *
 * @copyright 1999 Martin Dougiamas  http://dougiamas.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package core_user
 */

require_once('../config.php');
require_once($CFG->libdir.'/gdlib.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/user/editadvanced_form.php');
require_once($CFG->dirroot.'/user/editlib.php');
require_once($CFG->dirroot.'/user/profile/lib.php');
require_once($CFG->dirroot.'/user/lib.php');
require_once($CFG->dirroot.'/webservice/lib.php');

$id     = optional_param('id', $USER->id, PARAM_INT);    // User id; -1 if creating new user.
$course = optional_param('course', SITEID, PARAM_INT);   // Course id (defaults to Site).
$returnto = optional_param('returnto', null, PARAM_ALPHANUMEXT);  // Code determining where to return to after save.
$customreturn = optional_param('returnurl', '', PARAM_LOCALURL);

$PAGE->set_url('/user/editadvanced.php', array('course' => $course, 'id' => $id));
$PAGE->requires->css(new moodle_url('/theme/dlcourseflix/style/user_profile/style.css'));
$course = $DB->get_record('course', array('id' => $course), '*', MUST_EXIST);

if (!empty($USER->newadminuser)) {
    // Ignore double clicks, we must finish all operations before cancelling request.
    ignore_user_abort(true);

    $PAGE->set_course($SITE);
    $PAGE->set_pagelayout('maintenance');
} else {
    if ($course->id == SITEID) {
        require_login();
        $PAGE->set_context(context_system::instance());
    } else {
        require_login($course);
    }
    /*$PAGE->set_pagelayout('admin');*/
    $PAGE->set_pagelayout('profile');
}

if ($course->id == SITEID) {
    $coursecontext = context_system::instance();   // SYSTEM context.
} else {
    $coursecontext = context_course::instance($course->id);   // Course context.
}
$systemcontext = context_system::instance();

$iscreating = false;

if ($id == -1) {
    // Creating new user.
    $iscreating = true;
    $user = new stdClass();
    $user->id = -1;
    $user->auth = 'manual';
    $user->confirmed = 1;
    $user->deleted = 0;
    $user->timezone = '99';
    require_capability('moodle/user:create', $systemcontext);
    // TOTARA: This is no longer associated with a admin menu item
    //admin_externalpage_setup('addnewuser', '', array('id' => -1));
} else {
    // Editing existing user.
    if (!has_capability('moodle/user:update', $systemcontext)) {
        \core\notification::error(get_string('error:userprofilecapability', 'totara_core'));
        $redirecturl = new moodle_url('/user/profile.php', ['id' => $id]);
        redirect($redirecturl);
    }

    $user = $DB->get_record('user', array('id' => $id), '*');
    if ($user === false || $user->deleted) {
        $PAGE->set_context(context_system::instance());
        echo $OUTPUT->header();
        echo $OUTPUT->notification(get_string('usernotavailable', 'error'));
        echo $OUTPUT->footer();
        die;
    }
    $user_context = context_user::instance($user->id);

    if ($user_context->is_user_access_prevented($USER->id)) {
        $PAGE->set_context(context_system::instance());
        echo $OUTPUT->header();
        echo $OUTPUT->notification(get_string('usernotavailable', 'error'));
        echo $OUTPUT->footer();
        die;
    }

    $PAGE->set_context($user_context);
    $PAGE->navbar->includesettingsbase = true;
    if ($user->id != $USER->id) {
        $PAGE->navigation->extend_for_user($user);
    } else {
        if ($node = $PAGE->navigation->find('myprofile', navigation_node::TYPE_ROOTNODE)) {
            $node->force_open();
        }
    }
}

// Totara: Use standard external edit profile url if not admin.
$externalediturl = null;
if (exists_auth_plugin($user->auth)) {
    $userauth = get_auth_plugin($user->auth);
    $externalediturl = $userauth->edit_profile_url($user->id);
}
if ($externalediturl and !is_siteadmin()) {
    redirect($externalediturl);
}

if ($user->id != $USER->id and is_siteadmin($user) and !is_siteadmin($USER)) {  // Only admins may edit other admins.
    print_error('useradmineditadmin');
}

if (isguestuser($user->id)) { // The real guest user can not be edited.
    print_error('guestnoeditprofileother');
}

// Load user preferences.
useredit_load_preferences($user);

// Load custom profile fields data.
profile_load_data($user);

// User interests.
$user->interests = core_tag_tag::get_item_tags_array('core', 'user', $id,
    core_tag_tag::BOTH_STANDARD_AND_NOT, 0, false); // Totara: Do not encoded the special characters

if ($user->id !== -1) {
    $usercontext = context_user::instance($user->id);
    $editoroptions = array(
        'maxfiles'   => EDITOR_UNLIMITED_FILES,
        'maxbytes'   => $CFG->maxbytes,
        'forcehttps' => false,
        'context'    => $usercontext
    );

    $user = file_prepare_standard_editor($user, 'description', $editoroptions, $usercontext, 'user', 'profile', 0);
} else {
    $usercontext = null;
    // This is a new user, we don't want to add files here.
    $editoroptions = array(
        'maxfiles' => 0,
        'maxbytes' => 0,
        'forcehttps' => false,
        'context' => $coursecontext
    );
}

// Prepare filemanager draft area.
$draftitemid = 0;
$filemanagercontext = $editoroptions['context'];
$filemanageroptions = array('maxbytes'       => $CFG->maxbytes,
                             'subdirs'        => 0,
                             'maxfiles'       => 1,
                             'accepted_types' => 'web_image');
file_prepare_draft_area($draftitemid, $filemanagercontext->id, 'user', 'newicon', 0, $filemanageroptions);
$user->imagefile = $draftitemid;
// Create form.
$formurl = new moodle_url($PAGE->url, array('returnto' => $returnto));
if ($customreturn) {
    $formurl->param('returnurl', $customreturn);
}
$userform = new user_editadvanced_form($formurl, array(
    'editoroptions' => $editoroptions,
    'filemanageroptions' => $filemanageroptions,
    'user' => $user));

if ($userform->is_cancelled()) {
    redirect(useredit_get_return_url($user, $returnto, $course, $customreturn));
}

if ($usernew = $userform->get_data()) {
    $usercreated = false;

    if (empty($usernew->auth)) {
        // User editing self.
        $authplugin = get_auth_plugin($user->auth);
        unset($usernew->auth); // Can not change/remove.
    } else {
        $authplugin = get_auth_plugin($usernew->auth);
    }

    $usernew->timemodified = time();
    $createpassword = false;

    if ($usernew->id == -1) {
        unset($usernew->id);
        $createpassword = !empty($usernew->createpassword);
        unset($usernew->createpassword);
        $usernew = file_postupdate_standard_editor($usernew, 'description', $editoroptions, null, 'user', 'profile', null);
        $usernew->confirmed  = 1;
        $usernew->timecreated = time();
        if ($authplugin->is_internal()) {
            if ($createpassword or empty($usernew->newpassword)) {
                $usernew->password = '';
            } else {
                $usernew->password = hash_internal_user_password($usernew->newpassword);
            }
        } else {
            $usernew->password = AUTH_PASSWORD_NOT_CACHED;
        }
        $usernew->id = user_create_user($usernew, false, false);

        if (!$authplugin->is_internal() and $authplugin->can_change_password() and !empty($usernew->newpassword)) {
            if (!$authplugin->user_update_password($usernew, $usernew->newpassword)) {
                // Do not stop here, we need to finish user creation.
                debugging(get_string('cannotupdatepasswordonextauth', '', '', $usernew->auth), DEBUG_NONE);
            }
        }
        $usercreated = true;
    } else {
        $usernew = file_postupdate_standard_editor($usernew, 'description', $editoroptions, $usercontext, 'user', 'profile', 0);
        // Pass a true old $user here.
        if (!$authplugin->user_update($user, $usernew)) {
            // Auth update failed.
            print_error('cannotupdateuseronexauth', '', '', $user->auth);
        }
        user_update_user($usernew, false, false);

        // Set new password if specified.
        if (!empty($usernew->newpassword)) {
            if ($authplugin->can_change_password()) {
                $options = [
                    'signoutofotherservices' => !empty($usernew->signoutofotherservices),
                ];
                if (!user_change_password($usernew->id, $usernew->newpassword, $options)) {
                    print_error('cannotupdatepasswordonextauth', '', '', $usernew->auth);
                }
            }
        }

        // Force logout if user just suspended.
        if (isset($usernew->suspended) and $usernew->suspended and !$user->suspended) {
            \core\session\manager::kill_user_sessions($user->id);

            // Totara: Trigger a user suspended event later after the actual update!
            $triggersuspended = true;
        }
        if (isset($usernew->suspended) && !$usernew->suspended && $user->suspended) {
            // Totara: Trigger a user unsuspended event later after the actual update!
            $triggerunsuspended = true;
        }
    }

    $usercontext = context_user::instance($usernew->id);

    // Update preferences.
    useredit_update_user_preference($usernew);

    // Update tags.
    if (empty($USER->newadminuser) && isset($usernew->interests)) {
        useredit_update_interests($usernew, $usernew->interests);
    }

    // Update user picture.
    if (empty($USER->newadminuser)) {
        core_user::update_picture($usernew, $filemanageroptions);
    }

    // Update mail bounces.
    if (!$iscreating && isset($usernew->email) && $user->email !== $usernew->email) {
        $emailbouncecounter = new \core_user\email_bounce_counter($usernew);
        $emailbouncecounter->reset_counts();
    }

    // Update forum track preference.
    useredit_update_trackforums($user, $usernew);

    // Save custom profile fields data.
    profile_save_data($usernew);

    $hook = new core_user\hook\editadvanced_form_save_changes($iscreating, $usernew->id, $usernew);
    $hook->execute();

    // Reload from db.
    $usernew = $DB->get_record('user', array('id' => $usernew->id));

    if ($createpassword) {
        setnew_password_and_mail($usernew);
    }

    // Trigger update/create event, after all fields are stored.
    if ($usercreated) {
        \core\event\user_created::create_from_userid($usernew->id)->trigger();
    } else {
        \core\event\user_updated::create_from_userid($usernew->id)->trigger();

        // Totara feature, the var is not always initialised to minimise the diff.
        if (!empty($triggersuspended)) {
            \totara_core\event\user_suspended::create_from_user($user)->trigger();
        }
        if (!empty($triggerunsuspended)) {
            \totara_core\event\user_unsuspended::create_from_user($user)->trigger();
        }
    }

    if (optional_param('viewprofile', '', PARAM_TEXT)) {
        $customreturn = null;
        $returnto = 'profile';
    }
    if ($user->id == $USER->id) {
        // Override old $USER session variable.
        foreach ((array)$usernew as $variable => $value) {
            if ($variable === 'description' or $variable === 'password') {
                // These are not set for security nad perf reasons.
                continue;
            }
            $USER->$variable = $value;
        }
        // Preload custom fields.
        profile_load_custom_fields($USER);

        if (!empty($USER->newadminuser)) {
            unset($USER->newadminuser);
            // Apply defaults again - some of them might depend on admin user info, backup, roles, etc.
            admin_apply_default_settings(null, false);
            // Admin account is fully configured - set flag here in case the redirect does not work.
            unset_config('adminsetuppending');
            // Redirect to admin/ to continue with installation.
            redirect("$CFG->wwwroot/$CFG->admin/");
        } else if (empty($SITE->fullname)) {
            // Somebody double clicked when editing admin user during install.
            redirect("$CFG->wwwroot/$CFG->admin/");
        } else {
            redirect(useredit_get_return_url($usernew, $returnto, $course, $customreturn));
        }
    } else {
        \core\session\manager::gc(); // Remove stale sessions.
        redirect(useredit_get_return_url($usernew, $returnto, $course, $customreturn));
    }
    // Never reached..
}


// Display page header.
if ($user->id == -1 or ($user->id != $USER->id)) {
    if ($user->id == -1) {
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('createuser'));
    } else {
        $streditmyprofile = get_string('editmyprofile');
        $userfullname = fullname($user, true);
        $PAGE->set_heading($userfullname);
        $PAGE->set_title("$course->shortname: $streditmyprofile - $userfullname");
        $PAGE->set_privacy_aware_title("$course->shortname: $streditmyprofile - " . get_string('userx', 'moodle', $user->id));
        echo $OUTPUT->header();
        echo $OUTPUT->heading($userfullname);
    }
} else if (!empty($USER->newadminuser)) {
    $strinstallation = get_string('installation', 'install');
    $strprimaryadminsetup = get_string('primaryadminsetup');

    $PAGE->navbar->add($strprimaryadminsetup);
    $PAGE->set_title($strinstallation);
    $PAGE->set_heading($strinstallation);
    $PAGE->set_cacheable(false);

    echo $OUTPUT->header();
    echo $OUTPUT->box(get_string('configintroadmin', 'admin'), 'generalbox boxwidthnormal boxaligncenter');
    echo '<br />';
} else {
    $streditmyprofile = get_string('editmyprofile');
    $strparticipants  = get_string('participants');
    $strnewuser       = get_string('newuser');
    $userfullname     = fullname($user, true);

    $PAGE->set_title("$course->shortname: $streditmyprofile");
    $PAGE->set_heading($userfullname);

    echo $OUTPUT->header();
    echo $OUTPUT->heading($streditmyprofile);
}

if ($externalediturl) {
    // Totara: Tell admin that they should not edit the local profile when plugin has its own profile edit page.
    echo $OUTPUT->notification(get_string('profileeditexternal', 'core_auth'), 'notifyproblem');
    echo $OUTPUT->single_button(new moodle_url($externalediturl), get_string('editmyprofile'), 'get');
}

// Finally display THE form.
$userform->display();

// And proper footer.
echo $OUTPUT->footer();

die();