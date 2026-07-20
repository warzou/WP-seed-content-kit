<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!defined('WP_SEED_CONTENT_KIT_TEMPLATE_CONTRACT_VERSION')) {
    define('WP_SEED_CONTENT_KIT_TEMPLATE_CONTRACT_VERSION', '1.0');
}

function wp_seed_content_kit_get_contract_version()
{
    return WP_SEED_CONTENT_KIT_TEMPLATE_CONTRACT_VERSION;
}

function wp_seed_content_kit_get_contract_capabilities()
{
    return array(
        'template_extension' => '1.0',
        'template_modules' => '1.0',
        'template_placeholders' => '1.0',
        'typed_render_result' => '1.0',
        'render_assets' => '1.0',
        'recursion_guard' => '1.0',
    );
}

function wp_seed_content_kit_supports($capability, $minimum_version = '')
{
    if (!is_string($capability) || !is_string($minimum_version)) {
        return false;
    }

    $capability = sanitize_key($capability);
    $capabilities = wp_seed_content_kit_get_contract_capabilities();
    if ('' === $capability || !isset($capabilities[$capability])) {
        return false;
    }

    $minimum_version = trim($minimum_version);
    if ('' === $minimum_version) {
        return true;
    }

    if (!preg_match('/^[0-9]+(?:\.[0-9]+){0,2}$/D', $minimum_version)) {
        return false;
    }

    return version_compare($capabilities[$capability], $minimum_version, '>=');
}
