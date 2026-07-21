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
        'all_items' => __('Témoignages', 'wp-seed-content-kit'),
        'archives' => __('Archives des témoignages', 'wp-seed-content-kit'),
        'attributes' => __('Attributs du témoignage', 'wp-seed-content-kit'),
        'insert_into_item' => __('Insérer dans le témoignage', 'wp-seed-content-kit'),
        'uploaded_to_this_item' => __('Téléversé pour ce témoignage', 'wp-seed-content-kit'),
        'featured_image' => __('Photo du témoignage', 'wp-seed-content-kit'),
        'set_featured_image' => __('Définir la photo du témoignage', 'wp-seed-content-kit'),
        'remove_featured_image' => __('Retirer la photo du témoignage', 'wp-seed-content-kit'),
        'use_featured_image' => __('Utiliser comme photo du témoignage', 'wp-seed-content-kit'),
        'filter_items_list' => __('Filtrer la liste des témoignages', 'wp-seed-content-kit'),
        'items_list_navigation' => __('Navigation de la liste des témoignages', 'wp-seed-content-kit'),
        'items_list' => __('Liste des témoignages', 'wp-seed-content-kit'),
    );

    register_post_type('seed_testimonial', array(
        'labels' => $labels,
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => wp_seed_content_kit_get_post_type_menu_parent('seed_testimonial'),
        'show_in_rest' => true,
        'menu_position' => 20,
        'menu_icon' => 'dashicons-format-quote',
        'supports' => array('title', 'thumbnail', 'revisions', 'page-attributes'),
        'has_archive' => true,
        'rewrite' => array(
            'slug' => 'testimonials',
            'with_front' => false,
        ),
        'capability_type' => array('seed_testimonial', 'seed_testimonials'),
        'capabilities' => wp_seed_content_kit_get_capability_map('testimonials'),
        'map_meta_cap' => true,
    ));

    if (function_exists('wp_seed_content_kit_register_manual_order_for_post_type')) {
        wp_seed_content_kit_register_manual_order_for_post_type('seed_testimonial');
    }
}
