<?php
/**
 * Standalone assertions for the Divi testimonial Loop Builder adapter.
 *
 * Run with: php tests/divi-testimonial-loop-collection-harness.php
 */

declare(strict_types=1);

namespace ET\Builder\Packages\Module\Layout\Components\DynamicContent {
    class DynamicContentElements
    {
        public static function get_wrapper_element(array $args): string
        {
            return (string) ($args['value'] ?? '');
        }
    }

    abstract class DynamicContentOptionBase
    {
    }

    interface DynamicContentOptionInterface
    {
    }
}

namespace {
    define('ABSPATH', __DIR__ . '/');

    $GLOBALS['testimonial_loop_filters'] = array();
    $GLOBALS['testimonial_loop_calls'] = array();
    $GLOBALS['testimonial_loop_cases'] = 0;
    $GLOBALS['testimonial_provider_contexts'] = array();

    class WP_Post
    {
        public $ID;
        public $post_type;
        public $post_status = 'publish';
        public $post_password = '';

        public function __construct($id, $post_type)
        {
            $this->ID = (int) $id;
            $this->post_type = (string) $post_type;
        }
    }


    function __($text, $domain = 'default')
    {
        return $text;
    }

    function add_filter($hook, $callback, $priority = 10, $accepted_args = 1)
    {
        $GLOBALS['testimonial_loop_filters'][$hook][] = compact(
            'callback',
            'priority',
            'accepted_args'
        );
    }

    function sanitize_key($value)
    {
        return preg_replace('/[^a-z0-9_\-]/', '', strtolower((string) $value));
    }

    function absint($value)
    {
        return abs((int) $value);
    }

    function is_wp_error($value)
    {
        return false;
    }

    function _wp_seed_content_normalize_dynamic_data_post_id($value)
    {
        return absint($value);
    }

    function get_post($post_id)
    {
        $post_id = absint($post_id);

        return $post_id > 0 ? new WP_Post($post_id, 'seed_testimonial') : null;
    }

    function wp_seed_content_resolve_dynamic_data($field_id, $context = array())
    {
        $GLOBALS['testimonial_provider_contexts'][] = $context;

        return isset($context['current_post_id'])
            ? 'testimonial-' . (int) $context['current_post_id']
            : '';
    }

    function wp_seed_content_get_testimonials($args = array())
    {
        $GLOBALS['testimonial_loop_calls'][] = $args;
        $context = isset($args['context']) ? (string) $args['context'] : '';

        if ('empty' === $context) {
            return array();
        }

        if ('single' === $context) {
            return array(17);
        }

        if ('second-loop' === $context) {
            return array(31, 30);
        }

        return 'desc' === strtolower((string) ($args['order'] ?? 'asc'))
            ? array(23, 19, 17)
            : array(17, 19, 23);
    }

    require dirname(__DIR__) . '/plugin/includes/integrations/divi/collection-query.php';
    require dirname(__DIR__) . '/plugin/includes/integrations/divi/loop-context.php';
    require dirname(__DIR__) . '/plugin/includes/integrations/divi/testimonial-collection-query.php';
    require dirname(__DIR__) . '/plugin/includes/integrations/divi/class-dynamic-content-testimonial-base.php';
    require dirname(__DIR__) . '/plugin/includes/integrations/divi/class-dynamic-content-testimonial-loop-fields.php';

    class WP_Seed_Content_Test_Loop_Provider extends WP_Seed_Content_Divi_Dynamic_Content_Testimonial_Base
    {
        public function get_name(): string
        {
            return 'wp-seed-content-testimonial-test';
        }

        public function get_label(): string
        {
            return 'Test';
        }

        protected function get_dynamic_data_field_id(): string
        {
            return 'testimonial.text';
        }
    }

    function testimonial_loop_assert($condition, $message)
    {
        if (!$condition) {
            throw new \RuntimeException($message);
        }
    }

    function testimonial_loop_case($label, $callback)
    {
        $GLOBALS['testimonial_loop_cases']++;
        $callback();
        echo 'ok ' . $GLOBALS['testimonial_loop_cases'] . ' - ' . $label . PHP_EOL;
    }

    function testimonial_loop_query($context = '')
    {
        return array(
            'post_type' => array('seed_testimonial'),
            'posts_per_page' => 3,
            'paged' => 1,
            'offset' => 2,
            'orderby' => 'wp_seed_content_testimonial_display_order',
            'order' => 'ASC',
            'meta_query' => array(
                array(
                    'key' => 'wp_seed_content_testimonial_featured',
                    'value' => 'all',
                    'compare' => '=',
                ),
                array(
                    'key' => 'wp_seed_content_testimonial_context',
                    'value' => $context,
                    'compare' => '=',
                ),
                'relation' => 'AND',
            ),
        );
    }

