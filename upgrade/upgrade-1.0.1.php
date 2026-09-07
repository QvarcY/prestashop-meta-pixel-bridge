<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_0_1($module)
{
    $ok = true;
    if (Configuration::get('MPB_CONSENT_STORAGE_KEY') === false) {
        $ok = Configuration::updateValue('MPB_CONSENT_STORAGE_KEY', '') && $ok;
    }
    if (Configuration::get('MPB_CONSENT_STORAGE_FIELD') === false) {
        $ok = Configuration::updateValue('MPB_CONSENT_STORAGE_FIELD', 'marketing') && $ok;
    }
    if (Configuration::get('MPB_CONSENT_STORAGE_EXPIRY') === false) {
        $ok = Configuration::updateValue('MPB_CONSENT_STORAGE_EXPIRY', 'valid_until') && $ok;
    }
    return $ok;
}
