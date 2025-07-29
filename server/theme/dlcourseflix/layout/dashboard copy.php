<?php
defined('MOODLE_INTERNAL') || die();

$PAGE->set_popup_notification_allowed(false);

echo $OUTPUT->doctype();
?>
<html <?php echo $OUTPUT->htmlattributes(); ?>>
<head>
    <title><?php echo $OUTPUT->page_title(); ?></title>
    <?php echo $OUTPUT->standard_head_html(); ?>
    <link rel="stylesheet" type="text/css" href="<?php echo $CFG->wwwroot; ?>/theme/dlcourseflix/style/styles.css">
</head>
<body <?php echo $OUTPUT->body_attributes(); ?>>
<?php echo $OUTPUT->standard_top_of_body_html(); ?>

<!-- Main navigation -->
<?php
$themerenderer = $PAGE->get_renderer('theme_legacy');
$totara_core_renderer = $PAGE->get_renderer('totara_core');
$hasguestlangmenu = (!isset($PAGE->layout_options['langmenu']) || $PAGE->layout_options['langmenu'] );
$nocustommenu = !empty($PAGE->layout_options['nocustommenu']);

?>

<!-- Main content -->
<div id="page" class="container-fluid">
    <div id="page-content" class="row">
        <div id="region-top" class="col-12">
            <?php echo $OUTPUT->main_content() ?>
        </div>
    </div>
</div>

<?php echo $OUTPUT->standard_end_of_body_html(); ?>
</body>
</html>
