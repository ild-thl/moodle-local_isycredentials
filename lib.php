<?php
require_once(__DIR__ . '/classes/certificate_manager.php');
require_once(__DIR__ . '/classes/signing_service_interface.php');
require_once(__DIR__ . '/classes/dss_signing_service.php');
require_once(__DIR__ . '/classes/edci_issuer_signing_service.php');

/**
 * Signs a document using the configured signing service.
 *
 * @param string $document The document to sign.
 * @param string $service_type The type of signing service to use ('dss' or 'edci').
 * @return string The signed document data.
 * @throws Exception If any error occurs during the signing process.
 */
function local_isycredentials_sign_document(string $document, string $service_type): string {
    $certificate_password = get_config('local_isycredentials', 'certificate_password');
    if (empty($certificate_password)) {
        throw new Exception('Error: Plugin settings are not set. Please set the needed values in Site Administration.');
    }
    $fs = get_file_storage();
    $context = context_system::instance();
    $files = $fs->get_area_files($context->id, 'local_isycredentials', 'certificate_file', 0, 'itemid, filepath, filename', false);

    if (empty($files)) {
        throw new Exception('Error: Certificate file not found.');
    }

    $certificate_file = reset($files);
    $certificate = $certificate_file->get_content();

    if ($service_type === 'dss') {
        $signing_service = new local_isycredentials\dss_signing_service($certificate, $certificate_password);
    } else if ($service_type === 'edci') {
        $signing_service = new local_isycredentials\edci_issuer_signing_service($certificate_password);
    } else {
        throw new Exception('Error: Invalid service type specified.');
    }

    return $signing_service->sign($document);
}
