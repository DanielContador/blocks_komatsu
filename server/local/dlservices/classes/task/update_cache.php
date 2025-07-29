<?php

namespace local_dlservices\task;

use local_dlservices\webapi\resolver\query\items as items;
use local_dlservices\webapi\resolver\query\moreviewsitems as moreviewsitems;
use local_dlservices\webapi\resolver\query\mycoursesitems as mycoursesitems;

//require_once($CFG->dirroot.'/user/profile/lib.php');

class update_cache extends \core\task\scheduled_task {

    /**
     * Return the task's name as shown in admin screens.
     *
     * @return string
     */
    public function get_name() {
        return 'Actualizar caché';
    }

    public function execute() {
        global $DB;
        mtrace('##################Update dlservices caché start##################');
        $cache = \cache::make('local_dlservices', 'moreviewcourses');
        $cache->purge();
        try {
            items::get_moreviews_rank();
            moreviewsitems::get_all_moreviewcourses(10);
            mycoursesitems::get_moreviews_rank();
        } catch (Exception $e) {
            mtrace('Ocurrió un error al actualizar los datos');
            mtrace($e->getMessage());
        }
        mtrace('##################Update dlservices caché task end####################');
    }

}
