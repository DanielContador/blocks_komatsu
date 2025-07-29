<?php

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir.'/adminlib.php');

require_login();

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url('/theme/dlcourseflix/extrasettings.php');
$PAGE->set_pagelayout('admin');
$PAGE->set_pagetype('admin-setting-themedlcourseflixadditionalsettings');

$PAGE->set_title("$SITE->shortname: ".get_string('dlcourseflixthemeextrasettings', 'theme_dlcourseflix'));
$PAGE->set_heading("$SITE->shortname: ".get_string('dlcourseflixthemeextrasettings', 'theme_dlcourseflix'));

echo $OUTPUT->header();

// Load theme config for theme.
$theme_config = \theme_config::load('dlcourseflix');

// Save settings.
$theme_settings = new theme_dlcourseflix_additionalsettings($theme_config, 0);
$currentdata = $theme_settings->get_rawsettings();

$form = new \theme_dlcourseflix\form\additionalsettings_form($currentdata);

if ($formdata = $form->get_data()) {
    if(isset($formdata->tenantid)) {
        $tenantid = $formdata->tenantid;
        // $theme_settings = new theme_dl_additionalsettings($theme_config, $tenantid);
        $theme_settings->set_tenantid($tenantid);
        $theme_settings->update_categories($formdata);

        $itemid = $theme_settings->get_itemid();
        if (($files = $form->get_files()) && count($form->get_files()->{"tenant_{$tenantid}_footerlogo_filemanager"}) == 1) {
            $file = $files->{"tenant_{$tenantid}_footerlogo_filemanager"}[0];
            $form->save_stored_file(
                "tenant_{$tenantid}_footerlogo_filemanager",
                $context->id,
                'theme_dlcourseflix',
                'footerlogo',
                $itemid,
                '/',
                "tenant_{$tenantid}_footerimage.png",
                true, 
                $USER->id
            );
        }
        else {
            $fs = get_file_storage();
            $fs->delete_area_files(
                $context->id,
                'theme_dlcourseflix',
                'footerlogo',
                $itemid
            );
        }
    }  
}

echo $form->render();

echo $OUTPUT->footer();

       