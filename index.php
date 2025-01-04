<?php

require_once('../../config.php');

require_login();
require_capability('local/isycredentials:view', context_system::instance());

$PAGE->set_url('/local/isycredentials/index.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('pluginname', 'local_isycredentials'));
$PAGE->set_heading(get_string('pluginname', 'local_isycredentials'));

echo $OUTPUT->header();

echo '<p>' . get_string('description', 'local_isycredentials') . '</p>';
echo '<p><a href="sign.php">' . get_string('sign_document', 'local_isycredentials') . '</a></p>';

echo $OUTPUT->footer();
