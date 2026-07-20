<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_kit_get_template_allowed_html()
{
    return array(
        'a' => array('href' => true, 'title' => true, 'rel' => true, 'class' => true),
        'br' => array(),
        'em' => array('class' => true),
        'strong' => array('class' => true),
        'span' => array('class' => true),
        'p' => array('class' => true),
        'ul' => array('class' => true),
        'ol' => array('class' => true),
        'li' => array('class' => true),
        'img' => array('src' => true, 'alt' => true, 'width' => true, 'height' => true, 'class' => true, 'loading' => true),
    );
}

function wp_seed_content_kit_is_empty_context_value($value, $type)
{
    if ('image' === $type) {
        return !is_array($value) || empty($value['url']);
    }
    if ('text_list' === $type) {
        return !is_array($value) || empty($value);
    }

    return '' === trim((string) $value);
}

function wp_seed_content_kit_normalize_context_value($value, $definition)
{
    $type = $definition['type'];
    if ('image' === $type) {
        if (!is_array($value) || array_diff(array_keys($value), array('url', 'alt'))) {
            return null;
        }
        if ((isset($value['url']) && !is_scalar($value['url'])) || (isset($value['alt']) && !is_scalar($value['alt']))) {
            return null;
        }
        $url = isset($value['url']) ? (string) $value['url'] : '';
        $url = esc_url_raw($url, array('http', 'https'));
        if (!empty($value['url']) && '' === $url) {
            return null;
        }
        return array(
            'url' => $url,
            'alt' => isset($value['alt']) ? (string) $value['alt'] : '',
        );
    }

    if ('text_list' === $type) {
        if (!is_array($value)) {
            return null;
        }
        $items = array();
        foreach ($value as $item) {
            if (!is_scalar($item)) {
                return null;
            }
            $items[] = (string) $item;
        }
        return $items;
    }

    if (!is_scalar($value) && null !== $value) {
        return null;
    }

    $value = null === $value ? '' : (string) $value;
    if ('url' === $type) {
        $url = esc_url_raw($value, array('http', 'https'));
        return '' !== $value && '' === $url ? null : $url;
    }
    if ('email' === $type) {
        $email = sanitize_email($value);
        return '' !== $value && '' === $email ? null : $email;
    }
    if ('tel' === $type) {
        return preg_replace('/[^0-9+(). \-]/', '', $value);
    }

    return $value;
}

function wp_seed_content_kit_prepare_public_context($module_definition, $placeholder_definitions, array $context)
{
    $normalized = array();
    foreach ($placeholder_definitions as $placeholder) {
        $context_key = $placeholder['context_key'];
        $value = array_key_exists($context_key, $context) ? $context[$context_key] : $placeholder['empty'];
        $value = wp_seed_content_kit_normalize_context_value($value, $placeholder);
        if (null === $value) {
            return array('success' => false, 'code' => 'invalid_context', 'context' => array());
        }

        if (is_callable($placeholder['normalize_callback'])) {
            try {
                $value = call_user_func($placeholder['normalize_callback'], $value);
            } catch (Throwable $exception) {
                return array('success' => false, 'code' => 'provider_error', 'context' => array());
            }
            $value = wp_seed_content_kit_normalize_context_value($value, $placeholder);
            if (null === $value) {
                return array('success' => false, 'code' => 'invalid_context', 'context' => array());
            }
        }

        if ($placeholder['required'] && wp_seed_content_kit_is_empty_context_value($value, $placeholder['type'])) {
            return array('success' => false, 'code' => 'invalid_context', 'context' => array());
        }
        $normalized[$context_key] = $value;
    }

    if (is_callable($module_definition['validate_context'])) {
        try {
            if (true !== call_user_func($module_definition['validate_context'], $normalized)) {
                return array('success' => false, 'code' => 'invalid_context', 'context' => array());
            }
        } catch (Throwable $exception) {
            return array('success' => false, 'code' => 'provider_error', 'context' => array());
        }
    }

    return array('success' => true, 'code' => 'success', 'context' => $normalized);
}

