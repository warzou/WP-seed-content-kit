<?php

use ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContentElements;
use ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContentOptionBase;
use ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContentOptionInterface;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Shared Divi 5 Dynamic Content behavior for testimonial text fields.
 */
abstract class WP_Seed_Content_Divi_Dynamic_Content_Testimonial_Base extends DynamicContentOptionBase implements DynamicContentOptionInterface
{
    abstract protected function get_dynamic_data_field_id(): string;

    protected function get_dynamic_content_type(): string
    {
        return 'text';
    }

    public function register_option_callback(array $options, int $post_id, string $context): array
    {
        $name = $this->get_name();

        if (isset($options[$name])) {
            return $options;
        }

        $options[$name] = array(
            'id' => $name,
            'label' => wp_seed_content_divi_testimonial_dynamic_content_label($this->get_label()),
            'type' => $this->get_dynamic_content_type(),
            'custom' => false,
            'group' => wp_seed_content_divi_testimonial_dynamic_content_group_label(),
            'fields' => array(),
        );

        return $options;
    }

    public function render_callback($value, array $data_args = array()): string
    {
        $name = isset($data_args['name']) && is_string($data_args['name'])
            ? $data_args['name']
            : '';

        if (!wp_seed_content_divi_testimonial_dynamic_content_name_matches($this->get_name(), $name)) {
            return $value;
        }

        $resolver_context = wp_seed_content_divi_get_dynamic_content_context(
            $data_args,
            'seed_testimonial'
        );

        if (wp_seed_content_divi_should_defer_dynamic_content($value, $resolver_context)) {
            return $value;
        }

        $resolved_value = '';

        if (function_exists('wp_seed_content_resolve_dynamic_data')) {
            $resolved_value = wp_seed_content_resolve_dynamic_data(
                $this->get_dynamic_data_field_id(),
                $resolver_context
            );
        }

        if (is_wp_error($resolved_value) || !is_scalar($resolved_value)) {
            $resolved_value = '';
        } elseif (is_bool($resolved_value)) {
            $resolved_value = $resolved_value ? '1' : '';
        } else {
            $resolved_value = (string) $resolved_value;
        }

        $settings = isset($data_args['settings']) && is_array($data_args['settings'])
            ? $data_args['settings']
            : array();

        return DynamicContentElements::get_wrapper_element(
            array(
                'name' => $name,
                'post_id' => wp_seed_content_divi_get_dynamic_content_wrapper_post_id($data_args),
                'value' => $resolved_value,
                'settings' => $settings,
            )
        );
    }

}

function wp_seed_content_divi_testimonial_dynamic_content_group_label(): string
{
    return __('WPSCK — Témoignages', 'wp-seed-content-kit');
}

function wp_seed_content_divi_testimonial_dynamic_content_label($label): string
{
    return wp_seed_content_divi_testimonial_dynamic_content_group_label() . ' — ' . $label;
}

function wp_seed_content_divi_testimonial_dynamic_content_name_matches($canonical_name, $candidate_name): bool
{
    return wp_seed_content_divi_dynamic_content_name_matches($canonical_name, $candidate_name);
}
