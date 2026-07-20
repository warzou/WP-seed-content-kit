<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_kit_get_template_placeholder_types()
{
    return array('text', 'textarea', 'html', 'url', 'email', 'tel', 'image', 'text_list');
}

function wp_seed_content_kit_get_template_escape_strategy($type)
{
    $strategies = array(
        'text' => 'esc_html',
        'textarea' => 'escaped_lines',
        'html' => 'wp_kses',
        'url' => 'esc_url',
        'email' => 'sanitize_email',
        'tel' => 'sanitize_tel',
        'image' => 'validated_image',
        'text_list' => 'escaped_list',
    );

    return isset($strategies[$type]) ? $strategies[$type] : '';
}

function wp_seed_content_kit_is_valid_template_identifier($identifier)
{
    return is_string($identifier) && 1 === preg_match('/^[a-z][a-z0-9_-]*$/D', $identifier);
}

function wp_seed_content_kit_is_valid_placeholder_key($key)
{
    return is_string($key) && 1 === preg_match('/^[a-z][a-z0-9_.-]*$/D', $key);
}

function wp_seed_content_kit_is_template_registration_open()
{
    return !empty($GLOBALS['wp_seed_content_kit_template_registration_open']);
}

function wp_seed_content_kit_is_template_registry_ready()
{
    return !empty($GLOBALS['wp_seed_content_kit_template_registry_ready']);
}

function wp_seed_content_kit_get_registered_template_modules()
{
    return isset($GLOBALS['wp_seed_content_kit_template_modules']) && is_array($GLOBALS['wp_seed_content_kit_template_modules'])
        ? $GLOBALS['wp_seed_content_kit_template_modules']
        : array();
}

function wp_seed_content_kit_get_registered_template_module($module)
{
    $modules = wp_seed_content_kit_get_registered_template_modules();
    return isset($modules[$module]) ? $modules[$module] : null;
}

function wp_seed_content_kit_get_registered_template_placeholders($module)
{
    $placeholders = isset($GLOBALS['wp_seed_content_kit_template_placeholders']) && is_array($GLOBALS['wp_seed_content_kit_template_placeholders'])
        ? $GLOBALS['wp_seed_content_kit_template_placeholders']
        : array();

    return isset($placeholders[$module]) && is_array($placeholders[$module]) ? $placeholders[$module] : array();
}

function wp_seed_content_kit_normalize_asset_handles($handles)
{
    if (!is_array($handles)) {
        return null;
    }

    $normalized = array();
    foreach ($handles as $handle) {
        if (!is_string($handle)) {
            return null;
        }

        $handle = trim($handle);
        if ('' === $handle || sanitize_key($handle) !== $handle) {
            return null;
        }

        $normalized[$handle] = $handle;
    }

    return array_values($normalized);
}

function wp_seed_content_kit_normalize_placeholder_definition($key, $definition)
{
    if (!wp_seed_content_kit_is_valid_placeholder_key($key) || !is_array($definition)) {
        return null;
    }

    $type = isset($definition['type']) && is_string($definition['type']) ? sanitize_key($definition['type']) : '';
    if (!in_array($type, wp_seed_content_kit_get_template_placeholder_types(), true)) {
        return null;
    }

    $context_key = isset($definition['context_key']) ? $definition['context_key'] : $key;
    if (!wp_seed_content_kit_is_valid_placeholder_key($context_key)) {
        return null;
    }

    $normalizer = isset($definition['normalize_callback']) ? $definition['normalize_callback'] : null;
    if (null !== $normalizer && !is_callable($normalizer)) {
        return null;
    }

    $strategy = wp_seed_content_kit_get_template_escape_strategy($type);
    if (isset($definition['escape']) && $strategy !== $definition['escape']) {
        return null;
    }

    return array(
        'key' => $key,
        'type' => $type,
        'label' => isset($definition['label']) ? sanitize_text_field((string) $definition['label']) : $key,
        'empty' => array_key_exists('empty', $definition) ? $definition['empty'] : wp_seed_content_kit_get_empty_placeholder_value($type),
        'required' => !empty($definition['required']),
        'escape' => $strategy,
        'context_key' => $context_key,
        'normalize_callback' => $normalizer,
    );
}

