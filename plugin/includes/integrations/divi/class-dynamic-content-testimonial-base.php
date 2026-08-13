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

        $resolver_context = $this->get_resolver_context($data_args);

        if (
            empty($resolver_context['current_post_id'])
            && is_string($value)
            && false !== strpos($value, '$variable(')
        ) {
            return $value;
        }

        $resolved_value = '';

        if (function_exists('wp_seed_content_resolve_dynamic_data')) {
            $resolved_value = wp_seed_content_resolve_dynamic_data(
                $this->get_dynamic_data_field_id(),
                $resolver_context
            );
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

        if (isset($data_args['loop_object'])) {
            $loop_object = $data_args['loop_object'];
            if ($loop_object instanceof WP_Post) {
                return $this->get_resolver_context_for_post_id($loop_object->ID);
            }
            if (is_object($loop_object) && isset($loop_object->ID)) {
                return $this->get_resolver_context_for_post_id($loop_object->ID);
            }
            if (is_array($loop_object)) {
                $loop_post_id = isset($loop_object['ID'])
                    ? $loop_object['ID']
                    : (isset($loop_object['id']) ? $loop_object['id'] : 0);

                return $this->get_resolver_context_for_post_id($loop_post_id);
            }
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
        if (!$post instanceof WP_Post || 'seed_testimonial' !== $post->post_type) {
            return array('current_post_id' => 0);
        }

        return array(
            'current_post_id' => (int) $post->ID,
            'current_post_type' => 'seed_testimonial',
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
    if ($canonical_name === $candidate_name) {
        return true;
    }

    $legacy_names = array(
        'loop_wpsck_testimonial_visual' => 'wp_seed_content_testimonial_photo',
        'loop_wpsck_testimonial_title' => 'wp_seed_content_testimonial_title',
        'loop_wpsck_testimonial_summary' => 'wp_seed_content_testimonial_summary',
        'loop_wpsck_testimonial_full' => 'wp_seed_content_testimonial_text',
        'loop_wpsck_testimonial_name' => 'wp_seed_content_testimonial_name',
        'loop_wpsck_testimonial_context' => 'wp_seed_content_testimonial_context',
        'loop_wpsck_testimonial_date' => 'wp_seed_content_testimonial_date',
        'loop_wpsck_testimonial_id' => 'wp_seed_content_testimonial_id',
        'loop_wpsck_testimonial_anchor' => 'wp_seed_content_testimonial_anchor',
    );

    return isset($legacy_names[$canonical_name]) && $legacy_names[$canonical_name] === $candidate_name;
}
