<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_get_quote_template_placeholders($post_id)
{
    return array(
        'quote' => array(
            'type' => 'textarea',
            'value' => wp_seed_content_get_meta($post_id, '_seed_quote_text'),
        ),
        'author' => array(
            'type' => 'text',
            'value' => wp_seed_content_get_meta($post_id, '_seed_quote_author'),
        ),
        'era' => array(
            'type' => 'text',
            'value' => wp_seed_content_get_meta($post_id, '_seed_quote_era'),
        ),
        'source' => array(
            'type' => 'text',
            'value' => wp_seed_content_get_meta($post_id, '_seed_quote_source'),
        ),
    );
}
