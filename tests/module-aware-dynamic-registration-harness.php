<?php

declare(strict_types=1);

namespace ET\Builder\Packages\Module\Layout\Components\DynamicContent {
    class DynamicContentElements
    {
        public static function get_wrapper_element(array $args): string
        {
            return isset($args['value']) ? (string) $args['value'] : '';
        }
    }

    abstract class DynamicContentOptionBase
    {
        public function load()
        {
        }
    }

    interface DynamicContentOptionInterface
    {
    }
}

namespace {
    define('ABSPATH', __DIR__ . '/');
    define('WP_SEED_CONTENT_KIT_URL', 'https://example.test/wp-seed-content-kit/');
    define('WP_SEED_CONTENT_KIT_VERSION', 'test');

    $GLOBALS['wpsck_module_options'] = array(
        'testimonials' => true,
        'quotes' => true,
        'directory' => true,
    );
    $GLOBALS['wpsck_actions'] = array();
    $GLOBALS['wpsck_filters'] = array();
    $GLOBALS['wpsck_packages'] = array();
    $GLOBALS['wpsck_published_post_counts'] = array(
        'seed_quote' => 0,
        'seed_testimonial' => 0,
        'seed_directory' => 0,
    );
    $GLOBALS['wpsck_assertions'] = 0;

    class WP_Error
    {
    }

    class WP_Post
    {
        public $ID;
        public $post_type;

        public function __construct($id, $post_type)
        {
            $this->ID = (int) $id;
            $this->post_type = $post_type;
        }
    }

    class WP_Block
    {
        public $name = 'core/paragraph';
        public $context;

        public function __construct($post_type = 'seed_testimonial')
        {
            $this->context = array('postId' => 42, 'postType' => $post_type);
        }
    }

    function __($value, $domain = null) { return $value; }
    function sanitize_key($value) { return strtolower(preg_replace('/[^a-z0-9_\-]/', '', (string) $value)); }
    function get_option($key, $default = array()) { return 'wp_seed_content_kit_modules' === $key ? $GLOBALS['wpsck_module_options'] : $default; }
    function wp_parse_args($args, $defaults = array()) { return array_merge($defaults, is_array($args) ? $args : array()); }
    function add_action($hook, $callback, $priority = 10, $accepted_args = 1) { $GLOBALS['wpsck_actions'][$hook][] = $callback; }
    function add_filter($hook, $callback, $priority = 10, $accepted_args = 1) { $GLOBALS['wpsck_filters'][$hook][] = $callback; }
    function is_wp_error($value) { return $value instanceof WP_Error; }
    function get_the_ID() { return 42; }
    function get_post($id) { return new WP_Post($id, 'seed_testimonial'); }
    function et_core_is_fb_enabled() { return true; }
    function register_block_bindings_source($name, $args) { $GLOBALS['wpsck_binding_source'] = array($name, $args); }
    function wp_seed_content_divi_dynamic_content_name_matches($canonical, $candidate) { return $canonical === $candidate; }
    function wp_seed_content_divi_get_dynamic_content_context($args, $post_type) { return array('current_post_id' => 42, 'current_post_type' => $post_type); }
    function wp_seed_content_divi_should_defer_dynamic_content($value, $context) { return false !== strpos((string) $value, '$variable('); }
    function wp_seed_content_divi_get_dynamic_content_wrapper_post_id($args) { return 42; }
    function wp_seed_content_get_testimonial_data($id, $args = array()) { return array('name' => 'Ada', 'has_more' => true); }
    function wp_seed_content_get_quote_data($id, $args = array()) { return array('author' => 'Ada'); }
    function wp_seed_content_get_directory_data($id, $args = array()) { return !empty($GLOBALS['wpsck_module_options']['directory']) ? array('name' => 'Ada') : array(); }
    function wp_seed_content_directory_individual_contact_provider_definitions() { return array(); }

    eval('namespace ET\\Builder\\VisualBuilder\\Assets; class PackageBuildManager { public static function register_package_build($params) { $GLOBALS["wpsck_packages"][$params["name"]] = $params; } }');

    function wpsck_module_assert($condition, $message)
    {
        $GLOBALS['wpsck_assertions']++;
        if (!$condition) {
            throw new \RuntimeException($message);
        }
    }