    testimonial_loop_case('required Divi hooks are registered once', function () {
        $hooks = array(
            'et_builder_loop_order_by_options_seed_testimonial',
            'divi_loop_data_before_execution',
            'divi_module_options_loop_post_type_results_query_args',
        );

        foreach ($hooks as $hook) {
            testimonial_loop_assert(
                1 === count($GLOBALS['testimonial_loop_filters'][$hook] ?? array()),
                'Hook registration differs: ' . $hook
            );
        }
    });


    testimonial_loop_case('order options are deterministic and not duplicated', function () {
        $options = wp_seed_content_divi_testimonial_collection_order_options(array());
        testimonial_loop_assert(5 === count($options), 'Order option count differs.');
        testimonial_loop_assert(
            'wp_seed_content_testimonial_display_order' === $options[0]['value'],
            'Default order option differs.'
        );
        testimonial_loop_assert(
            5 === count(wp_seed_content_divi_testimonial_collection_order_options($options)),
            'Order options were duplicated.'
        );
    });

    testimonial_loop_case('random order and selection mode reach the canonical collection', function () {
        $query = testimonial_loop_query();
        $query['orderby'] = 'wp_seed_content_testimonial_random';
        $args = wp_seed_content_divi_filter_testimonial_collection_rest_query_args(
            $query,
            array('order_by' => 'wp_seed_content_testimonial_random')
        );
        $call = end($GLOBALS['testimonial_loop_calls']);
        testimonial_loop_assert('random' === $call['selection_mode'], 'Random mode was not selected.');
        testimonial_loop_assert('divi-loop-builder-preview' === $call['random_seed'], 'Builder seed differs.');
        testimonial_loop_assert('post__in' === $args['orderby'], 'Random IDs are not authoritative.');
    });

    testimonial_loop_case('custom provider remains outside the native loop namespace', function () {
        $provider = new WP_Seed_Content_Test_Loop_Provider();
        $options = $provider->register_option_callback(array(), 0, 'content');
        $name = $provider->get_name();
        testimonial_loop_assert(isset($options[$name]), 'Custom provider missing.');
        testimonial_loop_assert(!isset($options['loop_' . $name]), 'Custom provider leaked into the native loop namespace.');
        testimonial_loop_assert('WPSCK — Témoignages' === $options[$name]['group'], 'Builder group differs.');
        testimonial_loop_assert('text' === $options[$name]['type'], 'Text provider type differs.');
        $value = $provider->render_callback('', array('name' => $name, 'loop_id' => 19));
        testimonial_loop_assert('testimonial-19' === $value, 'Loop item context differs.');
    });
    testimonial_loop_case('frontend loop uses canonical public IDs', function () {
        $loop = wp_seed_content_divi_filter_testimonial_collection_loop_data(
            array('query_args' => testimonial_loop_query())
        );
        $args = $loop['query_args'];

        testimonial_loop_assert(array(17, 19, 23) === $args['post__in'], 'IDs differ.');
        testimonial_loop_assert('post__in' === $args['orderby'], 'Order is not canonical.');
        testimonial_loop_assert('ASC' === $args['order'], 'Canonical ID order changed.');
        testimonial_loop_assert(3 === $args['posts_per_page'], 'Divi limit changed.');
        testimonial_loop_assert(1 === $args['paged'], 'Divi pagination changed.');
        testimonial_loop_assert(2 === $args['offset'], 'Divi offset changed.');
        testimonial_loop_assert(!isset($args['meta_query']), 'Virtual clauses leaked.');
    });

    testimonial_loop_case('Visual Builder query matches frontend', function () {
        $base = testimonial_loop_query('single');
        $frontend = wp_seed_content_divi_filter_testimonial_collection_loop_data(
            array('query_args' => $base)
        )['query_args'];
        $rest = $base;
        unset($rest['orderby']);
        $rest = wp_seed_content_divi_filter_testimonial_collection_rest_query_args(
            $rest,
            array('order_by' => 'wp_seed_content_testimonial_display_order')
        );

        testimonial_loop_assert(
            $frontend['post__in'] === $rest['post__in'],
            'Frontend and Visual Builder IDs differ.'
        );
    });

    testimonial_loop_case('zero, one and three items are bounded safely', function () {
        $empty = wp_seed_content_divi_apply_testimonial_collection_query(
            testimonial_loop_query('empty')
        );
        $single = wp_seed_content_divi_apply_testimonial_collection_query(
            testimonial_loop_query('single')
        );
        $three = wp_seed_content_divi_apply_testimonial_collection_query(
            testimonial_loop_query()
        );

        testimonial_loop_assert(array(0) === $empty['post__in'], 'Empty loop is unsafe.');
        testimonial_loop_assert(array(17) === $single['post__in'], 'Single loop differs.');
        testimonial_loop_assert(
            array(17, 19, 23) === $three['post__in'],
            'Three-item loop differs.'
        );
    });

