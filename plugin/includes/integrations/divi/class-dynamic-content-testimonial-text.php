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
        return 'loop_wpsck_testimonial_full';
    }

    public function get_label(): string
    {
        return __('Témoignage complet', 'wp-seed-content-kit');
    }

    protected function get_dynamic_data_field_id(): string
    {
        return 'testimonial.text';
    }
}
