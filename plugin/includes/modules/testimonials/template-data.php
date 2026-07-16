<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_get_testimonial_template_placeholders($post_id)
{
    $testimonial_data = wp_seed_content_get_testimonial_data($post_id);
    $name = isset($testimonial_data['name']) ? (string) $testimonial_data['name'] : '';
    $text = isset($testimonial_data['text']) ? (string) $testimonial_data['text'] : '';
    $context = isset($testimonial_data['context']) ? (string) $testimonial_data['context'] : '';
    $date = isset($testimonial_data['testimonial_date']) ? wp_seed_content_format_date($testimonial_data['testimonial_date']) : '';
    $photo = isset($testimonial_data['photo']) && is_array($testimonial_data['photo']) ? $testimonial_data['photo'] : null;
    $photo_id = is_array($photo) && isset($photo['id']) ? absint($photo['id']) : 0;
    $photo_alt = is_array($photo) && isset($photo['alt']) ? (string) $photo['alt'] : '';
    if ('' === trim($photo_alt)) {
        $photo_alt = $name;
    }

    return array(
        'photo' => array(
            'type' => 'html',
            'value' => $photo_id && has_post_thumbnail($post_id)
                ? get_the_post_thumbnail($post_id, 'thumbnail', array('class' => 'seed-testimonials__photo-image', 'loading' => 'lazy'))
                : '',
        ),
        'name' => array(
            'type' => 'text',
            'value' => $name,
        ),
        'photo_url' => array(
            'type' => 'text',
            'value' => $photo_id ? wp_get_attachment_url($photo_id) : '',
        ),
        'text' => array(
            'type' => 'textarea',
            'value' => $text,
        ),
        'photo_alt' => array(
            'type' => 'text',
            'value' => $photo_alt,
        ),
        'context' => array(
            'type' => 'text',
            'value' => $context,
        ),
        'date' => array(
            'type' => 'text',
            'value' => $date,
        ),
    );
}
