<?php

if (!defined('ABSPATH')) {
    exit;
}

$loop_context_file = __DIR__ . '/loop-context.php';
if (file_exists($loop_context_file)) {
    require_once $loop_context_file;
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

/**
 * Loads the experimental Divi 5 Dynamic Content testimonial sources when its API is available.
 */
function wp_seed_content_load_divi_dynamic_content_testimonial_fields()
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

    $base_class_name = 'WP_Seed_Content_Divi_Dynamic_Content_Testimonial_Base';
    if (!class_exists($base_class_name, false)) {
        $base_class_file = __DIR__ . '/class-dynamic-content-testimonial-base.php';
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
            'file' => __DIR__ . '/class-dynamic-content-testimonial-photo.php',
            'class' => 'WP_Seed_Content_Divi_Dynamic_Content_Testimonial_Photo',
            'base' => '\ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContentOptionBase',
        ),
        array(
            'file' => __DIR__ . '/class-dynamic-content-testimonial-loop-fields.php',
            'class' => 'WP_Seed_Content_Divi_Dynamic_Content_Testimonial_Title',
            'base' => $base_class_name,
        ),
        array(
            'file' => __DIR__ . '/class-dynamic-content-testimonial-loop-fields.php',
            'class' => 'WP_Seed_Content_Divi_Dynamic_Content_Testimonial_Summary',
            'base' => $base_class_name,
        ),
        array(
            'file' => __DIR__ . '/class-dynamic-content-testimonial-text.php',
            'class' => 'WP_Seed_Content_Divi_Dynamic_Content_Testimonial_Text',
            'base' => $base_class_name,
        ),
        array(
            'file' => __DIR__ . '/class-dynamic-content-testimonial-loop-fields.php',
            'class' => 'WP_Seed_Content_Divi_Dynamic_Content_Testimonial_Intro',
            'base' => $base_class_name,
        ),
        array(
            'file' => __DIR__ . '/class-dynamic-content-testimonial-loop-fields.php',
            'class' => 'WP_Seed_Content_Divi_Dynamic_Content_Testimonial_More',
            'base' => $base_class_name,
        ),
        array(
            'file' => __DIR__ . '/class-dynamic-content-testimonial-loop-fields.php',
            'class' => 'WP_Seed_Content_Divi_Dynamic_Content_Testimonial_Has_More',
            'base' => $base_class_name,
        ),
        array(
            'file' => __DIR__ . '/class-dynamic-content-testimonial-name.php',
            'class' => 'WP_Seed_Content_Divi_Dynamic_Content_Testimonial_Name',
            'base' => $base_class_name,
        ),
        array(
            'file' => __DIR__ . '/class-dynamic-content-testimonial-context.php',
            'class' => 'WP_Seed_Content_Divi_Dynamic_Content_Testimonial_Context',
            'base' => $base_class_name,
        ),
        array(
            'file' => __DIR__ . '/class-dynamic-content-testimonial-date.php',
            'class' => 'WP_Seed_Content_Divi_Dynamic_Content_Testimonial_Date',
            'base' => $base_class_name,
        ),
        array(
            'file' => __DIR__ . '/class-dynamic-content-testimonial-loop-fields.php',
            'class' => 'WP_Seed_Content_Divi_Dynamic_Content_Testimonial_Id',
            'base' => $base_class_name,
        ),
        array(
            'file' => __DIR__ . '/class-dynamic-content-testimonial-loop-fields.php',
            'class' => 'WP_Seed_Content_Divi_Dynamic_Content_Testimonial_Anchor',
            'base' => $base_class_name,
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
            || !is_subclass_of($class_name, $source['base'])
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
add_action('init', 'wp_seed_content_load_divi_dynamic_content_testimonial_fields', 10);

function wp_seed_content_load_divi_dynamic_content_directory_fields()
{
    static $options = array();
    if (
        !class_exists('\\ET\\Builder\\Packages\\Module\\Layout\\Components\\DynamicContent\\DynamicContentElements')
        || !class_exists('\\ET\\Builder\\Packages\\Module\\Layout\\Components\\DynamicContent\\DynamicContentOptionBase')
        || !interface_exists('\\ET\\Builder\\Packages\\Module\\Layout\\Components\\DynamicContent\\DynamicContentOptionInterface')
    ) {
        return;
    }

    require_once __DIR__ . '/class-dynamic-content-directory-base.php';
    require_once __DIR__ . '/class-dynamic-content-directory-fields.php';
    $classes = array(
        'WP_Seed_Content_Divi_Dynamic_Content_Directory_Visual',
        'WP_Seed_Content_Divi_Dynamic_Content_Directory_Name',
        'WP_Seed_Content_Divi_Dynamic_Content_Directory_Professional_Label',
        'WP_Seed_Content_Divi_Dynamic_Content_Directory_Summary',
        'WP_Seed_Content_Divi_Dynamic_Content_Directory_Presentation',
        'WP_Seed_Content_Divi_Dynamic_Content_Directory_Presentation_Intro',
        'WP_Seed_Content_Divi_Dynamic_Content_Directory_Presentation_More',
        'WP_Seed_Content_Divi_Dynamic_Content_Directory_Status',
        'WP_Seed_Content_Divi_Dynamic_Content_Directory_Profile_Types',
        'WP_Seed_Content_Divi_Dynamic_Content_Directory_Seeking_Models',
        'WP_Seed_Content_Divi_Dynamic_Content_Directory_Location',
    );
    foreach ($classes as $class_name) {
        if (!isset($options[$class_name]) && class_exists($class_name)) {
            $option = new $class_name();
            $option->load();
            $options[$class_name] = $option;
        }
    }

    if (function_exists('wp_seed_content_directory_individual_contact_provider_definitions')) {
        foreach (wp_seed_content_directory_individual_contact_provider_definitions() as $definition) {
            foreach (array(false, true) as $href) {
                if ($href && empty($definition['has_href'])) {
                    continue;
                }
                $key = $definition['slug'] . ($href ? '_href' : '');
                if (isset($options[$key])) {
                    continue;
                }
                $option = new WP_Seed_Content_Divi_Dynamic_Content_Directory_Contact_Field($definition, $href);
                $option->load();
                $options[$key] = $option;
            }
        }
    }
    foreach (array(
        'WP_Seed_Content_Divi_Dynamic_Content_Directory_Id',
        'WP_Seed_Content_Divi_Dynamic_Content_Directory_Anchor',
    ) as $class_name) {
        if (!isset($options[$class_name]) && class_exists($class_name)) {
            $option = new $class_name();
            $option->load();
            $options[$class_name] = $option;
        }
    }
}
add_action('init', 'wp_seed_content_load_divi_dynamic_content_directory_fields', 10);
