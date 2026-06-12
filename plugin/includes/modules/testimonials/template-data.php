<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_get_testimonial_template_placeholders($post_id)
{
    return array(
        'photo' => array(
            'type' => 'html',
            'value' => has_post_thumbnail($post_id)
                ? get_the_post_thumbnail($post_id, 'thumbnail', array('class' => 'seed-testimonials__photo-image', 'loading' => 'lazy'))
                : '',
        ),
        'name' => array(
            'type' => 'text',
            'value' => wp_seed_content_get_meta($post_id, '_seed_testimonial_name'),
        ),
        'text' => array(
            'type' => 'textarea',
            'value' => wp_seed_content_get_meta($post_id, '_seed_testimonial_text'),
        ),
    );
}
