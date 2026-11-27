<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_directory_next_intervenant_order($post_id)
{
    $maximum = 0;
    $posts = get_posts(array(
        'post_type' => 'seed_directory', 'post_status' => array('draft', 'pending', 'publish', 'future', 'private'),
        'posts_per_page' => -1, 'orderby' => 'ID', 'order' => 'ASC', 'suppress_filters' => true,
    ));
    foreach ($posts as $post) {
        if ((int) $post->ID !== (int) $post_id && !in_array($post->post_status, array('trash', 'auto-draft'), true) && in_array('intervenant', wp_seed_content_directory_get_meta_value($post->ID, '_seed_directory_profile_types'), true)) {
            $maximum = max($maximum, (int) $post->menu_order);
        }
    }
    return $maximum + 10;
}

function wp_seed_content_directory_finish_initial_order($post_id)
{
    $key = '_wpsck_directory_initial_order_pending';
    if ('1' !== get_post_meta($post_id, $key, true)) {
        return;
    }
    $post = get_post($post_id);
    if (!$post || 'seed_directory' !== $post->post_type || 'auto-draft' === $post->post_status) {
        return;
    }
    // REST can save the post before the editor's profile metabox has been saved.
    $types = wp_seed_content_directory_get_meta_value($post_id, '_seed_directory_profile_types');
    if (!$types && !isset($_POST['wp_seed_content_directory_profile_present'])) {
        return;
    }
    $manual = isset($_POST['seed_directory_menu_order']) && '' !== trim((string) $_POST['seed_directory_menu_order']);
    delete_post_meta($post_id, $key);
    if (!$manual && 0 === (int) $post->menu_order && in_array('intervenant', $types, true)) {
        wp_update_post(array('ID' => (int) $post_id, 'menu_order' => wp_seed_content_directory_next_intervenant_order($post_id)));
    }
}

function wp_seed_content_directory_initial_order_created($post_id, $post, $update, $post_before)
{
    if (!$post || 'seed_directory' !== $post->post_type || wp_is_post_revision($post_id) || (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)) {
        return;
    }
    if (!$update || ($post_before && 'auto-draft' === $post_before->post_status)) {
        update_post_meta($post_id, '_wpsck_directory_initial_order_pending', '1');
    }
    wp_seed_content_directory_finish_initial_order($post_id);
}
add_action('wp_after_insert_post', 'wp_seed_content_directory_initial_order_created', 30, 4);
