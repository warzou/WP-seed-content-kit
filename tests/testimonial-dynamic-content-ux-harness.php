<?php

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
        public function load()
        {
            $GLOBALS['wpsck_loaded_providers'][] = $this->get_name();
        }
    }

    interface DynamicContentOptionInterface
    {
    }
}

namespace {
    define('ABSPATH', __DIR__ . '/');
    define('WPSCK_TEST_FIXTURE_PAGE_ID', 900001);

    $GLOBALS['wpsck_loaded_providers'] = array();
    $GLOBALS['wpsck_assertions'] = 0;
    $GLOBALS['wpsck_testimonial_dates'] = array(
        2909 => '2024-02-29',
        2912 => '',
        2915 => '',
    );
    $GLOBALS['wpsck_testimonial_raw_dates'] = array(
        2909 => '2024-02-29',
        2912 => '',
        2915 => '2026-02-31',
    );
    $GLOBALS['wpsck_post_dates'] = array(
        2909 => '2026-08-09 10:00:00',
        2912 => '2026-08-09 11:00:00',
        2915 => '2026-08-09 12:00:00',
    );

    class WP_Post
    {
        public $ID;
        public $post_type;

        public function __construct($id, $post_type = 'seed_testimonial')
        {
            $this->ID = (int) $id;
            $this->post_type = $post_type;
        }
    }

    function __($text, $domain = 'default')
    {
        return $text;
    }

    function add_action($hook, $callback, $priority = 10, $accepted_args = 1)
    {
    }

    function is_wp_error($value)
    {
        return false;
    }

    function get_post($post_id)
    {
        if ((int) $post_id <= 0) {
            return null;
        }

        if ((int) $post_id >= 4001 && (int) $post_id <= 4003) {
            return new WP_Post($post_id, 'seed_quote');
        }

        return new WP_Post($post_id, WPSCK_TEST_FIXTURE_PAGE_ID === (int) $post_id ? 'page' : 'seed_testimonial');
    }

    function _wp_seed_content_normalize_dynamic_data_post_id($post_id)
    {
        return (int) $post_id > 0 ? (int) $post_id : 0;
    }

    function wp_seed_content_resolve_dynamic_data($field_id, $context = array())
    {
        $post_id = isset($context['current_post_id']) ? (int) $context['current_post_id'] : 0;
        if ('testimonial.photo' === $field_id) {
            return array(
                'url' => 'https://example.test/testimonial-' . $post_id . '.jpg',
                'mime_type' => 'image/jpeg',
            );
        }
        if ('testimonial.id' === $field_id) {
            return (string) $post_id;
        }
        if ('testimonial.anchor' === $field_id) {
            return 'temoignage-' . $post_id;
        }
        if ('testimonial.testimonial_date' === $field_id) {
            return isset($GLOBALS['wpsck_testimonial_dates'][$post_id])
                ? $GLOBALS['wpsck_testimonial_dates'][$post_id]
                : '';
        }

        return $post_id > 0 ? $field_id . ':' . $post_id : '';
    }

    function wpsck_assert($condition, $message)
    {
        $GLOBALS['wpsck_assertions']++;
        if (!$condition) {
            throw new \RuntimeException($message);
        }
    }

    $root = dirname(__DIR__);
    require $root . '/plugin/includes/integrations/divi/class-dynamic-content-testimonial-base.php';
    require $root . '/plugin/includes/integrations/divi/class-dynamic-content-testimonial-loop-fields.php';
    require $root . '/plugin/includes/integrations/divi/class-dynamic-content-testimonial-text.php';
    require $root . '/plugin/includes/integrations/divi/class-dynamic-content-testimonial-name.php';
    require $root . '/plugin/includes/integrations/divi/class-dynamic-content-testimonial-context.php';
    require $root . '/plugin/includes/integrations/divi/class-dynamic-content-testimonial-date.php';
    require $root . '/plugin/includes/integrations/divi/class-dynamic-content-testimonial-photo.php';
    require $root . '/plugin/includes/integrations/divi/dynamic-content.php';

