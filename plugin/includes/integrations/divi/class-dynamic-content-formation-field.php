<?php
use ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContentElements;
use ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContentOptionBase;
use ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContentOptionInterface;
if (!defined('ABSPATH')) { exit; }

class WP_Seed_Content_Divi_Dynamic_Content_Formation_Field extends DynamicContentOptionBase implements DynamicContentOptionInterface
{
    private $name;
    private $field;
    public function __construct($name, $field) { $this->name = 'loop_' . $name; $this->field = $field; }
    public function get_name(): string { return $this->name; }
    public function get_label(): string { return __('WPSCK — Calendrier', 'wp-seed-content-kit') . ' — ' . $this->field['label']; }
    public function register_option_callback(array $options, int $post_id, string $context): array
    {
        if (wp_seed_content_kit_is_module_available_for_discovery('formation')) $options[$this->name] = array(
            'id' => $this->name, 'label' => $this->get_label(), 'type' => $this->field['type'],
            'custom' => false, 'group' => __('WPSCK — Calendrier', 'wp-seed-content-kit'), 'fields' => array(),
        );
        return $options;
    }
    public function render_callback($value, array $data_args = array()): string
    {
        if (!isset($data_args['name']) || $data_args['name'] !== $this->name) return $value;
        $context = wp_seed_content_divi_get_dynamic_content_context($data_args, 'seed_schedule');
        $resolved = '';
        if (wp_seed_content_kit_should_resolve_module_binding('formation')) {
            if (wp_seed_content_divi_should_defer_dynamic_content($value, $context)) return $value;
            $resolved = wp_seed_content_resolve_dynamic_data($this->field['id'], $context);
        }
        if (is_wp_error($resolved) || !is_scalar($resolved)) $resolved = '';
        return DynamicContentElements::get_wrapper_element(array(
            'name' => $this->name, 'post_id' => wp_seed_content_divi_get_dynamic_content_wrapper_post_id($data_args),
            'value' => (string) $resolved, 'settings' => isset($data_args['settings']) && is_array($data_args['settings']) ? $data_args['settings'] : array(),
        ));
    }
}
