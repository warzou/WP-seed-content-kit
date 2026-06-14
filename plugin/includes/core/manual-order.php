<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_kit_register_manual_order_for_post_type($post_type)
{
    if (!post_type_exists($post_type)) {
        return;
    }

    if (!post_type_supports($post_type, 'page-attributes')) {
        return;
    }

    static $registered_post_types = array();
    if (isset($registered_post_types[$post_type])) {
        return;
    }
    $registered_post_types[$post_type] = true;

    add_filter(
        'manage_' . $post_type . '_posts_columns',
        function ($columns) use ($post_type) {
            return wp_seed_content_kit_manual_order_add_columns($columns, $post_type);
        }
    );

    add_action(
        'manage_' . $post_type . '_posts_custom_column',
        function ($column, $post_id) use ($post_type) {
            wp_seed_content_kit_manual_order_render_columns($column, $post_id, $post_type);
        },
        10,
        2
    );

    add_action(
        'save_post',
        function ($post_id, $post, $update) use ($post_type) {
            wp_seed_content_kit_manual_order_ensure_auto_position($post_id, $post, $update, $post_type);
        },
        9,
        3
    );

    add_action(
        'load-edit.php',
        function () use ($post_type) {
            wp_seed_content_kit_manual_order_maybe_repair($post_type);
        }
    );

    add_action(
        'admin_init',
        function () use ($post_type) {
            wp_seed_content_kit_manual_order_handle_move($post_type);
        }
    );

    add_action(
        'pre_get_posts',
        function ($query) use ($post_type) {
            wp_seed_content_kit_manual_order_apply_default_admin_order($query, $post_type);
        }
    );
}

function wp_seed_content_kit_manual_order_add_columns($columns, $post_type)
{
    $position_key = $post_type . '_position';
    $actions_key = $post_type . '_order_actions';

    $ordered = array();
    foreach ($columns as $key => $label) {
        if ('title' === $key) {
            $ordered[$position_key] = __('Position', 'wp-seed-content-kit');
            $ordered[$actions_key] = __('Actions', 'wp-seed-content-kit');
        }
        $ordered[$key] = $label;
    }

    return $ordered;
}

function wp_seed_content_kit_manual_order_render_columns($column, $post_id, $post_type)
{
    if ($column === $post_type . '_position') {
        wp_seed_content_kit_manual_order_render_position_column($post_id, $post_type);
        return;
    }

    if ($column === $post_type . '_order_actions') {
        wp_seed_content_kit_manual_order_render_actions_column($post_id, $post_type);
    }
}

function wp_seed_content_kit_manual_order_render_position_column($post_id, $post_type)
{
    $ordered_post_ids = wp_seed_content_kit_manual_order_get_ordered_post_ids($post_type);
    $position = array_search((int) $post_id, $ordered_post_ids, true);

    if (false === $position) {
        echo '&mdash;';
        return;
    }

    echo (int) ($position + 1);
}

function wp_seed_content_kit_manual_order_render_actions_column($post_id, $post_type)
{
    $ordered_post_ids = wp_seed_content_kit_manual_order_get_ordered_post_ids($post_type);
    $count = count($ordered_post_ids);
    $position = array_search((int) $post_id, $ordered_post_ids, true);

    if (false === $position) {
        return;
    }

    $base_url = admin_url('admin-post.php');

    if ($position > 0) {
        $up_url = wp_nonce_url(
            add_query_arg(
                array(
                    'action' => 'wp_seed_content_kit_move_order',
                    'post_type' => $post_type,
                    'post_id' => (int) $post_id,
                    'direction' => 'up',
                ),
                $base_url
            ),
            'wp_seed_content_kit_move_order_' . $post_type . '_' . (int) $post_id . '_up'
        );
        echo '<a href="' . esc_url($up_url) . '">' . esc_html__('&#9650;', 'wp-seed-content-kit') . '</a> ';
    } else {
        echo '<span aria-hidden="true">&#9650;</span> ';
    }

    if ($position < $count - 1) {
        $down_url = wp_nonce_url(
            add_query_arg(
                array(
                    'action' => 'wp_seed_content_kit_move_order',
                    'post_type' => $post_type,
                    'post_id' => (int) $post_id,
                    'direction' => 'down',
                ),
                $base_url
            ),
            'wp_seed_content_kit_move_order_' . $post_type . '_' . (int) $post_id . '_down'
        );
        echo '<a href="' . esc_url($down_url) . '">' . esc_html__('&#9660;', 'wp-seed-content-kit') . '</a>';
    } else {
        echo '<span aria-hidden="true">&#9660;</span>';
    }
}

function wp_seed_content_kit_manual_order_get_ordered_post_ids($post_type)
{
    static $ordered_post_ids_by_type = array();

    if (isset($ordered_post_ids_by_type[$post_type])) {
        return $ordered_post_ids_by_type[$post_type];
    }

    $query = new WP_Query(array(
        'post_type' => $post_type,
        'post_status' => 'any',
        'posts_per_page' => -1,
        'orderby' => array(
            'menu_order' => 'ASC',
            'ID' => 'ASC',
        ),
        'order' => 'ASC',
        'fields' => 'ids',
        'no_found_rows' => true,
    ));

    $ordered_post_ids_by_type[$post_type] = array_map('intval', (array) $query->posts);
    return $ordered_post_ids_by_type[$post_type];
}

