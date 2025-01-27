<?php

defined('MOODLE_INTERNAL') || die();

use local_isycredentials\credential\credential;
use local_isycredentials\dss_signing_service;
use local_isycredentials\edci_issuer_signing_service;

/**
 * Signs a document using the configured signing service.
 *
 * @param string $document The document to sign.
 * @param string $service_type The type of signing service to use ('dss' or 'edci').
 * @return string The signed document data.
 * @throws Exception If any error occurs during the signing process.
 */
function local_isycredentials_sign_document(string $document, string $service_type = 'edci'): string {
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
        $signing_service = new dss_signing_service($certificate, $certificate_password);
    } else if ($service_type === 'edci') {
        $signing_service = new edci_issuer_signing_service($certificate_password);
    } else {
        throw new Exception('Error: Invalid service type specified.');
    }

    return $signing_service->sign($document);
}

/**
 * Creates a credential document based on the given badge id and user id and then signs it
 * @param int $badgeid the id of the badge that is the basis for the credential
 * @param int $userid the id of the user that is awarded with the badge
 * @param bool $withDeliveryDetails if true, the credential will be wrapped in a object with a key 'credential' and a key 'deliveryDetails'.
 * @return string returns the signed credential json document.
 * @throws Exception if badge or user not found.
 */
function local_isycredentials_create_credential_from_badge(int $badgeid, int $userid, bool $withDeliveryDetails = false): string {
    global $DB;

    // Fetch badge data
    $badge = $DB->get_record('badge', ['id' => $badgeid], '*', MUST_EXIST);
    // Fetch User data
    $user = \core_user::get_user($userid);

    if (!$user) {
        throw new Exception('User not found.');
    }

    $credential = credential::fromBadge($badge, $user);

    if ($withDeliveryDetails) {
        $credential = [
            'credential' => $credential->toArray(),
            'deliveryDetails' => [
                'deliveryAddress' => [
                    $user->email
                ]
            ]
        ];
    } else {
        $credential = $credential->toArray();
    }

    return json_encode($credential, JSON_UNESCAPED_SLASHES, JSON_UNESCAPED_UNICODE);
}
