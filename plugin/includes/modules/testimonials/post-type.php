<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_register_testimonial_post_type()
{
    $labels = array(
        'name' => __('Testimonials', 'wp-seed-content-kit'),
        'singular_name' => __('Testimonial', 'wp-seed-content-kit'),
        'menu_name' => __('Testimonials', 'wp-seed-content-kit'),
        'add_new' => __('Add New', 'wp-seed-content-kit'),
        'add_new_item' => __('Add Testimonial', 'wp-seed-content-kit'),
        'edit_item' => __('Edit Testimonial', 'wp-seed-content-kit'),
        'new_item' => __('New Testimonial', 'wp-seed-content-kit'),
        'view_item' => __('View Testimonial', 'wp-seed-content-kit'),
        'search_items' => __('Search Testimonials', 'wp-seed-content-kit'),
        'not_found' => __('No testimonials found', 'wp-seed-content-kit'),
        'not_found_in_trash' => __('No testimonials found in Trash', 'wp-seed-content-kit'),
    );

    register_post_type('seed_testimonial', array(
        'labels' => $labels,
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
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
