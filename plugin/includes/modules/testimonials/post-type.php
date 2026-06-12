<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_register_testimonial_post_type()
{
    $labels = array(
        'name' => __('Témoignages', 'wp-seed-content-kit'),
        'singular_name' => __('Témoignage', 'wp-seed-content-kit'),
        'menu_name' => __('Témoignages', 'wp-seed-content-kit'),
        'add_new' => __('Ajouter', 'wp-seed-content-kit'),
        'add_new_item' => __('Ajouter un témoignage', 'wp-seed-content-kit'),
        'edit_item' => __('Modifier le témoignage', 'wp-seed-content-kit'),
        'new_item' => __('Nouveau témoignage', 'wp-seed-content-kit'),
        'view_item' => __('Voir le témoignage', 'wp-seed-content-kit'),
        'search_items' => __('Rechercher des témoignages', 'wp-seed-content-kit'),
        'not_found' => __('Aucun témoignage trouvé', 'wp-seed-content-kit'),
        'not_found_in_trash' => __('Aucun témoignage trouvé dans la corbeille', 'wp-seed-content-kit'),
    );

    register_post_type('seed_testimonial', array(
        'labels' => $labels,
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => wp_seed_content_kit_get_post_type_menu_parent('seed_testimonial'),
        'show_in_rest' => true,
        'menu_position' => 20,
        'menu_icon' => 'dashicons-format-quote',
        'supports' => array('title', 'revisions'),
        'has_archive' => true,
        'rewrite' => array(
            'slug' => 'testimonials',
            'with_front' => false,
        ),
        'capability_type' => 'post',
    ));
}
