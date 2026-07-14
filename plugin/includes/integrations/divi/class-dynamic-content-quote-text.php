<?php

use ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContentElements;
use ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContentOptionBase;
use ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContentOptionInterface;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Experimental Divi 5 Dynamic Content source for quote text.
 */
class WP_Seed_Content_Divi_Dynamic_Content_Quote_Text extends DynamicContentOptionBase implements DynamicContentOptionInterface
{
    public function get_name(): string
    {
        return 'wp_seed_content_quote_quote';
    }

    public function get_label(): string
    {
        return __('Texte', 'wp-seed-content-kit');
    }

    public function register_option_callback(array $options, int $post_id, string $context): array
    {
        $name = $this->get_name();

        if (isset($options[$name])) {
            return $options;
        }

        $options[$name] = array(
            'id' => $name,
            'label' => $this->get_label(),
            'type' => 'text',
            'custom' => false,
            'group' => __('WP Seed — Citations', 'wp-seed-content-kit'),
            'fields' => array(),
        );

        return $options;
    }

    public function render_callback($value, array $data_args = array()): string
    {
        $name = isset($data_args['name']) && is_string($data_args['name'])
            ? $data_args['name']
            : '';

        if ($this->get_name() !== $name) {
            return $value;
        }

        $resolver_context = $this->get_resolver_context($data_args);
        $resolved_value = '';

        if (function_exists('wp_seed_content_resolve_dynamic_data')) {
            $resolved_value = wp_seed_content_resolve_dynamic_data('quote.quote', $resolver_context);
        }

        if (is_wp_error($resolved_value) || !is_string($resolved_value)) {
            $resolved_value = '';
        }

        $settings = isset($data_args['settings']) && is_array($data_args['settings'])
            ? $data_args['settings']
            : array();

        return DynamicContentElements::get_wrapper_element(
            array(
                'name' => $name,
                'post_id' => $this->get_wrapper_post_id($data_args),
                'value' => $resolved_value,
                'settings' => $settings,
            )
        );
    }

    private function get_resolver_context(array $data_args): array
    {
        if (array_key_exists('loop_id', $data_args) && null !== $data_args['loop_id']) {
            return $this->get_resolver_context_for_post_id($data_args['loop_id']);
        }

        $post_id = array_key_exists('post_id', $data_args) ? $data_args['post_id'] : 0;

        return $this->get_resolver_context_for_post_id($post_id);
    }

    private function get_wrapper_post_id(array $data_args): int
    {
        if (
            !array_key_exists('post_id', $data_args)
            || !function_exists('_wp_seed_content_normalize_dynamic_data_post_id')
        ) {
            return 0;
        }

        return _wp_seed_content_normalize_dynamic_data_post_id($data_args['post_id']);
    }

    private function get_resolver_context_for_post_id($post_id): array
    {
        if (!function_exists('_wp_seed_content_normalize_dynamic_data_post_id')) {
            return array('current_post_id' => 0);
        }

        $post_id = _wp_seed_content_normalize_dynamic_data_post_id($post_id);
        if (!$post_id) {
            return array('current_post_id' => 0);
        }

        $post = get_post($post_id);
        if (!$post instanceof WP_Post || 'seed_quote' !== $post->post_type) {
            return array('current_post_id' => 0);
        }

        return array(
            'current_post_id' => (int) $post->ID,
            'current_post_type' => 'seed_quote',
        );
    }
}
