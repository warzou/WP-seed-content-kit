<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_register_quote_post_type()
{
    $labels = array(
        'name' => __('Citations', 'wp-seed-content-kit'),
        'singular_name' => __('Citation', 'wp-seed-content-kit'),
        'menu_name' => __('Citations', 'wp-seed-content-kit'),
        'add_new' => __('Ajouter', 'wp-seed-content-kit'),
        'add_new_item' => __('Ajouter une citation', 'wp-seed-content-kit'),
        'edit_item' => __('Modifier la citation', 'wp-seed-content-kit'),
        'new_item' => __('Nouvelle citation', 'wp-seed-content-kit'),
        'view_item' => __('Voir la citation', 'wp-seed-content-kit'),
        'search_items' => __('Rechercher des citations', 'wp-seed-content-kit'),
        'not_found' => __('Aucune citation trouvée', 'wp-seed-content-kit'),
        'not_found_in_trash' => __('Aucune citation trouvée dans la corbeille', 'wp-seed-content-kit'),
        'all_items' => __('Citations', 'wp-seed-content-kit'),
        'archives' => __('Archives des citations', 'wp-seed-content-kit'),
        'attributes' => __('Attributs de la citation', 'wp-seed-content-kit'),
        'filter_items_list' => __('Filtrer la liste des citations', 'wp-seed-content-kit'),
        'items_list_navigation' => __('Navigation de la liste des citations', 'wp-seed-content-kit'),
        'items_list' => __('Liste des citations', 'wp-seed-content-kit'),
    );

    register_post_type('seed_quote', array(
        'labels' => $labels,
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => wp_seed_content_kit_get_post_type_menu_parent('seed_quote'),
        'show_in_rest' => true,
        'menu_position' => 20,
        'menu_icon' => 'dashicons-editor-quote',
        'supports' => array('title', 'revisions', 'page-attributes'),
        'has_archive' => true,
        'rewrite' => array(
            'slug' => 'quotes',
            'with_front' => false,
        ),
        'capability_type' => 'post',
    ));
}

function wp_seed_content_quotes_manage_columns($columns)
{
    $columns['seed_quote_order'] = __('Ordre', 'wp-seed-content-kit');
    return $columns;
}
add_filter('manage_seed_quote_posts_columns', 'wp_seed_content_quotes_manage_columns');

function wp_seed_content_quotes_render_order_column($column, $post_id)
{
    if ('seed_quote_order' !== $column) {
        return;
    }

    $post = get_post($post_id);
    if (!$post instanceof WP_Post) {
        echo '0';
        return;
    }

    echo (int) $post->menu_order;
}
add_action('manage_seed_quote_posts_custom_column', 'wp_seed_content_quotes_render_order_column', 10, 2);