function wp_seed_content_kit_get_empty_placeholder_value($type)
{
    if ('image' === $type) {
        return array('url' => '', 'alt' => '');
    }
    if ('text_list' === $type) {
        return array();
    }

    return '';
}

function wp_seed_content_kit_register_template_placeholders($module, array $definitions)
{
    if (!wp_seed_content_kit_is_template_registration_open() || !wp_seed_content_kit_is_valid_template_identifier($module)) {
        return false;
    }

    $module_definition = wp_seed_content_kit_get_registered_template_module($module);
    if (!is_array($module_definition) || empty($definitions)) {
        return false;
    }

    $current = wp_seed_content_kit_get_registered_template_placeholders($module);
    $normalized = array();
    foreach ($definitions as $key => $definition) {
        if (isset($current[$key]) || isset($normalized[$key])) {
            return false;
        }

        $placeholder = wp_seed_content_kit_normalize_placeholder_definition($key, $definition);
        if (!is_array($placeholder)) {
            return false;
        }
        $normalized[$key] = $placeholder;
    }

    if (!isset($GLOBALS['wp_seed_content_kit_template_placeholders']) || !is_array($GLOBALS['wp_seed_content_kit_template_placeholders'])) {
        $GLOBALS['wp_seed_content_kit_template_placeholders'] = array();
    }
    $GLOBALS['wp_seed_content_kit_template_placeholders'][$module] = array_merge($current, $normalized);
    $GLOBALS['wp_seed_content_kit_template_modules'][$module]['placeholders'] = array_keys($GLOBALS['wp_seed_content_kit_template_placeholders'][$module]);

    return true;
}

function wp_seed_content_kit_register_template_module($module, array $definition)
{
    if (!wp_seed_content_kit_is_template_registration_open() || !wp_seed_content_kit_is_valid_template_identifier($module)) {
        return false;
    }

    $modules = wp_seed_content_kit_get_registered_template_modules();
    if (isset($modules[$module])) {
        return false;
    }

    $provider = isset($definition['provider']) ? $definition['provider'] : null;
    $validator = isset($definition['validate_context']) ? $definition['validate_context'] : null;
    if ((null !== $provider && !is_callable($provider)) || (null !== $validator && !is_callable($validator))) {
        return false;
    }

    if (isset($definition['render_types']) && !is_array($definition['render_types'])) {
        return false;
    }
    $render_types = isset($definition['render_types']) ? $definition['render_types'] : array('native');
    foreach ($render_types as $render_type) {
        if (!is_string($render_type) || !in_array($render_type, array('native', 'divi_layout'), true)) {
            return false;
        }
    }
    $render_types = array_values(array_unique($render_types));
    if (empty($render_types)) {
        return false;
    }

    if (isset($definition['assets']) && !is_array($definition['assets'])) {
        return false;
    }
    $assets = isset($definition['assets']) ? $definition['assets'] : array();
    if (array_diff(array_keys($assets), array('styles', 'scripts'))) {
        return false;
    }
    $styles = wp_seed_content_kit_normalize_asset_handles(isset($assets['styles']) ? $assets['styles'] : array());
    $scripts = wp_seed_content_kit_normalize_asset_handles(isset($assets['scripts']) ? $assets['scripts'] : array());
    if (null === $styles || null === $scripts) {
        return false;
    }

    $placeholders = isset($definition['placeholders']) ? $definition['placeholders'] : array();
    if (!is_array($placeholders) || empty($placeholders)) {
        return false;
    }
    if ((isset($definition['label']) && !is_scalar($definition['label'])) || (isset($definition['description']) && !is_scalar($definition['description']))) {
        return false;
    }
    if (isset($definition['shortcode']) && !is_string($definition['shortcode'])) {
        return false;
    }

    if (!isset($GLOBALS['wp_seed_content_kit_template_modules']) || !is_array($GLOBALS['wp_seed_content_kit_template_modules'])) {
        $GLOBALS['wp_seed_content_kit_template_modules'] = array();
    }
    $GLOBALS['wp_seed_content_kit_template_modules'][$module] = array(
        'id' => $module,
        'label' => isset($definition['label']) ? sanitize_text_field((string) $definition['label']) : $module,
        'description' => isset($definition['description']) ? sanitize_text_field((string) $definition['description']) : '',
        'provider' => $provider,
        'validate_context' => $validator,
        'render_types' => $render_types,
        'assets' => array('styles' => $styles, 'scripts' => $scripts),
        'shortcode' => isset($definition['shortcode']) ? sanitize_key((string) $definition['shortcode']) : '',
        'placeholders' => array(),
    );

    if (!wp_seed_content_kit_register_template_placeholders($module, $placeholders)) {
        unset($GLOBALS['wp_seed_content_kit_template_modules'][$module]);
        return false;
    }

    return true;
}

