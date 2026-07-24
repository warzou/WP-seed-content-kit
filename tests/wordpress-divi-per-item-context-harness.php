<?php
/**
 * WordPress/Divi integration harness for per-item testimonial context.
 *
 * Requires an isolated WordPress with Divi 5.9.0 available.
 */

$wp_load = getenv('WP_SEED_WORDPRESS_LOAD');
if (!defined('ABSPATH')) {
    if (!is_string($wp_load) || '' === $wp_load || !is_file($wp_load)) {
        fwrite(STDERR, "Set WP_SEED_WORDPRESS_LOAD to an isolated WordPress wp-load.php.\n");
        exit(2);
    }
    require $wp_load;
}

$root = dirname(__DIR__);
$plugin_file = $root . '/plugin/wp-seed-content-kit.php';
$created_posts = array();
$assertions = 0;
$failures = array();
$previous_modules = get_option('wp_seed_content_kit_modules', null);

if (!class_exists('\ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContentOptionBase')) {
    fwrite(STDERR, "Divi 5 Dynamic Content is required in this isolated WordPress.\n");
    exit(2);
}

function seed_divi_item_wp_assert($condition, $label)
{
    global $assertions, $failures;
    $assertions++;
    if (!$condition) {
        $failures[] = $label;
    }
}

function seed_divi_item_wp_same($expected, $actual, $label)
{
    seed_divi_item_wp_assert(
        $expected === $actual,
        $label . ' expected=' . var_export($expected, true)
        . ' actual=' . var_export($actual, true)
    );
}

function seed_divi_item_wp_create_post($args)
{
    global $created_posts;
    $post_id = wp_insert_post($args, true);
    if (is_wp_error($post_id)) {
        throw new RuntimeException($post_id->get_error_message());
    }
    $created_posts[] = (int) $post_id;

    return (int) $post_id;
}

function seed_divi_item_wp_variable($name, $settings = array())
{
    return '$variable(' . wp_json_encode(
        array(
            'type' => 'content',
            'value' => array(
                'name' => $name,
                'settings' => $settings,
            ),
        ),
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    ) . ')$';
}

