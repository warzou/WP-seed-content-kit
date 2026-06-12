<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_register_assets()
{
    wp_register_style(
        'wp-seed-content-kit',
        WP_SEED_CONTENT_KIT_URL . 'assets/css/seed-content-kit.css',
        array(),
        WP_SEED_CONTENT_KIT_VERSION
    );
}
add_action('wp_enqueue_scripts', 'wp_seed_content_register_assets');

function wp_seed_content_enqueue_assets()
{
    wp_enqueue_style('wp-seed-content-kit');
}