function wp_seed_content_kit_escape_public_placeholder($value, $type)
{
    if ('textarea' === $type) {
        return nl2br(esc_html((string) $value));
    }
    if ('html' === $type) {
        return wp_kses((string) $value, wp_seed_content_kit_get_template_allowed_html());
    }
    if ('url' === $type) {
        return esc_url((string) $value);
    }
    if ('email' === $type) {
        return esc_html(sanitize_email((string) $value));
    }
    if ('tel' === $type) {
        return esc_html(preg_replace('/[^0-9+(). \-]/', '', (string) $value));
    }
    if ('image' === $type) {
        if (!is_array($value) || empty($value['url'])) {
            return '';
        }
        return '<img src="' . esc_url($value['url']) . '" alt="' . esc_attr(isset($value['alt']) ? $value['alt'] : '') . '" loading="lazy" />';
    }
    if ('text_list' === $type) {
        return implode(', ', array_map('esc_html', is_array($value) ? $value : array()));
    }

    return esc_html((string) $value);
}

function wp_seed_content_kit_prepare_public_replacements($module_definition, $placeholder_definitions, array $context)
{
    $prepared = wp_seed_content_kit_prepare_public_context($module_definition, $placeholder_definitions, $context);
    if (!$prepared['success']) {
        return $prepared;
    }

    $values = $prepared['context'];
    if (is_callable($module_definition['provider'])) {
        try {
            $provided = call_user_func($module_definition['provider'], $values);
        } catch (Throwable $exception) {
            return array('success' => false, 'code' => 'provider_error', 'replacements' => array());
        }
        if (!is_array($provided)) {
            return array('success' => false, 'code' => 'provider_error', 'replacements' => array());
        }
    } else {
        $provided = $values;
    }

    $replacements = array();
    foreach ($placeholder_definitions as $key => $placeholder) {
        $value_key = is_callable($module_definition['provider']) ? $key : $placeholder['context_key'];
        $value = array_key_exists($value_key, $provided) ? $provided[$value_key] : $placeholder['empty'];
        $value = wp_seed_content_kit_normalize_context_value($value, $placeholder);
        if (null === $value || ($placeholder['required'] && wp_seed_content_kit_is_empty_context_value($value, $placeholder['type']))) {
            return array('success' => false, 'code' => 'invalid_context', 'replacements' => array());
        }
        $replacements['{{' . $key . '}}'] = wp_seed_content_kit_escape_public_placeholder($value, $placeholder['type']);
    }

    return array('success' => true, 'code' => 'success', 'replacements' => $replacements);
}

function wp_seed_content_kit_validate_render_assets($assets)
{
    $validated = array('styles' => array(), 'scripts' => array());
    foreach (array('styles', 'scripts') as $type) {
        foreach (isset($assets[$type]) ? $assets[$type] : array() as $handle) {
            $registered = 'styles' === $type ? wp_style_is($handle, 'registered') : wp_script_is($handle, 'registered');
            if (!$registered) {
                return null;
            }
            $validated[$type][$handle] = $handle;
        }
        $validated[$type] = array_values($validated[$type]);
    }

    return $validated;
}

function wp_seed_content_kit_enqueue_render_assets($assets)
{
    foreach ($assets['styles'] as $handle) {
        wp_enqueue_style($handle);
    }
    foreach ($assets['scripts'] as $handle) {
        wp_enqueue_script($handle);
    }
}

function wp_seed_content_kit_get_public_template_by_slug($slug)
{
    static $templates = array();

    if (array_key_exists($slug, $templates)) {
        return $templates[$slug];
    }

    $template = wp_seed_content_get_template_by_slug($slug);
    $templates[$slug] = $template ? $template : null;

    return $templates[$slug];
}

