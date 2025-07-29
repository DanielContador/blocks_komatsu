<?php

defined('MOODLE_INTERNAL') || die();

$THEME->doctype = 'html5';
$THEME->name = 'dlcourseflix';
$THEME->parents = ['ventura', 'legacy', 'base'];
$THEME->enable_dock = true;
// $THEME->enable_hide = true;
$THEME->sheets = ['totara'];
$THEME->minify_css = false;
// $THEME->yuicssmodules = array();
// $THEME->requiredblocks = '';
// $THEME->addblockposition = BLOCK_ADDBLOCK_POSITION_DEFAULT;
$THEME->rendererfactory = 'theme_overridden_renderer_factory';
// $THEME->csspostprocess = 'theme_dlcourseflix_process_css';

$THEME->larrow = '<';
$THEME->rarrow = '>';

$THEME->parents_exclude_sheets = [
    'base' => ['flexible-icons'],
];

$THEME->layouts = array(
    'login'               => array(
        'file'    => 'login.php',
        'regions' => array(),
        'options' => array('langmenu' => true),
    ),
    'dashboard'           => array(
        'file'          => 'dashboard.php',
        'regions'       => array('top'),
        'defaultregion' => 'top',
    ),
    'catalog'             => array(
        'file'          => 'catalog.php',
        'regions'       => array('top'),
        'defaultregion' => 'top',
        'options'       => array('search_box' => 'js'),
    ),
    'coursecategory'      => array(
        'file'          => 'default1.php',
        'regions'       => array('top'),
        'defaultregion' => 'top',
        'options'       => array('breadcrumb' => false, 'search_box' => 'js'),
    ),
    'course'              => array(
        'file'          => 'internal.php',
        'regions'       => array('top', 'bottom', 'side-post'),
        'defaultregion' => 'side-post',
        'options'       => array('langmenu' => true),
    ),
    'incourse'            => array(
        'file'          => 'columns2.php',
        'regions'       => array('top', 'bottom', 'side-post',),
        'defaultregion' => 'side-post',
        'options'       => array('breadcrumb' => true),
    ),
    'dlscormview'         => array(
        'file'          => 'columns2.php',
        'regions'       => array('top', 'side-post'),
        'defaultregion' => 'side-post',
        'options'       => array('langmenu' => true, 'breadcrumb' => true),
    ),
    'dlscormplayer'       => array(
        'file'          => 'nobanner.php',
        'regions'       => array('top', 'bottom', 'side-post'),
        'defaultregion' => 'side-post',
        'options'       => array('langmenu' => true),
    ),
    'dlscormreport'       => array(
        'file'          => 'nobanner.php',
        'regions'       => array('top', 'bottom', 'side-post'),
        'defaultregion' => 'side-post',
        'options'       => array('langmenu' => true),
    ),
    'dlcompetencydetails' => array(
        'file'          => 'nobanner.php',
        'regions'       => array('top', 'bottom', 'side-post'),
        'defaultregion' => 'side-post',
        'options'       => array('langmenu' => true),
    ),
    'program'             => array(
        'file'          => 'internal.php',
        'regions'       => array('top', 'bottom', 'side-post'),
        'defaultregion' => 'side-post',
        'options'       => array('langmenu' => true),
    ),
    'selfenrol'           => array(
        'file'          => 'default2.php',
        'regions'       => array('bottom'),
        'defaultregion' => 'bottom',
        'options'       => array('langmenu' => true, 'breadcrumb' => false),
    ),
    'dlcompetencies'      => array(
        'file'          => 'columns2.php',
        'regions'       => array('main', 'content', 'side-post'),
        'defaultregion' => 'side-post',
        'options'       => array('langmenu' => true, 'breadcrumb' => true),
    ),
    'dlglossary'          => array(
        'file'          => 'columns2.php',
        'regions'       => array('top', 'bottom', 'side-post'),
        'defaultregion' => 'side-post',
        'options'       => array('langmenu' => true, 'breadcrumb' => true),
    ),
    'mypublic'            => array(
        'file'          => 'columns2.php',
        'regions'       => array('top', 'bottom', 'main', 'side-post'),
        'defaultregion' => 'main',
        'options'       => array('langmenu' => true, 'breadcrumb' => true),
    ),
    'profile'             => array(
        'file'          => 'columns2.php',
        'regions'       => array('top', 'bottom', 'main', 'side-post'),
        'defaultregion' => 'side-post',
        'options'       => array('langmenu' => true, 'breadcrumb' => true),
    ),
    'dlreport'            => array(
        'file'          => 'default1.php',
        'regions'       => array('top'),
        'defaultregion' => 'top',
        'options'       => array('langmenu' => true, 'breadcrumb' => true),
    ),
    'dljob'               => array(
        'file'          => 'columns2.php',
        'regions'       => array('top', 'bottom', 'side-post'),
        'defaultregion' => 'side-post',
        'options'       => array('langmenu' => true, 'breadcrumb' => true),
    ),
    'dlquiz'              => array(
        'file'          => 'columns2.php',
        'regions'       => array('top', 'main', 'bottom', 'side-post'),
        'defaultregion' => 'side-post',
        'options'       => array('langmenu' => true, 'breadcrumb' => true),
    ),
    'headerImage'         => array(
        'file'          => 'headerImage.php',
        'regions'       => array('top', 'bottom'),
        'defaultregion' => 'top',
        'options'       => array('breadcrumb' => false, 'search_box' => 'js'),
    ),
);



// //     // ...other layouts...

// $THEME->release = '1.0';
// $THEME->maturity = MATURITY_STABLE;
// $THEME->dependencies = [];
