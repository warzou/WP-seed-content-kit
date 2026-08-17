<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_divi_directory_has_more_condition_name()
{
    return 'wpsckDirectoryHasMore';
}

function wp_seed_content_divi_directory_has_more_condition_value_key()
{
    return 'wpsckResolvedPresentationMore';
}

function wp_seed_content_divi_directory_contact_condition_value_key()
{
    return 'wpsckResolvedDirectoryContact';
}

function wp_seed_content_divi_directory_contact_condition_names()
{
    return array_keys(wp_seed_content_divi_directory_contact_condition_definitions());
}

function wp_seed_content_divi_directory_contact_condition_definitions()
{
    $conditions = array();
    if (!function_exists('wp_seed_content_directory_individual_contact_provider_definitions')) {
        return $conditions;
    }
    foreach (wp_seed_content_directory_individual_contact_provider_definitions() as $definition) {
        $conditions[$definition['condition_name']] = array(
            'name' => $definition['condition_name'],
            'label' => $definition['condition_label'],
            'provider' => $definition['display_provider_id'],
        );
    }
    return $conditions;
}

/**
 * Evaluate the loop-resolved continuation transported in the condition settings.
 *
 * Divi resolves Dynamic Content recursively in every cloned block attribute before
 * evaluating display conditions, so this value belongs to the current Loop item.
 *
 * @param bool|null $result             Result from another custom condition handler.
 * @param string    $condition_name     Divi condition name.
 * @param array     $condition_settings Stored condition settings.
 * @param string    $condition_id       Condition instance ID.
 *
 * @return bool|null
 */
function wp_seed_content_divi_directory_evaluate_has_more_condition(
    $result,
    $condition_name,
    $condition_settings,
    $condition_id
) {
    unset($condition_id);

    $is_has_more = wp_seed_content_divi_directory_has_more_condition_name() === $condition_name;
    $is_contact = in_array($condition_name, wp_seed_content_divi_directory_contact_condition_names(), true);
    if (!$is_has_more && !$is_contact) {
        return $result;
    }

    $contact_value_key = wp_seed_content_divi_directory_contact_condition_value_key();
    $has_legacy_contact_value = $is_has_more
        && is_array($condition_settings)
        && array_key_exists($contact_value_key, $condition_settings);
    $value_key = ($is_contact || $has_legacy_contact_value)
        ? $contact_value_key
        : wp_seed_content_divi_directory_has_more_condition_value_key();

    $resolved_value = is_array($condition_settings) && isset($condition_settings[$value_key])
        ? (string) $condition_settings[$value_key]
        : '';

    // Early registry-driven conditions were stored under the has_more name while
    // already carrying the canonical contact token. The setting key identifies
    // those bindings without rewriting post_content or guessing a contact type.
    // The conditions status endpoint has no Loop context. The actual cloned block
    // resolves this token before frontend or Visual Builder canvas rendering.
    if (false !== strpos($resolved_value, '$variable(')) {
        return true;
    }

    return '' !== trim($resolved_value);
}
add_filter(
    'divi_module_options_conditions_is_custom_condition_true',
    'wp_seed_content_divi_directory_evaluate_has_more_condition',
    10,
    4
);

function wp_seed_content_divi_directory_has_more_condition_asset_version()
{
    $path = __DIR__ . '/directory-has-more-condition/visual-builder.js';
    $hash = is_file($path) ? hash_file('sha256', $path) : false;

    return $hash ? substr($hash, 0, 16) : WP_SEED_CONTENT_KIT_VERSION;
}

function wp_seed_content_register_divi_directory_has_more_condition_assets()
{
    static $registered = false;

    if (
        $registered
        || !function_exists('et_core_is_fb_enabled')
        || !et_core_is_fb_enabled()
        || !class_exists('ET\\Builder\\VisualBuilder\\Assets\\PackageBuildManager')
    ) {
        return;
    }

    $registered = true;
    \ET\Builder\VisualBuilder\Assets\PackageBuildManager::register_package_build(
        array(
            'name' => 'wp-seed-content-kit-divi-directory-has-more-condition',
            'version' => wp_seed_content_divi_directory_has_more_condition_asset_version(),
            'script' => array(
                'src' => WP_SEED_CONTENT_KIT_URL . 'includes/integrations/divi/directory-has-more-condition/visual-builder.js',
                'deps' => array('divi-field-library', 'divi-vendor-wp-hooks'),
                'enqueue_top_window' => false,
                'enqueue_app_window' => true,
                'data_app_window' => array(
                    'contactConditions' => array_values(
                        wp_seed_content_divi_directory_contact_condition_definitions()
                    ),
                ),
            ),
        )
    );
}
add_action(
    'divi_visual_builder_assets_before_enqueue_scripts',
    'wp_seed_content_register_divi_directory_has_more_condition_assets'
);
