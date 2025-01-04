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

    $ADMIN->add('localplugins', $settings);
}
