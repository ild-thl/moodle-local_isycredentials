<?php

defined('MOODLE_INTERNAL') || die();

use local_isycredentials\credential\address;
use local_isycredentials\credential\concept\language_concept;
use local_isycredentials\credential\concept\country_concept;
use local_isycredentials\credential\credential;
use local_isycredentials\credential\credential_subject;
use local_isycredentials\credential\display_parameter;
use local_isycredentials\credential\email_address;
use local_isycredentials\credential\individual_display;
use local_isycredentials\credential\legal_identifier;
use local_isycredentials\credential\learning_activity;
use local_isycredentials\credential\localized_string;
use local_isycredentials\credential\organisation;
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
 * @throws Exception If any error occurs during the credential creation or signing process.
 */
function local_isycredentials_create_credential_from_badge(int $badgeid, int $userid, bool $withDeliveryDetails = false): string {
    global $DB;

    try {
        // Fetch badge data
        $badge = $DB->get_record('badge', ['id' => $badgeid], '*', MUST_EXIST);
        $badge_issued = $DB->get_record('badge_issued', ['badgeid' => $badgeid, 'userid' => $userid], '*', MUST_EXIST);

        // Fetch User data
        $user = \core_user::get_user($userid);

        if (!$user) {
            throw new Exception('User not found.');
        }

        // Set the primary language
        localized_string::setPrimaryLanguage($badge->language);
        localized_string::setLanguageRestrictions(array_unique([$badge->language, 'de', 'en']));

        // Create language concept
        $languageConcept = language_concept::getByCode($badge->language);
        if (!$languageConcept) {
            debugging("Language concept not found for language code: " . $badge->language, DEBUG_DEVELOPER);
            $languageConcept = language_concept::EN();
            localized_string::setPrimaryLanguage('en');
        }

        // Get country code and name from settings
        $countryCode = get_config('local_isycredentials', 'awarding_body_address_country_code');
        $countryNameJson = get_config('local_isycredentials', 'awarding_body_address_country_name');
        $countryName = json_decode($countryNameJson, true);

        // Create concept for country code
        $countryConcept = new country_concept(
            "http://publications.europa.eu/resource/authority/country/{$countryCode}",
            $countryName,
            $countryCode
        );

        // Create address
        $address = new address(
            '1',
            $countryConcept,
            get_config('local_isycredentials', 'awarding_body_address')
        );

        // Create legal identifier
        $legalIdentifier = new legal_identifier(
            '1',
            get_config('local_isycredentials', 'awarding_body_legal_identifier'),
            $countryConcept
        );

        // Create awarding body
        $awarding_body = new organisation(
            '1',
            $address,
            get_config('local_isycredentials', 'awarding_body_legal_name'),
            $legalIdentifier,
            get_config('local_isycredentials', 'awarding_body_email'),
        );
        $awarding_body->withLegalIdentifier($legalIdentifier);
        $awarding_body->withEmail(new email_address(get_config('local_isycredentials', 'awarding_body_email')));

        // Create the claims for the credential based on the badge criteria
        // Currently only supports course completion criteria
        // TODO Add support for other criteria types
        $sql = "SELECT bcp.value FROM {badge_criteria} bc
                JOIN {badge_criteria_param} bcp ON bc.id = bcp.critid
                WHERE bc.badgeid = :badgeid AND bc.criteriatype = 5";

        $params = ['badgeid' => $badge->id];
        $courseids = $DB->get_records_sql($sql, $params);

        if (empty($courseids)) {
            throw new Exception('No completed courses found for the badge.');
        }

        $courseids = array_map(function ($course) {
            return $course->value;
        }, $courseids);

        $claims = [];
        $claimid = 1;
        foreach ($courseids as $courseid) {
            $course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
            $claims[] = learning_activity::fromCourse(
                $claimid++,
                ['fullname' => $course->fullname, 'summary' => $course->summary],
                $awarding_body
            );
        }

        // Create credential subject
        $credentialSubject = new credential_subject(
            '1',
            $user->firstname,
            $user->lastname,
            \core_user::get_fullname($user),
            $claims
        );

        //Create Individual display showing the badge image
        $individualDisplay = new individual_display(
            $languageConcept,
            $badge,
        );

        // Create Display Parameter
        $displayParameter = new display_parameter(
            '1',
            $languageConcept,
            [$individualDisplay],
            $languageConcept,
            new localized_string($badge->name),
        );

        $credential = new credential(
            $credentialSubject,
            $displayParameter,
            $badge_issued->dateissued,
            $badge_issued->dateexpire,
            $badge_issued->dateexpire,
        );

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
    } catch (Exception $e) {
        debugging("Error creating credential: " . $e->getMessage(), DEBUG_DEVELOPER);
        throw new Exception("Error creating credential: " . ' ' . $e->getMessage());
    }
}