    wp_seed_content_load_divi_dynamic_content_testimonial_fields();

    $expected_names = array(
        'loop_wpsck_testimonial_visual',
        'loop_wpsck_testimonial_title',
        'loop_wpsck_testimonial_summary',
        'loop_wpsck_testimonial_full',
        'loop_wpsck_testimonial_name',
        'loop_wpsck_testimonial_context',
        'loop_wpsck_testimonial_date',
        'loop_wpsck_testimonial_id',
        'loop_wpsck_testimonial_anchor',
    );
    wpsck_assert($expected_names === $GLOBALS['wpsck_loaded_providers'], 'Exactly nine WPSCK providers must load.');
    wpsck_assert(!in_array('wp_seed_content_testimonial_anchor_url', $GLOBALS['wpsck_loaded_providers'], true), 'URL provider must not load.');

    $providers = array(
        array(new WP_Seed_Content_Divi_Dynamic_Content_Testimonial_Photo(), 'WPSCK — Témoignages — Visuel', 'image'),
        array(new WP_Seed_Content_Divi_Dynamic_Content_Testimonial_Title(), 'WPSCK — Témoignages — Titre', 'text'),
        array(new WP_Seed_Content_Divi_Dynamic_Content_Testimonial_Summary(), 'WPSCK — Témoignages — Résumé', 'text'),
        array(new WP_Seed_Content_Divi_Dynamic_Content_Testimonial_Text(), 'WPSCK — Témoignages — Témoignage complet', 'text'),
        array(new WP_Seed_Content_Divi_Dynamic_Content_Testimonial_Name(), 'WPSCK — Témoignages — Nom', 'text'),
        array(new WP_Seed_Content_Divi_Dynamic_Content_Testimonial_Context(), 'WPSCK — Témoignages — Contexte', 'text'),
        array(new WP_Seed_Content_Divi_Dynamic_Content_Testimonial_Date(), 'WPSCK — Témoignages — Date', 'text'),
        array(new WP_Seed_Content_Divi_Dynamic_Content_Testimonial_Id(), 'WPSCK — Témoignages — ID', 'text'),
        array(new WP_Seed_Content_Divi_Dynamic_Content_Testimonial_Anchor(), 'WPSCK — Témoignages — Ancre', 'text'),
    );

    foreach ($providers as $definition) {
        list($provider, $label, $type) = $definition;
        $options = $provider->register_option_callback(array(), 0, 'content');
        $option = $options[$provider->get_name()];
        wpsck_assert('WPSCK — Témoignages' === $option['group'], 'Provider group differs.');
        wpsck_assert($label === $option['label'], 'Provider label differs.');
        wpsck_assert($type === $option['type'], 'Provider type differs.');

        $preview_values = array();
        foreach (array(2909, 2912, 2915) as $post_id) {
            $value = $provider->render_callback('', array(
                'name' => $provider->get_name(),
                'loop_id' => $post_id,
                'post_id' => WPSCK_TEST_FIXTURE_PAGE_ID,
            ));
            if ('loop_wpsck_testimonial_date' === $provider->get_name()) {
                $expected_date = isset($GLOBALS['wpsck_testimonial_dates'][$post_id])
                    ? $GLOBALS['wpsck_testimonial_dates'][$post_id]
                    : '';
                wpsck_assert($expected_date === $value, 'Optional testimonial date differs.');
            } else {
                wpsck_assert('' !== $value, 'Loop preview must not be empty.');
            }
            wpsck_assert(false === strpos($value, '$variable('), 'Raw variable leaked.');
            $preview_values[] = $value;
        }

        if ('loop_wpsck_testimonial_date' !== $provider->get_name()) {
            wpsck_assert(3 === count(array_unique($preview_values)), 'Loop preview values for items 1, 2 and 3 must be distinct.');
        }

        $deferred_value = '$variable({"type":"content","value":{"name":"'
            . $provider->get_name()
            . '","settings":[]}})$';
        wpsck_assert(
            $deferred_value === $provider->render_callback($deferred_value, array(
                'name' => $provider->get_name(),
                'post_id' => WPSCK_TEST_FIXTURE_PAGE_ID,
            )),
            'A nested Group Loop provider must remain deferred until Divi supplies its loop item context.'
        );

        $loop_object_value = $provider->render_callback('', array(
            'name' => $provider->get_name(),
            'loop_object' => new WP_Post(2909),
            'post_id' => WPSCK_TEST_FIXTURE_PAGE_ID,
        ));
        if ('loop_wpsck_testimonial_date' === $provider->get_name()) {
            wpsck_assert('2024-02-29' === $loop_object_value, 'Loop object date context differs.');
        } else {
            wpsck_assert('' !== $loop_object_value, 'Loop object context must resolve the provider.');
        }
    }
    require_once $root . '/plugin/includes/integrations/divi/class-dynamic-content-quote-base.php';
    require_once $root . '/plugin/includes/integrations/divi/class-dynamic-content-quote-text.php';
    require_once $root . '/plugin/includes/integrations/divi/class-dynamic-content-quote-author.php';
    require_once $root . '/plugin/includes/integrations/divi/class-dynamic-content-quote-era.php';
    require_once $root . '/plugin/includes/integrations/divi/class-dynamic-content-quote-source.php';