    $root = dirname(__DIR__);
    require $root . '/plugin/includes/core/modules.php';
    require $root . '/plugin/includes/core/dynamic-data.php';
    require $root . '/plugin/includes/integrations/divi/class-dynamic-content-quote-base.php';
    require $root . '/plugin/includes/integrations/divi/class-dynamic-content-testimonial-base.php';
    require $root . '/plugin/includes/integrations/divi/class-dynamic-content-testimonial-photo.php';
    require $root . '/plugin/includes/integrations/divi/class-dynamic-content-directory-base.php';
    require $root . '/plugin/includes/integrations/divi/testimonial-has-more-condition.php';
    require $root . '/plugin/includes/integrations/divi/directory-has-more-condition.php';

    class WPSCK_Test_Quote_Provider extends WP_Seed_Content_Divi_Dynamic_Content_Quote_Base
    {
        public function get_name(): string { return 'loop_wpsck_quote_author'; }
        public function get_label(): string { return 'Auteur'; }
        protected function get_dynamic_data_field_id(): string { return 'quote.author'; }
    }

    class WPSCK_Test_Testimonial_Provider extends WP_Seed_Content_Divi_Dynamic_Content_Testimonial_Base
    {
        public function get_name(): string { return 'loop_wpsck_testimonial_name'; }
        public function get_label(): string { return 'Nom'; }
        protected function get_dynamic_data_field_id(): string { return 'testimonial.name'; }
    }

    class WPSCK_Test_Directory_Provider extends WP_Seed_Content_Divi_Dynamic_Content_Directory_Base
    {
        public function get_name(): string { return 'loop_wpsck_directory_name'; }
        public function get_label(): string { return 'Nom'; }
        protected function get_dynamic_data_field_id(): string { return 'directory.name'; }
    }

    $providers = array(
        'quotes' => new WPSCK_Test_Quote_Provider(),
        'testimonials' => new WPSCK_Test_Testimonial_Provider(),
        'directory' => new WPSCK_Test_Directory_Provider(),
    );
    $tokens = array(
        'quotes' => '$variable({"name":"loop_wpsck_quote_author"})$',
        'testimonials' => '$variable({"name":"loop_wpsck_testimonial_name"})$',
        'directory' => '$variable({"name":"loop_wpsck_directory_name"})$',
    );

    wpsck_module_assert(0 === $GLOBALS['wpsck_published_post_counts']['seed_quote'], 'Citation zero-data precondition changed');
    $quote_provider_name = $providers['quotes']->get_name();
    wpsck_module_assert(isset($providers['quotes']->register_option_callback(array(), 0, 'content')[$quote_provider_name]), 'Citation discovery incorrectly depends on a published post');

    foreach ($providers as $module => $provider) {
        $name = $provider->get_name();
        wpsck_module_assert(isset($provider->register_option_callback(array(), 0, 'content')[$name]), $module . ' enabled discovery missing');

        $GLOBALS['wpsck_module_options'][$module] = false;
        wpsck_module_assert(array() === $provider->register_option_callback(array(), 0, 'content'), $module . ' disabled provider remains discoverable');
        wpsck_module_assert('' === $provider->render_callback($tokens[$module], array('name' => $name)), $module . ' disabled binding leaked a raw token');

        $GLOBALS['wpsck_module_options'][$module] = true;
        wpsck_module_assert(isset($provider->register_option_callback(array(), 0, 'content')[$name]), $module . ' provider did not return after re-enable');
        wpsck_module_assert('' !== $provider->render_callback('', array('name' => $name)), $module . ' binding did not resolve after re-enable');
    }

    $photo_provider = new WP_Seed_Content_Divi_Dynamic_Content_Testimonial_Photo();
    $photo_name = $photo_provider->get_name();
    $GLOBALS['wpsck_module_options']['testimonials'] = false;
    wpsck_module_assert(array() === $photo_provider->register_option_callback(array(), 0, 'content'), 'Disabled testimonial image remains discoverable');
    wpsck_module_assert('' === $photo_provider->render_callback('$variable({"name":"loop_wpsck_testimonial_visual"})$', array('name' => $photo_name)), 'Disabled testimonial image leaked a raw token');
    $GLOBALS['wpsck_module_options']['testimonials'] = true;
    wpsck_module_assert(isset($photo_provider->register_option_callback(array(), 0, 'content')[$photo_name]), 'Testimonial image did not return after re-enable');

