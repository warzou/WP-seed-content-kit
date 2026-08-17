<?php

use ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContentElements;
use ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContentOptionBase;
use ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContentOptionInterface;

if (!defined('ABSPATH')) {
    exit;
}

abstract class WP_Seed_Content_Divi_Dynamic_Content_Directory_Base extends DynamicContentOptionBase implements DynamicContentOptionInterface
{
    abstract protected function get_dynamic_data_field_id(): string;

    protected function get_dynamic_content_type(): string
    {
        return 'text';
    }

    public function register_option_callback(array $options, int $post_id, string $context): array
    {
        $name = $this->get_name();
        if (!isset($options[$name])) {
            $options[$name] = array(
                'id' => $name,
                'label' => __('WPSCK — Annuaire — ', 'wp-seed-content-kit') . $this->get_label(),
                'type' => $this->get_dynamic_content_type(),
                'custom' => false,
                'group' => __('WPSCK — Annuaire', 'wp-seed-content-kit'),
                'fields' => array(),
            );
        }

        return $options;
    }

    public function render_callback($value, array $data_args = array()): string
    {
        $name = isset($data_args['name']) && is_string($data_args['name']) ? $data_args['name'] : '';
        if (!wp_seed_content_divi_dynamic_content_name_matches($this->get_name(), $name)) {
            return $value;
        }

        $context = wp_seed_content_divi_get_dynamic_content_context($data_args, 'seed_directory');
        if (wp_seed_content_divi_should_defer_dynamic_content($value, $context)) {
            return $value;
        }

        $resolved = function_exists('wp_seed_content_resolve_dynamic_data')
            ? wp_seed_content_resolve_dynamic_data($this->get_dynamic_data_field_id(), $context)
            : '';
        if ('image' === $this->get_dynamic_content_type()) {
            $resolved = !is_wp_error($resolved) && is_array($resolved) && isset($resolved['url'])
                ? (string) $resolved['url']
                : '';
        } elseif (is_wp_error($resolved) || !is_scalar($resolved)) {
            $resolved = '';
        } else {
            $resolved = (string) $resolved;
        }

        return DynamicContentElements::get_wrapper_element(array(
            'name' => $name,
            'post_id' => wp_seed_content_divi_get_dynamic_content_wrapper_post_id($data_args),
            'value' => $resolved,
            'settings' => isset($data_args['settings']) && is_array($data_args['settings']) ? $data_args['settings'] : array(),
        ));
    }
}
