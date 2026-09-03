<?php

defined('MOODLE_INTERNAL') || die();

use local_isycredentials\credential\credential;
use local_isycredentials\package_csc_signing_service;
use local_isycredentials\csc\profile_repository;

/**
 * Signs a document using the configured signing service.
 *
 * @param string $document The document to sign.
 * @param string $service_type The signing service type. Only 'csc' is supported.
 * @return string The signed document data.
 * @throws Exception If any error occurs during the signing process.
 */
function local_isycredentials_sign_document(string $document, string $service_type = 'csc'): string {
    if ($service_type !== 'csc') {
        throw new moodle_exception('csc_only_signing_service', 'local_isycredentials');
    }
    $document_data = json_decode($document, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($document_data)) {
        throw new moodle_exception('csc_invalid_document', 'local_isycredentials');
    }

    $issuer = json_decode(get_config('local_isycredentials', 'elm_issuer_data'), true);
    if (!is_array($issuer) || empty($issuer['id']) || !is_string($issuer['id'])) {
        throw new moodle_exception('csc_invalid_issuer', 'local_isycredentials');
    }
    if (isset($document_data['issuer']['id'])
            && $document_data['issuer']['id'] !== $issuer['id']) {
        throw new moodle_exception(
            'csc_issuer_mismatch',
            'local_isycredentials',
            '',
            [$document_data['issuer']['id'], $issuer['id']]
        );
    }
    $profile = profile_repository::for_issuer($issuer['id']);
    $signing_service = new package_csc_signing_service($profile, $issuer);

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
