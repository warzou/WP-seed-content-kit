<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_directory_register_post_type()
{
    $labels = array(
        'name' => __('Annuaire', 'wp-seed-content-kit'),
        'singular_name' => __('Personne', 'wp-seed-content-kit'),
        'menu_name' => __('Annuaire', 'wp-seed-content-kit'),
        'add_new' => __('Ajouter une personne', 'wp-seed-content-kit'),
        'add_new_item' => __('Ajouter une personne', 'wp-seed-content-kit'),
        'edit_item' => __('Modifier la personne', 'wp-seed-content-kit'),
        'new_item' => __('Nouvelle personne', 'wp-seed-content-kit'),
        'search_items' => __('Rechercher dans l’annuaire', 'wp-seed-content-kit'),
        'not_found' => __('Aucune personne trouvée', 'wp-seed-content-kit'),
        'not_found_in_trash' => __('Aucune personne trouvée dans la corbeille', 'wp-seed-content-kit'),
        'all_items' => __('Toutes les personnes', 'wp-seed-content-kit'),
        'attributes' => __('Informations de la personne', 'wp-seed-content-kit'),
        'featured_image' => __('Photo', 'wp-seed-content-kit'),
        'set_featured_image' => __('Définir la photo', 'wp-seed-content-kit'),
        'remove_featured_image' => __('Retirer la photo', 'wp-seed-content-kit'),
        'use_featured_image' => __('Utiliser comme photo', 'wp-seed-content-kit'),
        'filter_items_list' => __('Filtrer la liste des personnes', 'wp-seed-content-kit'),
        'items_list_navigation' => __('Navigation de la liste des personnes', 'wp-seed-content-kit'),
        'items_list' => __('Liste des personnes', 'wp-seed-content-kit'),
    );

    register_post_type('seed_directory', array(
        'labels' => $labels,
        'description' => __('Gestion et affichage des personnes de l’annuaire.', 'wp-seed-content-kit'),
        'public' => false,
        'publicly_queryable' => false,
        'exclude_from_search' => true,
        'show_ui' => true,
        'show_in_menu' => wp_seed_content_kit_get_post_type_menu_parent('seed_directory'),
        'show_in_rest' => false,
        'has_archive' => false,
        'rewrite' => false,
        'query_var' => false,
        'can_export' => true,
        'menu_position' => 20,
        'menu_icon' => 'dashicons-admin-users',
        'supports' => array('title', 'excerpt', 'thumbnail', 'page-attributes', 'revisions'),
        'capability_type' => array('seed_directory_entry', 'seed_directory_entries'),
        'capabilities' => wp_seed_content_directory_get_capability_map(),
        'map_meta_cap' => true,
    ));

    wp_seed_content_directory_register_meta_fields();

    if (function_exists('wp_seed_content_kit_register_manual_order_for_post_type')) {
        wp_seed_content_kit_register_manual_order_for_post_type('seed_directory');
    }
}
