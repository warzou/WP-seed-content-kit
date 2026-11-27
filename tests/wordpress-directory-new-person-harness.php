<?php

$wp_load = getenv('WP_SEED_WORDPRESS_LOAD');
if (!defined('ABSPATH')) {
    if (!is_string($wp_load) || '' === $wp_load || !is_file($wp_load)) {
        fwrite(STDERR, "Set WP_SEED_WORDPRESS_LOAD to WordPress wp-load.php.\n");
        exit(2);
    }
    require $wp_load;
}

$assertions = 0;
$failures = array();
$fixture_id = 0;
$original_user_id = get_current_user_id();

function directory_new_person_assert($condition, $label)
{
    global $assertions, $failures;
    $assertions++;
    if (!$condition) {
        $failures[] = $label;
    }
}

function directory_new_person_same($expected, $actual, $label)
{
    directory_new_person_assert($expected === $actual, $label);
}

function directory_new_person_fingerprint()
{
    $ids = get_posts(array(
        'post_type' => 'seed_directory',
        'post_status' => 'any',
        'posts_per_page' => -1,
        'fields' => 'ids',
        'orderby' => 'ID',
        'order' => 'ASC',
    ));
    $rows = array();
    foreach ($ids as $id) {
        $post = get_post($id);
        $meta = get_post_meta($id);
        ksort($meta, SORT_STRING);
        foreach ($meta as $key => $values) {
            sort($values, SORT_STRING);
            $meta[$key] = $values;
        }
        $rows[] = array(
            'ID' => (int) $id,
            'title' => (string) $post->post_title,
            'status' => (string) $post->post_status,
            'excerpt' => (string) $post->post_excerpt,
            'content' => (string) $post->post_content,
            'order' => (int) $post->menu_order,
            'meta' => $meta,
        );
    }
    return array(count($rows), hash('sha256', wp_json_encode($rows)));
}

function directory_new_person_preexisting_autodrafts()
{
    $ids = get_posts(array(
        'post_type' => 'seed_directory',
        'post_status' => 'auto-draft',
        'posts_per_page' => -1,
        'fields' => 'ids',
        'orderby' => 'ID',
        'order' => 'ASC',
    ));
    $rows = array();
    foreach ($ids as $id) {
        $meta = get_post_meta($id);
        $rows[] = array(
            'ID' => (int) $id,
            'title' => (string) get_post_field('post_title', $id),
            'thumbnail_id' => (int) get_post_thumbnail_id($id),
            'meta_keys' => array_values(array_keys($meta)),
        );
    }
    return $rows;
}