    $quote_providers = array(
        array(new WP_Seed_Content_Divi_Dynamic_Content_Quote_Text(), 'WPSCK — Citations — Texte'),
        array(new WP_Seed_Content_Divi_Dynamic_Content_Quote_Author(), 'WPSCK — Citations — Auteur'),
        array(new WP_Seed_Content_Divi_Dynamic_Content_Quote_Era(), 'WPSCK — Citations — Époque'),
        array(new WP_Seed_Content_Divi_Dynamic_Content_Quote_Source(), 'WPSCK — Citations — Source'),
    );
    $expected_quote_names = array(
        'loop_wpsck_quote_text',
        'loop_wpsck_quote_author',
        'loop_wpsck_quote_era',
        'loop_wpsck_quote_source',
    );
    wpsck_assert(
        $expected_quote_names === array_map(function ($definition) {
            return $definition[0]->get_name();
        }, $quote_providers),
        'Quote providers must use the native Divi Loop namespace.'
    );
    foreach ($quote_providers as $definition) {
        list($provider, $label) = $definition;
        $options = $provider->register_option_callback(array(), 0, 'content');
        $option = $options[$provider->get_name()];
        wpsck_assert($label === $option['label'], 'Quote provider label differs.');
        wpsck_assert('text' === $option['type'], 'Quote provider type differs.');

        $values = array();
        foreach (array(4001, 4002, 4003) as $quote_id) {
            $values[] = $provider->render_callback('', array(
                'name' => $provider->get_name(),
                'loop_id' => $quote_id,
                'post_id' => WPSCK_TEST_FIXTURE_PAGE_ID,
            ));
        }
        wpsck_assert(3 === count(array_unique($values)), 'Quote loop values must remain distinct.');
        wpsck_assert(false === strpos(implode('', $values), '$variable('), 'Raw Quote token leaked.');

        $object_value = $provider->render_callback('', array(
            'name' => $provider->get_name(),
            'loop_object' => new WP_Post(4001, 'seed_quote'),
            'post_id' => WPSCK_TEST_FIXTURE_PAGE_ID,
        ));
        wpsck_assert('' !== $object_value, 'Nested Quote loop object did not resolve.');

        $deferred = '$variable({"type":"content","value":{"name":"'
            . $provider->get_name()
            . '","settings":[]}})$';
        wpsck_assert($deferred === $provider->render_callback($deferred, array(
            'name' => $provider->get_name(),
            'post_id' => WPSCK_TEST_FIXTURE_PAGE_ID,
        )), 'Quote provider must remain deferred without loop context.');
    }
    wpsck_assert(
        4 === count(array_unique(array_map(function ($definition) {
            return $definition[0]->get_name();
        }, $quote_providers))),
        'Quote provider IDs must remain unique.'
    );
    foreach (wp_seed_content_divi_legacy_loop_provider_names() as $legacy_name => $canonical_name) {
        if (0 !== strpos($canonical_name, 'loop_wpsck_quote_')) {
            continue;
        }
        wpsck_assert(
            wp_seed_content_divi_dynamic_content_name_matches($canonical_name, $legacy_name),
            'Legacy Quote provider binding must remain renderable.'
        );
    }

