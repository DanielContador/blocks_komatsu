<?php
defined('MOODLE_INTERNAL') || die();

$PAGE->set_popup_notification_allowed(false);
$PAGE->requires->css(new moodle_url('/theme/dlcourseflix/style/login.css'));
$themerenderer = $PAGE->get_renderer('theme_dlcourseflix');

echo $OUTPUT->doctype();
?>
<html <?php echo $OUTPUT->htmlattributes(); ?>>
<head>
    <title><?php echo $OUTPUT->page_title(); ?></title>
    <link rel="shortcut icon" href="<?php echo $OUTPUT->favicon(); ?>"/>
    <?php echo $OUTPUT->standard_head_html(); ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimal-ui">
    <link rel="stylesheet" type="text/css" href="<?php echo $CFG->wwwroot; ?>/theme/dlcourseflix/style/styles.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $CFG->wwwroot; ?>/theme/dlcourseflix/style/block_dlfrontuserinfo.css">
</head>
<body <?php echo $OUTPUT->body_attributes(['dl-dark-mode']); ?>>
<?php echo $OUTPUT->standard_top_of_body_html(); ?>

<!-- Main content -->
<div id="page">
    <div id="page-login-content" class="row h-100">
        <div id="region-main" class="<?php echo $themerenderer->main_content_classes() . " h-100"; ?>">
            <img id="dl-login-logo"
                 src="<?php echo $themerenderer->get_white_logo_url() ?>" alt="Logo-Image">
            <?php echo $themerenderer->course_content_header(); ?>
            <?php echo $themerenderer->main_content(); ?>
            <?php echo $themerenderer->course_content_footer(); ?>
        </div>
    </div>
</div>

<?php echo $themerenderer->dl_footer(); ?>

<?php echo $OUTPUT->standard_end_of_body_html(); ?>
</body>
</html>
