<?php
defined('MOODLE_INTERNAL') || die();

$PAGE->set_popup_notification_allowed(false);
$PAGE->requires->css(new moodle_url('/theme/dlcourseflix/style/lucide_icons.css'));
$themerenderer = $PAGE->get_renderer('theme_dlcourseflix');
$full_header = $themerenderer->full_header();
echo $OUTPUT->doctype();
?>
<html <?php echo $OUTPUT->htmlattributes(); ?>>
<head>
    <title><?php echo $OUTPUT->page_title(); ?></title>
    <link rel="shortcut icon" href="<?php echo $OUTPUT->favicon(); ?>" />
    <?php echo $OUTPUT->standard_head_html(); ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimal-ui">
    <link rel="stylesheet" type="text/css" href="<?php echo $CFG->wwwroot; ?>/theme/dlcourseflix/style/styles.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $CFG->wwwroot; ?>/theme/dlcourseflix/style/block_dlfrontuserinfo.css">
</head>
<body <?php echo $OUTPUT->body_attributes(['dl-dark-mode', !has_capability('moodle/site:config', context_system::instance()) ? 'no-show-breadcrumb-button' : '']); ?>>
<?php echo $OUTPUT->standard_top_of_body_html(); ?>

<!-- Main navigation -->
<?php
$totara_core_renderer = $PAGE->get_renderer('totara_core');
$hasguestlangmenu = (!isset($PAGE->layout_options['langmenu']) || $PAGE->layout_options['langmenu'] );
$nocustommenu = !empty($PAGE->layout_options['nocustommenu']);
echo $totara_core_renderer->masthead($hasguestlangmenu, $nocustommenu);

?>

<?php if ($full_header !== '') { ?>
<!-- Breadcrumb and edit buttons -->
<div class="container-fluid breadcrumb-container">
    <div class="row">
        <div class="col-sm-12">
            <?php echo $full_header; ?>
        </div>
    </div>
</div>
<?php } ?>

<!-- Main content -->
<div id="page" class="container-fluid">
    <div id="page-content" class="row">


        <?php echo $themerenderer->blocks_top(); ?>
        <div class="row">
            <div id="region-main" class="<?php echo $themerenderer->main_content_classes(); ?>">
                <?php echo $themerenderer->course_content_header(); ?>
                <?php echo $themerenderer->main_content(); ?>
                <?php echo $themerenderer->course_content_footer(); ?>
            </div>
        </div>
    </div>
</div>

<?php echo $themerenderer->dl_footer(); ?>

<?php echo $OUTPUT->standard_end_of_body_html(); ?>
</body>
</html>
