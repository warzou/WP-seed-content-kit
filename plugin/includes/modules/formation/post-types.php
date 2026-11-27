<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_formation_register_program_post_type()
{
    register_post_type('seed_program', array(
        'labels' => array(
            'name' => __('Programmes', 'wp-seed-content-kit'),
            'singular_name' => __('Programme', 'wp-seed-content-kit'),
            'menu_name' => __('Programmes', 'wp-seed-content-kit'),
            'add_new' => __('Ajouter un programme', 'wp-seed-content-kit'),
            'add_new_item' => __('Ajouter un programme', 'wp-seed-content-kit'),
            'edit_item' => __('Modifier le programme', 'wp-seed-content-kit'),
            'new_item' => __('Nouveau programme', 'wp-seed-content-kit'),
            'search_items' => __('Rechercher des programmes', 'wp-seed-content-kit'),
            'not_found' => __('Aucun programme trouvé', 'wp-seed-content-kit'),
            'all_items' => __('Tous les programmes', 'wp-seed-content-kit'),
        ),
        'description' => __('Programmes de parcours.', 'wp-seed-content-kit'),
        'public' => false,
        'publicly_queryable' => false,
        'exclude_from_search' => true,
        'show_ui' => true,
        'show_in_menu' => false,
        'show_in_rest' => true,
        'show_in_admin_bar' => false,
        'has_archive' => false,
        'rewrite' => false,
        'query_var' => false,
        'can_export' => true,
        'supports' => array('title', 'editor', 'excerpt', 'page-attributes', 'revisions'),
        'capability_type' => array('seed_program', 'seed_programs'),
        'capabilities' => wp_seed_content_kit_get_capability_map('formation_programs'),
        'map_meta_cap' => true,
    ));

    if (function_exists('wp_seed_content_kit_register_manual_order_for_post_type')) {
        wp_seed_content_kit_register_manual_order_for_post_type('seed_program');
    }
}

function wp_seed_content_formation_register_theme_post_type()
{
    register_post_type('seed_theme', array(
        'labels' => array(
            'name' => __('Thèmes', 'wp-seed-content-kit'),
            'singular_name' => __('Thème', 'wp-seed-content-kit'),
            'menu_name' => __('Thèmes', 'wp-seed-content-kit'),
            'add_new' => __('Ajouter un thème', 'wp-seed-content-kit'),
            'add_new_item' => __('Ajouter un thème', 'wp-seed-content-kit'),
            'edit_item' => __('Modifier le thème', 'wp-seed-content-kit'),
            'new_item' => __('Nouveau thème', 'wp-seed-content-kit'),
            'search_items' => __('Rechercher des thèmes', 'wp-seed-content-kit'),
            'not_found' => __('Aucun thème trouvé', 'wp-seed-content-kit'),
            'all_items' => __('Tous les thèmes', 'wp-seed-content-kit'),
            'featured_image' => __('Image du thème', 'wp-seed-content-kit'),
            'set_featured_image' => __('Choisir l’image du thème', 'wp-seed-content-kit'),
            'remove_featured_image' => __('Retirer l’image du thème', 'wp-seed-content-kit'),
        ),
        'description' => __('Thèmes canoniques de parcours.', 'wp-seed-content-kit'),
        'public' => false,
        'publicly_queryable' => false,
        'exclude_from_search' => true,
        'show_ui' => true,
        'show_in_menu' => false,
        'show_in_rest' => true,
        'show_in_admin_bar' => false,
        'has_archive' => false,
        'rewrite' => false,
        'query_var' => false,
        'can_export' => true,
        'supports' => array('title', 'editor', 'excerpt', 'thumbnail', 'page-attributes', 'revisions'),
        'capability_type' => array('seed_theme', 'seed_themes'),
        'capabilities' => wp_seed_content_kit_get_capability_map('formation_themes'),
        'map_meta_cap' => true,
    ));

    if (function_exists('wp_seed_content_kit_register_manual_order_for_post_type')) {
        wp_seed_content_kit_register_manual_order_for_post_type('seed_theme');
    }
}

function wp_seed_content_formation_register_technical_post_type($post_type, $labels, $capability_family)
{
    register_post_type($post_type, array(
        'labels' => $labels,
        'public' => false,
        'publicly_queryable' => false,
        'exclude_from_search' => true,
        'show_ui' => false,
        'show_in_menu' => false,
        'show_in_rest' => false,
        'show_in_admin_bar' => false,
        'has_archive' => false,
        'rewrite' => false,
        'query_var' => false,
        'can_export' => true,
        'supports' => array('title', 'page-attributes', 'revisions'),
        'capability_type' => array($post_type, $post_type . 's'),
        'capabilities' => wp_seed_content_kit_get_capability_map($capability_family),
        'map_meta_cap' => true,
    ));
}

function wp_seed_content_formation_register_post_types()
{
    wp_seed_content_formation_register_program_post_type();
    wp_seed_content_formation_register_theme_post_type();
    wp_seed_content_formation_register_technical_post_type(
        'seed_section',
        array('name' => __('Sections', 'wp-seed-content-kit'), 'singular_name' => __('Section', 'wp-seed-content-kit')),
        'formation_sections'
    );
    wp_seed_content_formation_register_technical_post_type(
        'seed_schedule',
        array('name' => __('Programmations annuelles', 'wp-seed-content-kit'), 'singular_name' => __('Programmation annuelle', 'wp-seed-content-kit')),
        'formation_schedules'
    );
}
