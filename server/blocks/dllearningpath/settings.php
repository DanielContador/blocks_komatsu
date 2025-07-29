<?php
/**
 * Adds admin settings for the plugin.
 *
 * @package     local_helloworld
 * @category    admin
 * @copyright   2020 Your Name <email@example.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$listpath = new admin_externalpage('path_list', get_string('pluginname', 'block_dllearningpath'),
    new moodle_url('/blocks/dllearningpath/list_learning_path.php'), 'moodle/site:config');
//$admin_category->add('block_dllearningpath', $addstage);

//$settingspage = $listpath;
$settings = $listpath;