function wp_seed_content_kit_manual_order_get_next_auto_position($post_type)
{
    $query = new WP_Query(array(
        'post_type' => $post_type,
        'post_status' => 'any',
        'posts_per_page' => 1,
        'orderby' => array(
            'menu_order' => 'DESC',
            'ID' => 'DESC',
        ),
        'order' => 'DESC',
        'fields' => 'ids',
        'no_found_rows' => true,
    ));

    if (empty($query->posts)) {
        return 1;
    }

    $last_post = get_post((int) $query->posts[0]);
    if (!$last_post instanceof WP_Post) {
        return 1;
    }

    return (int) $last_post->menu_order + 1;
}

function wp_seed_content_kit_manual_order_ensure_auto_position($post_id, $post, $update, $post_type)
{
    if (!$post instanceof WP_Post || $post->post_type !== $post_type || $update) {
        return;
    }

    if ('auto-draft' === $post->post_status) {
        return;
    }

    if ((int) $post->menu_order > 0) {
        return;
    }

    if (!current_user_can('edit_post', (int) $post_id)) {
        return;
    }

    wp_update_post(array(
        'ID' => (int) $post_id,
        'menu_order' => wp_seed_content_kit_manual_order_get_next_auto_position($post_type),
    ));
}

function wp_seed_content_kit_manual_order_repair_legacy($post_type)
{
    $all_posts = get_posts(array(
        'post_type' => $post_type,
        'post_status' => 'any',
        'posts_per_page' => -1,
        'orderby' => array(
            'menu_order' => 'ASC',
            'ID' => 'ASC',
        ),
        'order' => 'ASC',
        'fields' => 'ids',
        'no_found_rows' => true,
    ));

    $zero_order_posts = array();
    foreach ($all_posts as $candidate_post_id) {
        $candidate_id = (int) $candidate_post_id;
        $candidate = get_post($candidate_id);
        if ($candidate instanceof WP_Post && 0 === (int) $candidate->menu_order) {
            $zero_order_posts[] = $candidate_id;
        }
    }

    if (count($zero_order_posts) <= 1) {
        return;
    }

    $next_order = wp_seed_content_kit_manual_order_get_next_auto_position($post_type);

    foreach ($zero_order_posts as $candidate_id) {
        wp_update_post(array(
            'ID' => (int) $candidate_id,
            'menu_order' => $next_order++,
        ));
    }
}

function wp_seed_content_kit_manual_order_maybe_repair($post_type)
{
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!$screen || 'edit' !== $screen->base || $post_type !== $screen->post_type) {
        return;
    }

    wp_seed_content_kit_manual_order_repair_legacy($post_type);
}

function wp_seed_content_kit_manual_order_handle_move($post_type)
{
    if (!is_admin() || !isset($_GET['action']) || 'wp_seed_content_kit_move_order' !== sanitize_key(wp_unslash($_GET['action']))) {
        return;
    }

    if (!isset($_GET['post_type']) || $post_type !== sanitize_key(wp_unslash($_GET['post_type']))) {
        return;
    }

    if (!current_user_can('edit_posts')) {
        return;
    }

    $post_id = isset($_GET['post_id']) ? absint(wp_unslash($_GET['post_id'])) : 0;
    $direction = isset($_GET['direction']) ? sanitize_key(wp_unslash($_GET['direction'])) : '';

    if (!$post_id || !in_array($direction, array('up', 'down'), true) || !current_user_can('edit_post', $post_id)) {
        return;
    }

    check_admin_referer('wp_seed_content_kit_move_order_' . $post_type . '_' . $post_id . '_' . $direction);

    $ordered_post_ids = wp_seed_content_kit_manual_order_get_ordered_post_ids($post_type);
    $position = array_search($post_id, $ordered_post_ids, true);
    if (false === $position) {
        return;
    }

    $swap_position = ('up' === $direction) ? $position - 1 : $position + 1;
    if (!isset($ordered_post_ids[$swap_position])) {
        return;
    }

    $other_id = (int) $ordered_post_ids[$swap_position];
    $current_post = get_post($post_id);
    $other_post = get_post($other_id);
    if (!$current_post instanceof WP_Post || !$other_post instanceof WP_Post) {
        return;
    }

    $current_order = (int) $current_post->menu_order;
    $other_order = (int) $other_post->menu_order;

    wp_update_post(array(
        'ID' => $post_id,
        'menu_order' => $other_order,
    ));
    wp_update_post(array(
        'ID' => $other_id,
        'menu_order' => $current_order,
    ));

    wp_safe_redirect(
        add_query_arg(
            array(
                'post_type' => $post_type,
                'orderby' => 'menu_order',
                'order' => 'ASC',
            ),
            admin_url('edit.php')
        )
    );
    exit;
}

function wp_seed_content_kit_manual_order_apply_default_admin_order($query, $post_type)
{
    if (!$query instanceof WP_Query || !$query->is_admin || !$query->is_main_query()) {
        return;
    }

    if ((string) $query->get('post_type') !== (string) $post_type) {
        return;
    }

    if ($query->get('orderby')) {
        return;
    }

    $query->set('orderby', array(
        'menu_order' => 'ASC',
        'ID' => 'ASC',
    ));
    $query->set('order', 'ASC');
}