try {
    $administrators = get_users(array('role' => 'administrator', 'number' => 1, 'fields' => 'ID'));
    if (empty($administrators)) {
        throw new RuntimeException('No administrator available for the isolated workflow.');
    }
    wp_set_current_user((int) $administrators[0]);
    require_once ABSPATH . 'wp-admin/includes/post.php';

    $before = directory_new_person_fingerprint();
    $preexisting_autodrafts = directory_new_person_preexisting_autodrafts();

    $fixture = get_default_post_to_edit('seed_directory', true);
    if (!$fixture || empty($fixture->ID)) {
        throw new RuntimeException('WordPress did not create the post-new.php auto-draft.');
    }
    $fixture_id = (int) $fixture->ID;

    directory_new_person_same('auto-draft', get_post_status($fixture_id), 'new entry starts as auto-draft');
    $default_presentation = "<!-- wp:more -->\n<!--more-->\n<!-- /wp:more -->";
    directory_new_person_same($default_presentation, (string) $fixture->post_content, 'new entry editor receives one native More block');
    directory_new_person_same(1, preg_match_all('/<!--more(?:\s+.*?)?-->/s', (string) $fixture->post_content), 'new entry default contains one More marker');
    directory_new_person_same(false, wp_seed_content_directory_has_more_content(wp_seed_content_split_wordpress_more($fixture->post_content)), 'empty default More does not expose read-more UI');
    directory_new_person_assert(in_array((string) get_post_field('post_title', $fixture_id), array('', __('Auto Draft')), true), 'new entry has only the WordPress auto-draft title');
    directory_new_person_same(0, (int) get_post_thumbnail_id($fixture_id), 'new entry has no inherited featured image');
    $initial_meta = get_post_meta($fixture_id);
    $business_meta = array_filter(array_keys($initial_meta), function ($key) {
        return 0 === strpos($key, '_seed_directory') || 0 === strpos($key, 'seed_directory');
    });
    directory_new_person_same(array(), array_values($business_meta), 'new entry has no inherited Directory meta');
    directory_new_person_same('', wp_seed_content_directory_get_meta_value($fixture_id, '_seed_directory_status'), 'new entry status is empty');
    directory_new_person_same(array(), wp_seed_content_directory_get_meta_value($fixture_id, '_seed_directory_profile_types'), 'new entry profile types are empty');
    directory_new_person_same('', wp_seed_content_directory_get_meta_value($fixture_id, '_seed_directory_country'), 'new entry country starts empty');
    directory_new_person_same('', wp_seed_content_directory_get_meta_value($fixture_id, '_seed_directory_publication_authorized'), 'new entry publication authorization is empty');

    $_POST = array(
        'wp_seed_content_directory_nonce' => wp_create_nonce('wp_seed_content_directory_save'),
        'wp_seed_content_directory_name' => 'Personne Test WPSCK',
        'wp_seed_content_directory_profile_present' => '1',
        'wp_seed_content_directory_publication_present' => '1',
        '_seed_directory_status' => 'en_exercice',
        '_seed_directory_country' => 'FR',
        '_seed_directory_publication_authorized' => '1',
        '_seed_directory_publicly_listed' => '1',
        'seed_directory_menu_order' => '0',
        'wp_seed_content_directory_excerpt' => '',
    );
    $saved = wp_update_post(array(
        'ID' => $fixture_id,
        'post_type' => 'seed_directory',
        'post_status' => 'draft',
        'post_title' => 'Personne Test WPSCK',
        'post_content' => $fixture->post_content,
    ), true);
    if (is_wp_error($saved)) {
        throw new RuntimeException($saved->get_error_message());
    }

    clean_post_cache($fixture_id);
    directory_new_person_same('draft', get_post_status($fixture_id), 'new entry saves as draft');
    directory_new_person_same('Personne Test WPSCK', (string) get_post_field('post_title', $fixture_id), 'name input saves to post_title');
    directory_new_person_same('', (string) get_post_field('post_excerpt', $fixture_id), 'summary remains optional');
    directory_new_person_same(0, (int) get_post_thumbnail_id($fixture_id), 'draft has no inherited featured image');
    directory_new_person_same($default_presentation, (string) get_post_field('post_content', $fixture_id), 'default More persists on first save');
    directory_new_person_same('en_exercice', wp_seed_content_directory_get_meta_value($fixture_id, '_seed_directory_status'), 'valid status is stored on the fixture');

    directory_new_person_same(array(), wp_seed_content_directory_get_publication_errors($fixture_id, array(
        '_seed_directory_profile_types' => array('intervenant'),
        '_seed_directory_status' => '',
    )), 'intervenant-only fixture does not require a status');
    directory_new_person_assert(in_array('invalid_status', wp_seed_content_directory_get_publication_errors($fixture_id, array(
        '_seed_directory_profile_types' => array('praticien'),
        '_seed_directory_status' => '',
    )), true), 'practitioner-only fixture without status is blocked');
    directory_new_person_assert(in_array('invalid_status', wp_seed_content_directory_get_publication_errors($fixture_id, array(
        '_seed_directory_profile_types' => array('praticien', 'intervenant'),
        '_seed_directory_status' => '',
    )), true), 'practitioner and intervenant fixture without status is blocked');
    directory_new_person_same(array(), wp_seed_content_directory_get_publication_errors($fixture_id, array(
        '_seed_directory_profile_types' => array('praticien'),
        '_seed_directory_status' => 'autre',
    )), 'registry status autre satisfies practitioner publication');

    $_POST = array(
        'wp_seed_content_directory_nonce' => wp_create_nonce('wp_seed_content_directory_save'),
        'wp_seed_content_directory_name' => 'Personne Test WPSCK',
        'wp_seed_content_directory_profile_present' => '1',
        'wp_seed_content_directory_publication_present' => '1',
        '_seed_directory_profile_types' => array('intervenant'),
        '_seed_directory_publication_authorized' => '1',
        '_seed_directory_publicly_listed' => '1',
    );
    $unchecked_practitioner = wp_update_post(array(
        'ID' => $fixture_id,
        'post_type' => 'seed_directory',
        'post_status' => 'draft',
        'post_title' => 'Personne Test WPSCK',
    ), true);
    if (is_wp_error($unchecked_practitioner)) {
        throw new RuntimeException($unchecked_practitioner->get_error_message());
    }
    clean_post_cache($fixture_id);
    directory_new_person_same('en_exercice', wp_seed_content_directory_get_meta_value($fixture_id, '_seed_directory_status'), 'unchecking practitioner without a status payload preserves the status');
    directory_new_person_same(array('intervenant'), wp_seed_content_directory_get_meta_value($fixture_id, '_seed_directory_profile_types'), 'intervenant-only profile is stored');

    $_POST = array();
    $published_empty_more = wp_update_post(array(
        'ID' => $fixture_id,
        'post_type' => 'seed_directory',
        'post_status' => 'publish',
        'post_title' => 'Personne Test WPSCK',
        'post_content' => $default_presentation,
    ), true);
    if (is_wp_error($published_empty_more)) {
        throw new RuntimeException($published_empty_more->get_error_message());
    }
    clean_post_cache($fixture_id);
    $empty_more_data = wp_seed_content_directory_get_public_data($fixture_id);
    directory_new_person_assert(is_array($empty_more_data), 'fixture with empty default More remains publicly projectable');
    directory_new_person_same(false, $empty_more_data['has_more'], 'empty default More projects has_more false');
    directory_new_person_same('', $empty_more_data['presentation_more'], 'empty default More projects no continuation');

    $content_after_more = $default_presentation . "\n<!-- wp:paragraph --><p>Suite réelle.</p><!-- /wp:paragraph -->";
    wp_update_post(array('ID' => $fixture_id, 'post_content' => $content_after_more));
    clean_post_cache($fixture_id);
    $real_more_data = wp_seed_content_directory_get_public_data($fixture_id);
    directory_new_person_same(true, $real_more_data['has_more'], 'content after More projects has_more true');
    directory_new_person_assert(false !== strpos($real_more_data['presentation_more'], 'Suite réelle.'), 'content after More is available in the continuation');

    $removed_more = wp_update_post(array(
        'ID' => $fixture_id,
        'post_type' => 'seed_directory',
        'post_status' => 'draft',
        'post_content' => '',
    ), true);
    if (is_wp_error($removed_more)) {
        throw new RuntimeException($removed_more->get_error_message());
    }
    clean_post_cache($fixture_id);
    directory_new_person_same('', (string) get_post_field('post_content', $fixture_id), 'user removal of the default More is preserved');

    $search = new WP_Query(array(
        'post_type' => 'seed_directory',
        'post_status' => 'any',
        's' => 'Personne Test WPSCK',
        'fields' => 'ids',
        'posts_per_page' => 20,
    ));
    directory_new_person_assert(in_array($fixture_id, array_map('intval', $search->posts), true), 'admin search finds the canonical name');

    $published = wp_update_post(array(
        'ID' => $fixture_id,
        'post_type' => 'seed_directory',
        'post_status' => 'publish',
        'post_title' => 'Personne Test WPSCK',
    ), true);
    if (is_wp_error($published)) {
        throw new RuntimeException($published->get_error_message());
    }
    clean_post_cache($fixture_id);
    directory_new_person_same('publish', get_post_status($fixture_id), 'valid new entry publishes');
    directory_new_person_same('Personne Test WPSCK', wp_seed_content_resolve_dynamic_data('directory.name', array(
        'current_post_id' => $fixture_id,
        'current_post_type' => 'seed_directory',
    )), 'Content Data resolves the canonical name');

    $block = new WP_Block(array(
        'blockName' => 'core/paragraph',
        'attrs' => array(),
        'innerBlocks' => array(),
        'innerHTML' => '',
        'innerContent' => array(),
    ), array('postId' => $fixture_id, 'postType' => 'seed_directory'));
    directory_new_person_same('Personne Test WPSCK', wp_seed_content_get_gutenberg_binding_value(
        array('field_id' => 'directory.name', 'post_id' => $fixture_id),
        $block,
        'content'
    ), 'Block Binding resolves the canonical name');
    directory_new_person_same(false, metadata_exists('post', $fixture_id, 'wp_seed_content_directory_name'), 'no duplicate name meta exists');

    wp_delete_post($fixture_id, true);
    $fixture_id = 0;
    $_POST = array();
    clean_post_cache(0);
    $after = directory_new_person_fingerprint();
    directory_new_person_same($before, $after, 'existing Directory fingerprint is unchanged after fixture cleanup');

    echo wp_json_encode(array(
        'assertions' => $assertions,
        'preexisting_autodrafts' => $preexisting_autodrafts,
        'existing_count' => $before[0],
        'existing_sha256' => $before[1],
    ), JSON_UNESCAPED_SLASHES) . "\n";
} catch (Exception $exception) {
    $failures[] = $exception->getMessage();
} finally {
    $_POST = array();
    if ($fixture_id > 0) {
        wp_delete_post($fixture_id, true);
    }
    wp_set_current_user($original_user_id);
}

if ($failures) {
    fwrite(STDERR, 'FAIL ' . count($failures) . '/' . $assertions . ': ' . implode('; ', $failures) . "\n");
    exit(1);
}

echo 'PASS ' . $assertions . " WordPress new Directory person assertions\n";
