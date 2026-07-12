<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_get_quote_template_placeholders($post_id)
{
    $quote_data = wp_seed_content_get_quote_data($post_id);

    return array(
        'quote' => array(
            'type' => 'textarea',
            'value' => isset($quote_data['quote']) ? (string) $quote_data['quote'] : '',
        ),
        'author' => array(
            'type' => 'text',
            'value' => isset($quote_data['author']) ? (string) $quote_data['author'] : '',
        ),
        'era' => array(
            'type' => 'text',
            'value' => isset($quote_data['era']) ? (string) $quote_data['era'] : '',
        ),
        'source' => array(
            'type' => 'text',
            'value' => isset($quote_data['source']) ? (string) $quote_data['source'] : '',
        ),
    );
}
