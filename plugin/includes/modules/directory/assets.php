<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_directory_register_public_assets()
{
    wp_register_style(
        'wp-seed-directory',
        WP_SEED_CONTENT_KIT_URL . 'assets/css/directory.css',
        array(),
        WP_SEED_CONTENT_KIT_VERSION
    );
    wp_register_style(
        'wp-seed-directory-card',
        WP_SEED_CONTENT_KIT_URL . 'assets/css/directory-card.css',
        array('wp-seed-directory'),
        WP_SEED_CONTENT_KIT_VERSION
    );
}
add_action('wp_enqueue_scripts', 'wp_seed_content_directory_register_public_assets');

function wp_seed_content_directory_enqueue_structure_assets()
{
    if (!wp_style_is('wp-seed-directory', 'registered')) {
        wp_seed_content_directory_register_public_assets();
    }
    wp_enqueue_style('wp-seed-directory');
}

function wp_seed_content_directory_enqueue_native_card_assets()
{
    if (!wp_style_is('wp-seed-directory-card', 'registered')) {
        wp_seed_content_directory_register_public_assets();
    }
    wp_enqueue_style('wp-seed-directory-card');
}