function wp_seed_content_kit_render_template($slug, $module, array $context = array(), array $args = array())
{
    if (!wp_seed_content_kit_is_template_registry_ready()) {
        return wp_seed_content_kit_render_failure('unavailable');
    }
    if (!is_string($slug) || trim($slug) !== $slug || '' === $slug || sanitize_title($slug) !== $slug) {
        return wp_seed_content_kit_render_failure('invalid_slug');
    }
    if (!wp_seed_content_kit_is_valid_template_identifier($module)) {
        return wp_seed_content_kit_render_failure('unavailable');
    }

    $module_definition = wp_seed_content_kit_get_registered_template_module($module);
    if (!is_array($module_definition)) {
        return wp_seed_content_kit_render_failure('unavailable');
    }

    $template = wp_seed_content_kit_get_public_template_by_slug($slug);
    if (!$template) {
        return wp_seed_content_kit_render_failure('template_not_found');
    }
    if ($module !== wp_seed_content_get_template_module($template->ID)) {
        return wp_seed_content_kit_render_failure('module_mismatch', $template->ID);
    }

    $source = function_exists('wp_seed_content_get_template_layout_source') ? wp_seed_content_get_template_layout_source($template->ID) : 'native';
    if (!in_array($source, $module_definition['render_types'], true)) {
        return wp_seed_content_kit_render_failure('module_mismatch', $template->ID);
    }

    $stack_key = (int) $template->ID . ':' . $module;
    $stack = isset($GLOBALS['wp_seed_content_kit_template_render_stack']) && is_array($GLOBALS['wp_seed_content_kit_template_render_stack'])
        ? $GLOBALS['wp_seed_content_kit_template_render_stack']
        : array();
    if (isset($stack[$stack_key]) || count($stack) >= 8) {
        $active_keys = array_keys($stack);
        $owner_key = isset($stack[$stack_key]) ? $stack_key : reset($active_keys);
        if ($owner_key) {
            $GLOBALS['wp_seed_content_kit_template_recursion_failures'][$owner_key] = true;
        }
        return wp_seed_content_kit_render_failure('recursion_detected', $template->ID);
    }

    $GLOBALS['wp_seed_content_kit_template_render_stack'][$stack_key] = true;
    try {
        $placeholder_definitions = wp_seed_content_kit_get_registered_template_placeholders($module);
        $prepared = wp_seed_content_kit_prepare_public_replacements($module_definition, $placeholder_definitions, $context);
        if (!$prepared['success']) {
            return wp_seed_content_kit_render_failure($prepared['code'], $template->ID);
        }

        if ('divi_layout' === $source) {
            $html = wp_seed_content_render_template_using_divi_layout($template->ID, $prepared['replacements']);
        } else {
            $html = '';
        }
        if ('' === trim((string) $html)) {
            $html = apply_filters('the_content', strtr((string) $template->post_content, $prepared['replacements']));
        }
        if (!empty($GLOBALS['wp_seed_content_kit_template_recursion_failures'][$stack_key])) {
            unset($GLOBALS['wp_seed_content_kit_template_recursion_failures'][$stack_key]);
            return wp_seed_content_kit_render_failure('recursion_detected', $template->ID);
        }
        if (preg_match('/\{\{\s*[a-z][a-z0-9_.-]*\s*\}\}/i', (string) $html)) {
            return wp_seed_content_kit_render_failure('invalid_context', $template->ID);
        }
        if ('' === trim((string) $html)) {
            return wp_seed_content_kit_render_failure('empty_render', $template->ID);
        }

        $assets = wp_seed_content_kit_validate_render_assets($module_definition['assets']);
        if (null === $assets) {
            return wp_seed_content_kit_render_failure('invalid_assets', $template->ID);
        }
        wp_seed_content_kit_enqueue_render_assets($assets);

        return wp_seed_content_kit_render_success($html, $template->ID, $assets);
    } catch (Throwable $exception) {
        return wp_seed_content_kit_render_failure('provider_error', $template->ID);
    } finally {
        unset($GLOBALS['wp_seed_content_kit_template_render_stack'][$stack_key]);
        unset($GLOBALS['wp_seed_content_kit_template_recursion_failures'][$stack_key]);
    }
}