    $legacy_quote_token = '$variable({"type":"content","value":{"name":"wp_seed_content_quote_quote","settings":[]}})$';
    $canonical_quote_token = wp_seed_content_divi_normalize_legacy_loop_provider_tokens($legacy_quote_token);
    wpsck_assert(
        false !== strpos($canonical_quote_token, '"name":"loop_wpsck_quote_text"'),
        'Legacy Quote bindings must become native Loop bindings in the Builder response.'
    );

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
    foreach ($legacy_names as $canonical_name => $legacy_name) {
        wpsck_assert(wp_seed_content_divi_testimonial_dynamic_content_name_matches($canonical_name, $legacy_name), 'Legacy provider binding must remain renderable.');
    }

    $date_provider = new WP_Seed_Content_Divi_Dynamic_Content_Testimonial_Date();
    $render_date = function ($post_id) use ($date_provider) {
        return $date_provider->render_callback('', array(
            'name' => $date_provider->get_name(),
            'loop_id' => $post_id,
            'post_id' => WPSCK_TEST_FIXTURE_PAGE_ID,
        ));
    };
    wpsck_assert('2024-02-29' === $render_date(2909), 'Stored canonical testimonial date must resolve.');
    wpsck_assert('' === $render_date(2912), 'Missing testimonial date must resolve to an empty string.');
    wpsck_assert('' !== $GLOBALS['wpsck_post_dates'][2912], 'The missing-date fixture must have a WordPress post date.');
    wpsck_assert('' === $render_date(2912), 'WordPress post_date must not be used as a fallback.');
    wpsck_assert('2026-02-31' === $GLOBALS['wpsck_testimonial_raw_dates'][2915], 'The invalid-date fixture must retain its raw invalid value.');
    wpsck_assert('' === $render_date(2915), 'Invalid testimonial date must resolve to the canonical empty string.');

    $anchor_provider = new WP_Seed_Content_Divi_Dynamic_Content_Testimonial_Anchor();
    $anchors = array();
    foreach (range(2909, 2930) as $post_id) {
        $anchors[] = $anchor_provider->render_callback('', array(
            'name' => $anchor_provider->get_name(),
            'loop_id' => $post_id,
        ));
    }
    wpsck_assert(22 === count(array_unique($anchors)), 'Twenty-two anchors must be unique.');
    wpsck_assert('temoignage-2909' === $anchors[0], 'Anchor 2909 differs.');
    wpsck_assert('temoignage-2912' === $anchors[3], 'Anchor 2912 differs.');
    wpsck_assert('temoignage-2915' === $anchors[6], 'Anchor 2915 differs.');
    $next_anchor = $anchor_provider->render_callback('', array(
        'name' => $anchor_provider->get_name(),
        'loop_id' => 2931,
    ));
    wpsck_assert(!in_array($next_anchor, $anchors, true), 'Twenty-third anchor must be unique.');
    wpsck_assert('temoignage-2909' === $anchor_provider->render_callback('', array(
        'name' => $anchor_provider->get_name(),
        'loop_id' => 2909,
        'title' => 'Changed title',
    )), 'Anchor must remain stable after title changes.');

    echo 'Testimonial Dynamic Content UX harness: '
        . $GLOBALS['wpsck_assertions']
        . ' assertions OK'
        . PHP_EOL;
}
