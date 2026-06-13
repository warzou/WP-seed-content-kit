<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_get_testimonial_template_placeholders($post_id)
{
    $thumbnail_id = get_post_thumbnail_id($post_id);
    $name = wp_seed_content_get_meta($post_id, '_seed_testimonial_name');
    $text = wp_seed_content_get_meta($post_id, '_seed_testimonial_text');
    $photo_alt = '';
    if ($thumbnail_id) {
        $photo_alt = (string) get_post_meta((int) $thumbnail_id, '_wp_attachment_image_alt', true);
    }
    if (!is_string($photo_alt) || '' === trim($photo_alt)) {
        $photo_alt = is_string($name) ? $name : '';
    }

    return array(
        'photo' => array(
            'type' => 'html',
            'value' => has_post_thumbnail($post_id)
                ? get_the_post_thumbnail($post_id, 'thumbnail', array('class' => 'seed-testimonials__photo-image', 'loading' => 'lazy'))
                : '',
        ),
        'name' => array(
            'type' => 'text',
            'value' => $name,
        ),
        'photo_url' => array(
            'type' => 'text',
            'value' => $thumbnail_id ? wp_get_attachment_url((int) $thumbnail_id) : '',
        ),
        'text' => array(
            'type' => 'textarea',
            'value' => $text,
        ),
        'photo_alt' => array(
            'type' => 'text',
            'value' => $photo_alt,
        ),
    );
}
