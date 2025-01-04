<?php

defined('MOODLE_INTERNAL') || die();

use local_isycredentials\credential\address;
use local_isycredentials\credential\awarding_process;
use local_isycredentials\credential\credential;
use local_isycredentials\credential\credential_subject;
use local_isycredentials\credential\display_parameter;
use local_isycredentials\credential\individual_display;
use local_isycredentials\credential\issuer;
use local_isycredentials\credential\location;
use local_isycredentials\credential\organisation;
use local_isycredentials\credential\legal_identifier;
use local_isycredentials\credential\learning_activity;
use local_isycredentials\credential\concept;
use local_isycredentials\credential\concept_scheme;
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
        $user = $DB->get_record('user', ['id' => $userid], '*', MUST_EXIST);

        // Create language concept
        $languageConcept = concept::from(
            'http://publications.europa.eu/resource/authority/language/DEU',
            ['de' => ['Deutsch']],
            'language',
            new concept_scheme('http://publications.europa.eu/resource/authority/language')
        ); // TODO get the real language from the badge

        // Create concept for country code
        $countryCodeConcept = concept::from(
            'http://publications.europa.eu/resource/authority/country/DEU',
            ['de' => ['Deutschland']],
            'country',
            new concept_scheme('http://publications.europa.eu/resource/authority/country')
        ); // TODO get the real country of the issuer address

        // Create address
        $address = address::from(
            '1',
            $countryCodeConcept,
            ['de' => ["Goseriede 9, 30159 Hannover"]],
        );
        // Create location
        $location = location::from(
            '1',
            [$address],
        );

        //Create legal identifier
        $legalIdentifier = legal_identifier::from(
            '1',
            'DUMMY-LEGAL-IDENTIFIER', ## TODO get a real legal identifier
            $countryCodeConcept
        );
        // create organisation
        $organisation = Organisation::from(
            '1',
            [$location],
            ['de' => ['OrgLegalName']], // TODO get the issuer name from the badge
            $legalIdentifier
        );

        // create awarding process
        $awardingProcess = awarding_process::from(
            '1',
            [$organisation]
        );

        // Create learning activity
        // TODO (Based on the badge criteria) get the completed courses and create learning activities for each course
        $learningActivity = learning_activity::fromCourse(
            '1',
            ['fullname' => 'Course Name', 'summary' => 'Course Description'],
            $awardingProcess
        );

        // Create credential subject
        $credentialSubject = credential_subject::from(
            '1',
            [$badge->language => [$user->firstname]],
            [$badge->language => [$user->lastname]],
            [$learningActivity]
        );


        //Create Individual display
        $individualDisplay = individual_display::from(
            $languageConcept,
            $badge,
        );
        // Create Display Parameter
        $displayParameter = display_parameter::from(
            '1',
            [$languageConcept],
            [$individualDisplay],
            $languageConcept,
            ['de' => ['Teilnahmebescheinigung']]
        );
        // create issuer
        $issuer = issuer::from(
            'http://example.org/issuer565049',
            [$location],
            ['de' => $badge->issuername]
        );

        $credential = credential::from(
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
