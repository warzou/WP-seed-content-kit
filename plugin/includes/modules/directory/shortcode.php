<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_directory_shortcode($atts = array())
{
    return wp_seed_content_render_directory_collection($atts, true);
}
add_shortcode('seed_directory', 'wp_seed_content_directory_shortcode');
add_shortcode('wp_seed_directory', 'wp_seed_content_directory_shortcode');