    testimonial_loop_case('native inclusion and exclusion remain effective', function () {
        $query = testimonial_loop_query();
        $query['post__in'] = array(19, 23, 99);
        $query['post__not_in'] = array(23);
        $args = wp_seed_content_divi_apply_testimonial_collection_query($query);

        testimonial_loop_assert(array(19) === $args['post__in'], 'Native bounds changed.');
    });

    testimonial_loop_case('ordinary meta clauses remain active', function () {
        $query = testimonial_loop_query();
        $query['meta_query'][] = array(
            'key' => 'public_fixture',
            'value' => 'yes',
            'compare' => '=',
        );
        $args = wp_seed_content_divi_apply_testimonial_collection_query($query);

        testimonial_loop_assert(1 === count($args['meta_query']), 'Meta clause removed.');
        testimonial_loop_assert(
            'public_fixture' === $args['meta_query'][0]['key'],
            'Meta clause changed.'
        );
    });

    testimonial_loop_case('non-testimonial and mixed loops remain unchanged', function () {
        $ordinary = array('post_type' => array('post'), 'orderby' => 'date');
        $mixed = array('post_type' => array('post', 'seed_testimonial'), 'orderby' => 'date');

        testimonial_loop_assert(
            $ordinary === wp_seed_content_divi_apply_testimonial_collection_query($ordinary),
            'Ordinary loop changed.'
        );
        testimonial_loop_assert(
            $mixed === wp_seed_content_divi_apply_testimonial_collection_query($mixed),
            'Mixed loop changed.'
        );
    });

    testimonial_loop_case('two loops retain independent collection state', function () {
        $first = wp_seed_content_divi_apply_testimonial_collection_query(
            testimonial_loop_query()
        );
        $second = wp_seed_content_divi_apply_testimonial_collection_query(
            testimonial_loop_query('second-loop')
        );

        testimonial_loop_assert(array(17, 19, 23) === $first['post__in'], 'First loop changed.');
        testimonial_loop_assert(array(31, 30) === $second['post__in'], 'Second loop changed.');
    });

    testimonial_loop_case('existing provider resolves each loop_id without leakage', function () {
        $GLOBALS['testimonial_provider_contexts'] = array();
        $provider = new WP_Seed_Content_Test_Loop_Provider();
        $first = $provider->render_callback(
            '',
            array(
                'name' => $provider->get_name(),
                'loop_id' => 17,
                'post_id' => 999,
                'settings' => array(),
            )
        );
        $second = $provider->render_callback(
            '',
            array(
                'name' => $provider->get_name(),
                'loop_id' => 31,
                'post_id' => 999,
                'settings' => array(),
            )
        );

        testimonial_loop_assert('testimonial-17' === $first, 'First provider context differs.');
        testimonial_loop_assert('testimonial-31' === $second, 'Second provider context differs.');
        testimonial_loop_assert(
            17 === $GLOBALS['testimonial_provider_contexts'][0]['current_post_id'],
            'First context leaked.'
        );
        testimonial_loop_assert(
            31 === $GLOBALS['testimonial_provider_contexts'][1]['current_post_id'],
            'Second context leaked.'
        );
    });

    testimonial_loop_case('adapter has no storage, shortcode or card renderer', function () {
        $source = file_get_contents(
            dirname(__DIR__) . '/plugin/includes/integrations/divi/testimonial-collection-query.php'
        );

        foreach (
            array(
                'get_post_meta(',
                'update_post_meta(',
                'WP_Query(',
                'do_shortcode(',
                '<article',
                'testimonial-collection/Module.php',
            ) as $forbidden
        ) {
            testimonial_loop_assert(
                false === strpos($source, $forbidden),
                'Forbidden adapter dependency found: ' . $forbidden
            );
        }
    });

    testimonial_loop_case('public loops require canonical consent and preserve fallback module', function () {
        $query = wp_seed_content_divi_apply_testimonial_collection_query(
            testimonial_loop_query()
        );
        $collection_source = file_get_contents(
            dirname(__DIR__) . '/plugin/includes/core/collections.php'
        );
        $bootstrap_source = file_get_contents(
            dirname(__DIR__) . '/plugin/wp-seed-content-kit.php'
        );

        testimonial_loop_assert(
            array(17, 19, 23) === $query['post__in'],
            'Canonical historical testimonial population changed.'
        );
        testimonial_loop_assert(
            false !== strpos($collection_source, '_seed_testimonial_publication_consent'),
            'Canonical consent is missing from collection eligibility.'
        );
        testimonial_loop_assert(
            false !== strpos($bootstrap_source, "testimonial-collection.php';")
            && false !== strpos($bootstrap_source, "testimonial-collection-query.php';"),
            'Historical module and Loop adapter do not coexist.'
        );
    });
    echo 'Divi testimonial loop collection harness: '
        . $GLOBALS['testimonial_loop_cases']
        . '/'
        . $GLOBALS['testimonial_loop_cases']
        . ' OK'
        . PHP_EOL;
}
