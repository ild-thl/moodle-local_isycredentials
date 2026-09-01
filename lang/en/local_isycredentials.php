<?php

$string['pluginname'] = 'ISy Credentials';
$string['description'] = 'This plugin allows you to sign credentials using an external signing service.';
$string['sign_document'] = 'Sign Document';
$string['signed_document'] = 'Signed Document';
$string['upload'] = 'Upload JSON File';
$string['download'] = 'Download Signed Credential';
$string['dss_service'] = 'DSS Service';
$string['edci_service'] = 'EDCI Service';
$string['csc_service'] = 'CSC Service';

$string['isycredentials:sign'] = 'Sign credentials';

// Admin settings
$string['dss_signing_service_url'] = 'DSS URL';
$string['dss_signing_service_url_desc'] = 'The URL of the external Digital Signature Service (DSS).';
$string['edci_signing_service_url'] = 'EDCI-Issuer URL';
$string['edci_signing_service_url_desc'] = 'The URL of the European Digital Credential Infrastructure (EDCI) Issuer.';
$string['certificate_file'] = 'Certificate file';
$string['certificate_file_desc'] = 'Upload the certificate file here.';
$string['certificate_password'] = 'Certificate Password';
$string['certificate_password_desc'] = 'The password for the .p12 certificate file.';
$string['csc_issuer_profiles'] = 'CSC issuer profiles';
$string['csc_issuer_profiles_desc'] = 'JSON map from issuer ID to CSC profile. Include the credential_id and client_id. Store only environment-variable names for the client secret and TLS certificate/key paths.';
$string['csc_request_failed'] = 'CSC request failed.';
$string['csc_invalid_response'] = 'CSC returned an invalid response.';
$string['csc_missing_certificate'] = 'CSC did not return a signing certificate.';
$string['csc_empty_data'] = 'No data was provided for CSC signing.';
$string['csc_missing_request_id'] = 'CSC did not return a signing request ID.';
$string['csc_missing_authorization_code'] = 'CSC did not return an authorization code.';
$string['csc_missing_access_token'] = 'CSC did not return an access token.';
$string['csc_missing_signature'] = 'CSC did not return a signature.';
$string['csc_missing_timestamp'] = 'CSC did not return a timestamp token.';
$string['csc_missing_secret'] = 'The required CSC secret environment variable is unavailable: {$a}.';
$string['csc_invalid_profile'] = 'The CSC issuer profile is missing or has an invalid field: {$a}.';
$string['csc_poll_timeout'] = 'CSC did not complete asynchronous signing before the polling limit was reached: {$a}.';
$string['csc_profile_not_found'] = 'No CSC issuer profile is configured for issuer: {$a}.';
$string['csc_invalid_issuer'] = 'The configured issuer does not contain a valid ID.';
$string['csc_unsupported_key_type'] = 'The CSC credential certificate uses an unsupported key type.';
$string['elm_issuer_data'] = 'ELM Issuer Data';
$string['elm_issuer_data_desc'] = 'JSON data for the ELM issuer to be used in the credential. This has to match the signing certificates information.';
$string['awarding_body_address'] = 'Awarding Body Address';
$string['awarding_body_address_desc'] = 'The address of the awarding body.';
$string['awarding_body_address_country_code'] = 'Awarding Body Address Country Code';
$string['awarding_body_address_country_code_desc'] = 'The 3-letter ISO 639-2/T country code of the awarding body address.';
$string['awarding_body_address_country_name'] = 'Awarding Body Address Country Name';
$string['awarding_body_address_country_name_desc'] = 'The name of the country in different languages (JSON format).';
$string['awarding_body_legal_identifier'] = 'Awarding Body Legal Identifier';
$string['awarding_body_legal_identifier_desc'] = 'The legal identifier of the awarding body.';
$string['awarding_body_legal_name'] = 'Awarding Body Legal Name';
$string['awarding_body_legal_name_desc'] = 'The legal name of the awarding body.';
$string['awarding_body_email'] = 'Awarding Body Email';
$string['awarding_body_email_desc'] = 'The email of the awarding body (optional).';
$string['badge_image_missing'] = 'No image was found for badge {$a}.';