function wp_seed_content_kit_register_core_template_modules()
{
    wp_seed_content_kit_register_template_module(
        'testimonials',
        array(
            'label' => __('Témoignages', 'wp-seed-content-kit'),
            'description' => __('Templates de témoignages.', 'wp-seed-content-kit'),
            'shortcode' => 'seed_testimonials',
            'render_types' => array('native', 'divi_layout'),
            'assets' => array('styles' => array('wp-seed-content-kit')),
            'placeholders' => array(
                'photo' => array('type' => 'html', 'label' => __('Photo du témoignage', 'wp-seed-content-kit')),
                'photo_url' => array('type' => 'url', 'label' => __('URL de la photo', 'wp-seed-content-kit')),
                'name' => array('type' => 'text', 'label' => __('Nom ou initiales', 'wp-seed-content-kit')),
                'text' => array('type' => 'textarea', 'label' => __('Texte du témoignage', 'wp-seed-content-kit')),
                'photo_alt' => array('type' => 'text', 'label' => __('Texte alternatif de la photo', 'wp-seed-content-kit')),
                'context' => array('type' => 'text', 'label' => __('Information complémentaire', 'wp-seed-content-kit')),
                'date' => array('type' => 'text', 'label' => __('Date du témoignage', 'wp-seed-content-kit')),
            ),
        )
    );

    wp_seed_content_kit_register_template_module(
        'quotes',
        array(
            'label' => __('Citations', 'wp-seed-content-kit'),
            'description' => __('Templates de citations.', 'wp-seed-content-kit'),
            'shortcode' => 'seed_quotes',
            'render_types' => array('native', 'divi_layout'),
            'assets' => array('styles' => array('wp-seed-content-kit')),
            'placeholders' => array(
                'quote' => array('type' => 'textarea', 'label' => __('Citation', 'wp-seed-content-kit')),
                'author' => array('type' => 'text', 'label' => __('Auteur', 'wp-seed-content-kit')),
                'era' => array('type' => 'text', 'label' => __('Époque / date affichée', 'wp-seed-content-kit')),
                'source' => array('type' => 'text', 'label' => __('Source / contexte', 'wp-seed-content-kit')),
            ),
        )
    );
}

function wp_seed_content_kit_initialize_template_registry()
{
    if (wp_seed_content_kit_is_template_registry_ready() || wp_seed_content_kit_is_template_registration_open()) {
        return;
    }

    $GLOBALS['wp_seed_content_kit_template_registration_open'] = true;
    try {
        wp_seed_content_kit_register_core_template_modules();
        do_action('wp_seed_content_kit_register_template_modules');
    } finally {
        $GLOBALS['wp_seed_content_kit_template_registration_open'] = false;
        $GLOBALS['wp_seed_content_kit_template_registry_ready'] = true;
    }
}

add_action('init', 'wp_seed_content_kit_initialize_template_registry', 1);