    $GLOBALS['wpsck_module_options']['testimonials'] = false;
    $discoverable = wp_seed_content_get_discoverable_dynamic_data_fields();
    $all_fields = wp_seed_content_get_dynamic_data_fields();
    wpsck_module_assert(!isset($discoverable['testimonial.name']), 'Disabled testimonial field remains discoverable');
    wpsck_module_assert(isset($all_fields['testimonial.name']), 'Historical testimonial field left the stable registry');
    wpsck_module_assert('Ada' === wp_seed_content_resolve_dynamic_data('testimonial.name', array('current_post_id' => 42, 'current_post_type' => 'seed_testimonial')), 'Content Data compatibility was incorrectly gated');
    $GLOBALS['wpsck_module_options']['quotes'] = false;
    wpsck_module_assert('Ada' === wp_seed_content_resolve_dynamic_data('quote.author', array('current_post_id' => 42, 'current_post_type' => 'seed_quote')), 'Quote Content Data compatibility was incorrectly gated');
    $GLOBALS['wpsck_module_options']['directory'] = false;
    wpsck_module_assert('' === wp_seed_content_resolve_dynamic_data('directory.name', array('current_post_id' => 42, 'current_post_type' => 'seed_directory')), 'Disabled Directory Content Data was not safely empty');

    require $root . '/plugin/includes/integrations/gutenberg/block-bindings.php';
    $block_value = wp_seed_content_get_gutenberg_binding_value(array('field_id' => 'testimonial.name'), new WP_Block(), 'content');
    wpsck_module_assert('Ada' === $block_value, 'Historical Block Binding did not remain readable');
    wpsck_module_assert('Ada' === wp_seed_content_get_gutenberg_binding_value(array('field_id' => 'quote.author'), new WP_Block('seed_quote'), 'content'), 'Historical Quote Block Binding did not remain readable');
    wpsck_module_assert('' === wp_seed_content_get_gutenberg_binding_value(array('field_id' => 'directory.name'), new WP_Block('seed_directory'), 'content'), 'Disabled Directory Block Binding was not safely empty');
    $GLOBALS['wpsck_module_options']['directory'] = true;
    wpsck_module_assert('Ada' === wp_seed_content_get_gutenberg_binding_value(array('field_id' => 'directory.name'), new WP_Block('seed_directory'), 'content'), 'Directory Block Binding did not recover after re-enable');

    $condition_name = wp_seed_content_divi_testimonial_has_more_condition_name();
    $condition_key = wp_seed_content_divi_testimonial_has_more_condition_value_key();
    wpsck_module_assert(false === wp_seed_content_divi_testimonial_evaluate_has_more_condition(null, $condition_name, array($condition_key => '1'), 'disabled'), 'Disabled testimonial condition was not safe false');

    $asset_callbacks = $GLOBALS['wpsck_actions']['divi_visual_builder_assets_before_enqueue_scripts'];
    call_user_func($asset_callbacks[0]);
    wpsck_module_assert(empty($GLOBALS['wpsck_packages']), 'Disabled testimonial condition remains discoverable');
    $GLOBALS['wpsck_module_options']['testimonials'] = true;
    call_user_func($asset_callbacks[0]);
    wpsck_module_assert(isset($GLOBALS['wpsck_packages']['wp-seed-content-kit-divi-testimonial-has-more-condition']), 'Testimonial condition did not return after re-enable');

    $GLOBALS['wpsck_module_options']['directory'] = false;
    $directory_condition_name = wp_seed_content_divi_directory_has_more_condition_name();
    $directory_condition_key = wp_seed_content_divi_directory_has_more_condition_value_key();
    wpsck_module_assert(false === wp_seed_content_divi_directory_evaluate_has_more_condition(null, $directory_condition_name, array($directory_condition_key => 'More'), 'disabled'), 'Disabled directory condition was not safe false');
    call_user_func($asset_callbacks[1]);
    wpsck_module_assert(!isset($GLOBALS['wpsck_packages']['wp-seed-content-kit-divi-directory-has-more-condition']), 'Disabled directory condition remains discoverable');
    $GLOBALS['wpsck_module_options']['directory'] = true;
    call_user_func($asset_callbacks[1]);
    wpsck_module_assert(isset($GLOBALS['wpsck_packages']['wp-seed-content-kit-divi-directory-has-more-condition']), 'Directory condition did not return after re-enable');

    wpsck_module_assert(true === wp_seed_content_kit_is_module_active('cards'), 'Always-on Cards contract changed');
    wpsck_module_assert(false === wp_seed_content_kit_is_module_active('audio'), 'Planned Audio module became activatable');

    echo 'PASS ' . $GLOBALS['wpsck_assertions'] . ' module-aware dynamic registration assertions' . PHP_EOL;
}
