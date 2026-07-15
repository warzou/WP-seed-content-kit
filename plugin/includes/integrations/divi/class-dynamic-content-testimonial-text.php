<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Experimental Divi 5 Dynamic Content source for testimonial text.
 */
class WP_Seed_Content_Divi_Dynamic_Content_Testimonial_Text extends WP_Seed_Content_Divi_Dynamic_Content_Testimonial_Base
{
    public function get_name(): string
    {
        return 'wp_seed_content_testimonial_text';
    }

    public function get_label(): string
    {
        return __('Texte', 'wp-seed-content-kit');
    }

    protected function get_dynamic_data_field_id(): string
    {
        return 'testimonial.text';
    }
}
