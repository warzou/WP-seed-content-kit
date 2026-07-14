<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Loads the experimental Divi 5 Dynamic Content quote sources when its API is available.
 */
function wp_seed_content_load_divi_dynamic_content_quote_fields()
{
    static $options = array();
    static $loaded_names = array();

    if (
        !class_exists('\ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContentElements')
        || !class_exists('\ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContentOptionBase')
        || !interface_exists('\ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContentOptionInterface')
    ) {
        return;
    }

    $base_class_name = 'WP_Seed_Content_Divi_Dynamic_Content_Quote_Base';
    if (!class_exists($base_class_name, false)) {
        $base_class_file = __DIR__ . '/class-dynamic-content-quote-base.php';
        if (!file_exists($base_class_file)) {
            return;
        }

        require_once $base_class_file;
    }

    if (
        !class_exists($base_class_name, false)
        || !is_subclass_of($base_class_name, '\ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContentOptionBase')
        || !is_subclass_of($base_class_name, '\ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContentOptionInterface')
    ) {
        return;
    }

    $base_reflection = new ReflectionClass($base_class_name);
    if (!$base_reflection->isAbstract() || !$base_reflection->hasMethod('get_dynamic_data_field_id')) {
        return;
    }

    $sources = array(
        array(
            'file' => __DIR__ . '/class-dynamic-content-quote-text.php',
            'class' => 'WP_Seed_Content_Divi_Dynamic_Content_Quote_Text',
        ),
        array(
            'file' => __DIR__ . '/class-dynamic-content-quote-author.php',
            'class' => 'WP_Seed_Content_Divi_Dynamic_Content_Quote_Author',
        ),
        array(
            'file' => __DIR__ . '/class-dynamic-content-quote-era.php',
            'class' => 'WP_Seed_Content_Divi_Dynamic_Content_Quote_Era',
        ),
        array(
            'file' => __DIR__ . '/class-dynamic-content-quote-source.php',
            'class' => 'WP_Seed_Content_Divi_Dynamic_Content_Quote_Source',
        ),
    );

    foreach ($sources as $source) {
        $class_name = $source['class'];
        if (isset($options[$class_name])) {
            continue;
        }

        if (!class_exists($class_name, false)) {
            if (!file_exists($source['file'])) {
                continue;
            }

            require_once $source['file'];
        }

        if (
            !class_exists($class_name, false)
            || !is_subclass_of($class_name, $base_class_name)
            || !is_subclass_of($class_name, '\ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContentOptionInterface')
        ) {
            continue;
        }

        $class_reflection = new ReflectionClass($class_name);
        if (!$class_reflection->isInstantiable()) {
            continue;
        }

        $option = $class_reflection->newInstance();
        $name = $option->get_name();
        if (!is_string($name) || '' === $name || isset($loaded_names[$name])) {
            continue;
        }

        $option->load();
        $options[$class_name] = $option;
        $loaded_names[$name] = true;
    }
}
add_action('init', 'wp_seed_content_load_divi_dynamic_content_quote_fields', 10);
