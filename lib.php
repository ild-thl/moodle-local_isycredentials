<?php

defined('MOODLE_INTERNAL') || die();

use local_isycredentials\credential\address;
use local_isycredentials\credential\awarding_body;
use local_isycredentials\credential\awarding_process;
use local_isycredentials\credential\credential;
use local_isycredentials\credential\credential_subject;
use local_isycredentials\credential\display_parameter;
use local_isycredentials\credential\individual_display;
use local_isycredentials\credential\issuer;
use local_isycredentials\credential\legal_identifier;
use local_isycredentials\credential\learning_activity;
use local_isycredentials\credential\concept;
use local_isycredentials\credential\concept_scheme;
use local_isycredentials\credential\localized_string;
use local_isycredentials\credential\language_mapping;
use local_isycredentials\credential\language_mapping_key;
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

        // Get the mapped language data
        $iso6392tLanguage = language_mapping::getMappedData($badge->language, language_mapping_key::ISO6392T);
        $languageLiterals = language_mapping::getMappedData($badge->language, language_mapping_key::LITERALS);

        // Create language concept
        $languageConcept = new concept(
            "http://publications.europa.eu/resource/authority/language/{$iso6392tLanguage}",
            new localized_string($languageLiterals),
            'language',
            new concept_scheme('http://publications.europa.eu/resource/authority/language')
        );

        // Create concept for country code
        $countryCodeConcept = new concept(
            'http://publications.europa.eu/resource/authority/country/DEU',
            new localized_string(['de' => ['Deutschland'], 'en' => ['Germany']]),
            'country',
            new concept_scheme('http://publications.europa.eu/resource/authority/country')
        ); // TODO get the real country of the issuer address

        // Create address
        $address = new address(
            '1',
            $countryCodeConcept,
            'Goseriede 9, 30159 Hannover',
        );

        //Create legal identifier
        $legalIdentifier = new legal_identifier(
            '1',
            'DUMMY-LEGAL-IDENTIFIER', ## TODO get a real legal identifier
            $countryCodeConcept
        );

        // create awarding body
        $awarding_body = new awarding_body(
            '1',
            $address,
            'OrgLegalName', // TODO get the issuer name from the badge
            null,
            $legalIdentifier
        );

        // create awarding process
        $awardingProcess = new awarding_process(
            '1',
            [$awarding_body]
        );

        // Create the claims
        // TODO (Based on the badge criteria) get the completed courses and create learning activities for each course 
        $claims =  [
            learning_activity::fromCourse(
                '1',
                ['fullname' => 'Course Name', 'summary' => 'Course Description'],
                $awardingProcess
            )
        ];

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
            [$languageConcept],
            [$individualDisplay],
            $languageConcept,
            new localized_string($badge->name),
        );

        // create issuer
        $issuer = new issuer(
            $badge->issuerurl,
            $address,
            $badge->issuername,
            $badge->issuercontact,
        );

        $credential = new credential(
            $credentialSubject,
            $issuer,
            $displayParameter,
            $badge_issued->dateissued,
            $badge_issued->dateissued,
            $badge_issued->dateissued,
            $badge->expiredate,
            $badge->expiredate,
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
