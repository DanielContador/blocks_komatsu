<?php

namespace theme_dlcourseflix\quickaccessmenu;

use \totara_core\quickaccessmenu\group;
use \totara_core\quickaccessmenu\item;

class dlcourseflix implements \totara_core\quickaccessmenu\provider {

    /**
     * Return the items that core_user wishes to introduce to the quick access menu.
     *
     * @return item[]
     */
    public static function get_items(): array {
        global $USER, $PAGE;

        // Do not show this for admin user.
        if (is_siteadmin($USER)) {
            return [];
        }

        // Do not show this if theme is not dlcourseflix.
        if ($PAGE->theme->name !== 'dlcourseflix') {
            return [];
        }

        return [
            item::from_provider(
                'dlcourseflix_editor',
                group::get(group::CONFIGURATION),
                new \lang_string('pluginname', 'theme_dlcourseflix'),
                1000
            )
        ];
    }
}