<?php
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_isycredentials', get_string('pluginname', 'local_isycredentials'));

    $settings->add(new admin_setting_configtext(
        'local_isycredentials/dss_signing_service_url',
        get_string('dss_signing_service_url', 'local_isycredentials'),
        get_string('dss_signing_service_url_desc', 'local_isycredentials'),
        'http://dss:8080/services/rest/signature',
        PARAM_URL
    ));

    $settings->add(new admin_setting_configtext(
        'local_isycredentials/edci_signing_service_url',
        get_string('edci_signing_service_url', 'local_isycredentials'),
        get_string('edci_signing_service_url_desc', 'local_isycredentials'),
        'http://issuer:8080',
        PARAM_URL
    ));

    $settings->add(new admin_setting_configstoredfile(
        'local_isycredentials/certificate_file',
        get_string('certificate_file', 'local_isycredentials'),
        get_string('certificate_file_desc', 'local_isycredentials'),
        'certificate_file'
    ));

    $settings->add(new admin_setting_configpasswordunmask(
        'local_isycredentials/certificate_password',
        get_string('certificate_password', 'local_isycredentials'),
        get_string('certificate_password_desc', 'local_isycredentials'),
        ''
    ));

    $settings->add(new admin_setting_configtextarea(
        'local_isycredentials/elm_issuer_data',
        get_string('elm_issuer_data', 'local_isycredentials'),
        get_string('elm_issuer_data_desc', 'local_isycredentials'),
        '{
            "id": "urn:epass:org:4f246f56-fbd0-4492-ad1e-c780c987a121",
            "type": "Organisation",
            "eIDASIdentifier": {
                "id": "urn:certificateIdentifier:1",
                "type": "LegalIdentifier",
                "spatial": {
                    "id": "http://publications.europa.eu/resource/authority/country/$ISO-639-2/t-CountryCode$",
                    "type": "Concept",
                    "inScheme": {
                        "id": "http://publications.europa.eu/resource/authority/country",
                        "type": "ConceptScheme"
                    },
                    "notation": "country",
                    "prefLabel": {
                        "de": "$CountryName$"
                    }
                },
                "notation": "$eIDASIdentifier$"
            },
            "location": {
                "id": "urn:certificateLocation:1",
                "type": "Location",
                "address": {
                    "id": "urn:certificateAddress:1",
                    "type": "Address",
                    "countryCode": {
                        "id": "http://publications.europa.eu/resource/authority/country/$ISO-639-2/t-CountryCode$",
                        "type": "Concept",
                        "inScheme": {
                            "id": "http://publications.europa.eu/resource/authority/country",
                            "type": "ConceptScheme"
                        },
                        "notation": "country",
                        "prefLabel": {
                            "de": "$CountryName$"
                        }
                    }
                }
            },
            "altLabel": {
                "de": "$IssuerAlternativeName$"
            },
            "legalName": {
                "de": "$IssuerLegalName$"
            }
        }',
        PARAM_RAW_TRIMMED
    ));

    $ADMIN->add('localplugins', $settings);
}