try {
    $modules = is_array($previous_modules)
        ? $previous_modules
        : array();
    $modules['testimonials'] = true;
    update_option('wp_seed_content_kit_modules', $modules);

    if (!function_exists('wp_seed_content_render_testimonial_collection')) {
        require $plugin_file;
        do_action('init');
    }

    if (!function_exists('wp_seed_content_prepare_divi_testimonial_layout_content')) {
        throw new RuntimeException('Per-item Divi context component unavailable.');
    }

    $testimonial_ids = array();
    $fixtures = array(
        array('name' => 'Alpha', 'context' => 'Paris', 'date' => '2026-01-01'),
        array('name' => 'Bravo', 'context' => 'Lyon', 'date' => '2026-02-02'),
        array('name' => 'Charlie', 'context' => 'Metz', 'date' => '2026-03-03'),
    );
    foreach ($fixtures as $index => &$fixture) {
        $testimonial_ids[] = seed_divi_item_wp_create_post(
            array(
                'post_type' => 'seed_testimonial',
                'post_status' => 'publish',
                'post_title' => 'Divi per-item fixture ' . ($index + 1),
                'meta_input' => array(
                    '_seed_testimonial_text' => 'Public text ' . ($index + 1),
                    '_seed_testimonial_name' => $fixture['name'],
                    '_seed_testimonial_context' => $fixture['context'],
                    '_seed_testimonial_date' => $fixture['date'],
                    '_seed_testimonial_publication_consent' => '1',
                ),
            )
        );

        $testimonial_id = $testimonial_ids[$index];
        $filename = 'divi-per-item-' . ($index + 1) . '.png';
        $upload = wp_upload_bits(
            $filename,
            null,
            base64_decode(
                'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='
            )
        );
        if (!empty($upload['error'])) {
            throw new RuntimeException($upload['error']);
        }

        $attachment_id = wp_insert_attachment(
            array(
                'post_mime_type' => 'image/png',
                'post_title' => 'Divi per-item media ' . ($index + 1),
                'post_status' => 'inherit',
            ),
            $upload['file'],
            $testimonial_id,
            true
        );
        if (is_wp_error($attachment_id)) {
            throw new RuntimeException($attachment_id->get_error_message());
        }
        $created_posts[] = (int) $attachment_id;
        set_post_thumbnail($testimonial_id, $attachment_id);
        $fixture['photo'] = $filename;
    }
    unset($fixture);

    foreach ($testimonial_ids as $fixture_index => $testimonial_id) {
        seed_divi_item_wp_same(
            $fixtures[$fixture_index]['name'],
            get_post_meta($testimonial_id, '_seed_testimonial_name', true),
            'fixture name stored ' . $testimonial_id
        );
        $fixture_data = wp_seed_content_get_testimonial_data($testimonial_id);
        seed_divi_item_wp_same(
            $fixtures[$fixture_index]['name'],
            isset($fixture_data['name']) ? $fixture_data['name'] : null,
            'fixture name available through Data API ' . $testimonial_id
        );
    }

    $variables = array(
        seed_divi_item_wp_variable('wp_seed_content_testimonial_photo'),
        seed_divi_item_wp_variable('wp_seed_content_testimonial_text'),
        seed_divi_item_wp_variable('wp_seed_content_testimonial_name'),
        seed_divi_item_wp_variable('wp_seed_content_testimonial_context'),
        seed_divi_item_wp_variable('wp_seed_content_testimonial_date'),
    );
    $layout_blocks = array();
    foreach ($variables as $variable) {
        $layout_blocks[] = array(
            'blockName' => 'divi/text',
            'attrs' => array(
                'content' => array(
                    'innerContent' => array(
                        'desktop' => array('value' => $variable),
                    ),
                ),
            ),
            'innerBlocks' => array(),
            'innerHTML' => '',
            'innerContent' => array(),
        );
    }
    $layout_content = serialize_blocks($layout_blocks);
    $layout_id = seed_divi_item_wp_create_post(
        array(
            'post_type' => 'et_pb_layout',
            'post_status' => 'publish',
            'post_title' => 'Divi per-item Layout fixture',
            'post_content' => wp_slash($layout_content),
        )
    );
    $template_id = seed_divi_item_wp_create_post(
        array(
            'post_type' => 'seed_template',
            'post_status' => 'publish',
            'post_title' => 'Divi per-item Template fixture',
            'post_name' => 'divi-per-item-fixture',
            'post_content' => 'Native fallback {{name}}',
        )
    );
    update_post_meta($template_id, '_wp_seed_content_template_module', 'testimonials');
    update_post_meta($template_id, '_wp_seed_content_template_source', 'divi_layout');
    update_post_meta($template_id, '_wp_seed_content_divi_layout_id', $layout_id);

    $second_layout_blocks = array();
    foreach (wp_seed_content_divi_testimonial_dynamic_content_names() as $variable_name) {
        $second_layout_blocks[] = array(
            'blockName' => 'divi/text',
            'attrs' => array(
                'content' => array(
                    'innerContent' => array(
                        'desktop' => array(
                            'value' => seed_divi_item_wp_variable(
                                $variable_name,
                                array('before' => 'L2-')
                            ),
                        ),
                    ),
                ),
            ),
            'innerBlocks' => array(),
            'innerHTML' => '',
            'innerContent' => array(),
        );
    }
    $second_layout_content = serialize_blocks($second_layout_blocks);
    $second_layout_id = seed_divi_item_wp_create_post(
        array(
            'post_type' => 'et_pb_layout',
            'post_status' => 'publish',
            'post_title' => 'Divi per-item second Layout fixture',
            'post_content' => wp_slash($second_layout_content),
        )
    );
    $second_template_id = seed_divi_item_wp_create_post(
        array(
            'post_type' => 'seed_template',
            'post_status' => 'publish',
            'post_title' => 'Divi per-item second Template fixture',
            'post_name' => 'divi-per-item-second-fixture',
            'post_content' => 'Native fallback {{name}}',
        )
    );
    update_post_meta($second_template_id, '_wp_seed_content_template_module', 'testimonials');
    update_post_meta($second_template_id, '_wp_seed_content_template_source', 'divi_layout');
    update_post_meta($second_template_id, '_wp_seed_content_divi_layout_id', $second_layout_id);

    $prepared_values = array();
    foreach ($testimonial_ids as $testimonial_id) {
        $prepared = wp_seed_content_prepare_divi_testimonial_layout_content(
            $layout_content,
            $testimonial_id
        );
        seed_divi_item_wp_assert(is_string($prepared), 'Layout prepared for ' . $testimonial_id);
        seed_divi_item_wp_assert(
            false !== strpos($prepared, '\u0022post_id\u0022')
            || false !== strpos($prepared, 'post_id'),
            'Serialized Layout contains post_id for ' . $testimonial_id
        );
        $prepared_values[] = $prepared;
    }
    seed_divi_item_wp_same(3, count(array_unique($prepared_values)), 'three cache identities');
    seed_divi_item_wp_same($layout_content, get_post_field('post_content', $layout_id), 'stored Layout unchanged');

    seed_divi_item_wp_same(
        $testimonial_ids,
        wp_seed_content_get_testimonials(
            array(
                'ids' => $testimonial_ids,
                'limit' => 0,
                'orderby' => 'id',
                'order' => 'ASC',
            )
        ),
        'three fixtures enter the public Collection'
    );

    $html = wp_seed_content_render_testimonial_collection(
        array(
            'ids' => implode(',', $testimonial_ids),
            'limit' => 0,
            'orderby' => 'id',
            'order' => 'ASC',
            'template' => 'divi-per-item-fixture',
        ),
        false
    );
    foreach ($fixtures as $fixture) {
        seed_divi_item_wp_assert(false !== strpos($html, $fixture['name']), $fixture['name'] . ' rendered');
        seed_divi_item_wp_assert(false !== strpos($html, $fixture['context']), $fixture['context'] . ' rendered');
        seed_divi_item_wp_assert(false !== strpos($html, $fixture['date']), $fixture['date'] . ' rendered');
        seed_divi_item_wp_assert(false !== strpos($html, $fixture['photo']), $fixture['photo'] . ' rendered');
    }
    seed_divi_item_wp_assert(false === strpos($html, '$variable('), 'no raw Dynamic Content');
    seed_divi_item_wp_assert(false !== strpos($html, 'et_pb_text'), 'first Layout follows the Divi renderer');
    seed_divi_item_wp_assert(false === strpos($html, 'Native fallback'), 'Divi Layout renders without fallback');
    seed_divi_item_wp_assert(false === strpos($html, 'seed-card--testimonial'), 'first Layout does not use native cards');
    seed_divi_item_wp_same(0, WP_Seed_Content_Render_Context::depth(), 'stack empty after collection');

    $second_html = wp_seed_content_render_testimonial_collection(
        array(
            'limit' => 0,
            'orderby' => 'id',
            'order' => 'DESC',
            'template' => 'divi-per-item-second-fixture',
        ),
        false
    );
    seed_divi_item_wp_assert(false !== strpos($second_html, 'L2-Charlie'), 'second Layout card 1 renders');
    seed_divi_item_wp_assert(false !== strpos($second_html, 'L2-Bravo'), 'second Layout card 2 renders');
    seed_divi_item_wp_assert(false !== strpos($second_html, 'L2-Alpha'), 'second Layout card 3 renders');
    seed_divi_item_wp_assert(
        strpos($second_html, 'L2-Charlie') < strpos($second_html, 'L2-Bravo')
        && strpos($second_html, 'L2-Bravo') < strpos($second_html, 'L2-Alpha'),
        'changed collection order remains stable'
    );
    seed_divi_item_wp_assert(false === strpos($second_html, 'Native fallback'), 'second Layout has no fallback');
    seed_divi_item_wp_assert(false !== strpos($second_html, 'et_pb_text'), 'second Layout follows the Divi renderer');
    seed_divi_item_wp_assert(false === strpos($second_html, 'seed-card--testimonial'), 'second Layout does not use native cards');

    $frontend_page_id = seed_divi_item_wp_create_post(
        array(
            'post_type' => 'page',
            'post_status' => 'publish',
            'post_title' => 'Divi per-item frontend fixture',
            'post_content' =>
                '[seed_testimonials ids="' . implode(',', $testimonial_ids) . '" limit="0" orderby="id" order="ASC" template="divi-per-item-fixture"]'
                . "\n\n"
                . '[seed_testimonials ids="' . implode(',', $testimonial_ids) . '" limit="0" orderby="id" order="ASC" template="divi-per-item-second-fixture"]',
        )
    );
    $frontend_post = get_post($frontend_page_id);
    $previous_post = isset($GLOBALS['post']) ? $GLOBALS['post'] : null;
    $GLOBALS['post'] = $frontend_post;
    setup_postdata($frontend_post);
    $frontend_html = apply_filters('the_content', $frontend_post->post_content);
    wp_reset_postdata();
    if ($previous_post instanceof WP_Post) {
        $GLOBALS['post'] = $previous_post;
    } else {
        unset($GLOBALS['post']);
    }

    $frontend_names_valid = true;
    foreach ($fixtures as $fixture) {
        if (2 !== substr_count($frontend_html, $fixture['name'])) {
            $frontend_names_valid = false;
            break;
        }
    }
    seed_divi_item_wp_assert(
        $frontend_names_valid,
        'frontend: Alpha, Bravo and Charlie render once in each module'
    );

    $frontend_photos_valid = true;
    foreach ($fixtures as $fixture) {
        if (2 !== substr_count($frontend_html, $fixture['photo'])) {
            $frontend_photos_valid = false;
            break;
        }
    }
    seed_divi_item_wp_assert(
        $frontend_photos_valid,
        'frontend: each testimonial keeps its photo in both modules'
    );

    seed_divi_item_wp_assert(
        !preg_match('/>L[12]-\s*</', $frontend_html)
        && false === strpos($frontend_html, '$variable(')
        && false === strpos($frontend_html, 'Native fallback')
        && false === strpos($frontend_html, 'seed-card--testimonial'),
        'frontend: no bare prefix, raw variable, empty Divi card or unexpected fallback'
    );

    seed_divi_item_wp_assert(
        6 === substr_count($frontend_html, 'seed-testimonial-template-item')
        && 0 === WP_Seed_Content_Render_Context::depth()
        && $layout_content === get_post_field('post_content', $layout_id)
        && $second_layout_content === get_post_field('post_content', $second_layout_id),
        'frontend: six cards render, stack is empty and both Layouts stay unchanged'
    );

    $draft_template_id = seed_divi_item_wp_create_post(
        array(
            'post_type' => 'seed_template',
            'post_status' => 'draft',
            'post_title' => 'Divi per-item draft Template fixture',
            'post_name' => 'divi-per-item-draft-fixture',
            'post_content' => 'Draft must not render {{name}}',
        )
    );
    update_post_meta($draft_template_id, '_wp_seed_content_template_module', 'testimonials');

    $mixed_html = wp_seed_content_render_template_testimonial_item(
        $testimonial_ids[2],
        'divi-per-item-second-fixture'
    );
    $mixed_html .= wp_seed_content_render_template_testimonial_item(
        $testimonial_ids[1],
        'divi-per-item-draft-fixture'
    );
    $mixed_html .= wp_seed_content_render_template_testimonial_item(
        $testimonial_ids[0],
        'divi-per-item-second-fixture'
    );
    seed_divi_item_wp_assert(false !== strpos($mixed_html, 'L2-Charlie'), 'valid card before fallback');
    seed_divi_item_wp_assert(false !== strpos($mixed_html, 'seed-card--testimonial'), 'draft middle card uses native fallback');
    seed_divi_item_wp_assert(false !== strpos($mixed_html, 'L2-Alpha'), 'valid card after fallback');
    seed_divi_item_wp_assert(
        strpos($mixed_html, 'L2-Charlie') < strpos($mixed_html, 'seed-card--testimonial')
        && strpos($mixed_html, 'seed-card--testimonial') < strpos($mixed_html, 'L2-Alpha'),
        'valid cards remain ordered around middle fallback'
    );
    seed_divi_item_wp_same(0, WP_Seed_Content_Render_Context::depth(), 'stack restored after middle fallback');
    seed_divi_item_wp_assert(
        false === strpos($html . $second_html . $mixed_html, '$variable('),
        'two Layouts have no raw Dynamic Content'
    );

    $native = wp_seed_content_render_testimonial_collection(
        array(
            'ids' => implode(',', $testimonial_ids),
            'limit' => 0,
            'orderby' => 'id',
            'order' => 'ASC',
        ),
        false
    );
    seed_divi_item_wp_assert(false !== strpos($native, 'seed-card--testimonial'), 'historical native render unchanged');
} catch (Throwable $error) {
    $failures[] = 'Harness exception: ' . $error->getMessage();
} finally {
    foreach (array_reverse($created_posts) as $post_id) {
        wp_delete_post($post_id, true);
    }
    if (null === $previous_modules) {
        delete_option('wp_seed_content_kit_modules');
    } else {
        update_option('wp_seed_content_kit_modules', $previous_modules);
    }
}

if (!empty($failures)) {
    fwrite(STDERR, 'FAIL ' . count($failures) . ' / ' . $assertions . PHP_EOL);
    foreach ($failures as $failure) {
        fwrite(STDERR, '- ' . $failure . PHP_EOL);
    }
    exit(1);
}

echo 'PASS ' . $assertions . ' WordPress/Divi per-item context assertions' . PHP_EOL;
