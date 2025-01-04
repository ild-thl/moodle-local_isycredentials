<?php
require_once('../../config.php');
require_once('lib.php'); // Include our plugin's library
require_login();
require_capability('local/isycredentials:sign', context_system::instance());

if (isset($_FILES['json_file']) && $_FILES['json_file']['error'] == 0) {
    $file_data = file_get_contents($_FILES['json_file']['tmp_name']);
    $service_type = optional_param('service_type', 'edci', PARAM_ALPHA);

    try {
        $signed_document = local_isycredentials_sign_document($file_data, $service_type);
        local_isycredentials_display_signed_document($signed_document);
    } catch (Exception $e) {
        echo $OUTPUT->notification($e->getMessage(), 'error');
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['signbadgedemo'])) {
    try {
        $to_sign_document = local_isycredentials_create_credential_from_badge(1, 6, true);

        // header('Content-Type: application/json');
        // header('Charset: utf-8');
        // echo $to_sign_document;
        $signed_document = local_isycredentials_sign_document($to_sign_document, 'edci');
        local_isycredentials_display_signed_document($signed_document);
    } catch (Exception $e) {
        echo $OUTPUT->notification($e->getMessage(), 'error');
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['download']) && isset($_POST['signed_document'])) {
    // Trigger download of the signed document
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="signed_document.jsonld"');
    echo $_POST['signed_document'];
} else {
    $PAGE->set_url('/local/isycredentials/sign.php');
    $PAGE->set_context(context_system::instance());
    $PAGE->set_title(get_string('sign_document', 'local_isycredentials'));
    $PAGE->set_heading(get_string('sign_document', 'local_isycredentials'));

    echo $OUTPUT->header();

    // Upload form
    echo '<form method="post" enctype="multipart/form-data">';
    echo '<input type="file" name="json_file" accept="application/json">';
    echo '<select name="service_type">';
    echo '<option value="dss">' . get_string('dss_service', 'local_isycredentials') . '</option>';
    echo '<option value="edci">' . get_string('edci_service', 'local_isycredentials') . '</option>';
    echo '</select>';
    echo '<input type="submit" value="' . get_string('upload', 'local_isycredentials') . '">';
    echo '</form>';

    echo '</br>';

    // Demo button
    echo '<form method="get">';
    echo '<input type="submit" name="signbadgedemo" value="Sign Badge Demo">';
    echo '</form>';

    echo $OUTPUT->footer();
}

function local_isycredentials_display_signed_document(string $signed_document): void {
    global $PAGE, $OUTPUT;

    $PAGE->set_url('/local/isycredentials/sign.php');
    $PAGE->set_context(context_system::instance());
    $PAGE->set_title(get_string('signed_document', 'local_isycredentials'));
    $PAGE->set_heading(get_string('signed_document', 'local_isycredentials'));

    echo $OUTPUT->header();

    // Download button
    echo '<form method="post">';
    echo '<input type="submit" name="download" value="' . get_string('download', 'local_isycredentials') . '">';
    echo '<input type="hidden" name="signed_document" value="' . htmlspecialchars($signed_document) . '">';
    echo '</form>';

    // Display the signed documents display details
    $signed_document_json = json_decode($signed_document, true);
    if (isset($signed_document_json['payload'])) {
        $payload_json = json_decode($signed_document_json['payload'], true);
        if (isset($payload_json['displayParameter']['individualDisplay'])) {
            $individual_display = [];
            if (isset($payload_json['displayParameter']['individualDisplay']['displayDetail'])) {
                $individual_display = $payload_json['displayParameter']['individualDisplay'];
            } else {
                $individual_display = reset($payload_json['displayParameter']['individualDisplay']);
            }

            if ($individual_display && isset($individual_display['displayDetail'])) {
                $pages = [];
                if (isset($individual_display['displayDetail']['image'])) {
                    $pages = [$individual_display['displayDetail']];
                } else {
                    foreach ($individual_display['displayDetail'] as $detail) {
                        if (isset($detail['image'])) {
                            $pages[] = $detail;
                        }
                    }
                }

                echo '<div class="container mt-4">';
                echo '<div class="multi-page-document">';
                foreach ($pages as $index => $page) {
                    echo '<div class="page card mb-4">';
                    echo '<div class="card-header">';
                    echo '<h2 class="card-title">Page ' . ($index + 1) . '</h2>';
                    echo '</div>';
                    echo '<div class="card-body d-flex justify-content-center align-items-center">';
                    if (isset($page['image']['content'])) {
                        $image_data = base64_decode($page['image']['content']);
                        $content_type = 'image/jpeg'; // Default to JPEG

                        if (isset($page['image']['contentType']['id'])) {
                            $content_type_id = $page['image']['contentType']['id'];
                            if ($content_type_id === 'http://publications.europa.eu/resource/authority/file-type/PNG') {
                                $content_type = 'image/png';
                            } else if ($content_type_id === 'http://publications.europa.eu/resource/authority/file-type/JPEG') {
                                $content_type = 'image/jpeg';
                            }
                        }

                        $image_src = 'data:' . $content_type . ';base64,' . base64_encode($image_data);
                        echo '<img src="' . $image_src . '" class="img-fluid" alt="Credential Image">';
                    }
                    echo '</div>';
                    echo '</div>';
                }
                echo '</div>';
                echo '</div>';
            }
        }
    }

    echo $OUTPUT->footer();
}
