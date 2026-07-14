<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Experimental Divi 5 Dynamic Content source for quote sources.
 */
class WP_Seed_Content_Divi_Dynamic_Content_Quote_Source extends WP_Seed_Content_Divi_Dynamic_Content_Quote_Base
{
    public function get_name(): string
    {
        return 'wp_seed_content_quote_source';
    }

    public function get_label(): string
    {
        return __('Source', 'wp-seed-content-kit');
    }

    protected function get_dynamic_data_field_id(): string
    {
        return 'quote.source';
    }
}
