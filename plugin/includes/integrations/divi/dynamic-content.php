<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Loads the experimental Divi 5 Dynamic Content source when its API is available.
 */
function wp_seed_content_load_divi_dynamic_content_quote_text()
{
    static $option = null;

    if (null !== $option) {
        return;
    }

    if (
        !class_exists('\\ET\\Builder\\Packages\\Module\\Layout\\Components\\DynamicContent\\DynamicContentElements')
        || !class_exists('\\ET\\Builder\\Packages\\Module\\Layout\\Components\\DynamicContent\\DynamicContentOptionBase')
        || !interface_exists('\\ET\\Builder\\Packages\\Module\\Layout\\Components\\DynamicContent\\DynamicContentOptionInterface')
    ) {
        return;
    }

    $class_name = 'WP_Seed_Content_Divi_Dynamic_Content_Quote_Text';
    if (!class_exists($class_name, false)) {
        $class_file = __DIR__ . '/class-dynamic-content-quote-text.php';
        if (!file_exists($class_file)) {
            return;
        }

        require_once $class_file;
    }

    if (
        !class_exists($class_name, false)
        || !is_subclass_of($class_name, '\\ET\\Builder\\Packages\\Module\\Layout\\Components\\DynamicContent\\DynamicContentOptionBase')
        || !is_subclass_of($class_name, '\\ET\\Builder\\Packages\\Module\\Layout\\Components\\DynamicContent\\DynamicContentOptionInterface')
    ) {
        return;
    }

    $option = new $class_name();
    $option->load();
}
add_action('init', 'wp_seed_content_load_divi_dynamic_content_quote_text', 10);
