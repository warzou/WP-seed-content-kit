<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Immutable result returned by the public Template Extension API.
 */
final class WP_Seed_Content_Kit_Render_Result
{
    private $success;
    private $html;
    private $code;
    private $template_id;
    private $assets;

    public function __construct($success, $html, $code, $template_id = 0, array $assets = array())
    {
        $this->success = (bool) $success;
        $this->html = $this->success ? (string) $html : '';
        $this->code = sanitize_key((string) $code);
        $this->template_id = absint($template_id);
        $this->assets = $this->success
            ? array(
                'styles' => isset($assets['styles']) && is_array($assets['styles']) ? array_values($assets['styles']) : array(),
                'scripts' => isset($assets['scripts']) && is_array($assets['scripts']) ? array_values($assets['scripts']) : array(),
            )
            : array('styles' => array(), 'scripts' => array());
    }

    public function is_success()
    {
        return $this->success;
    }

    public function get_html()
    {
        return $this->html;
    }

    public function get_code()
    {
        return $this->code;
    }

    public function get_template_id()
    {
        return $this->template_id;
    }

    public function get_assets()
    {
        return $this->assets;
    }
}

function wp_seed_content_kit_render_success($html, $template_id, array $assets = array())
{
    return new WP_Seed_Content_Kit_Render_Result(true, $html, 'success', $template_id, $assets);
}

function wp_seed_content_kit_render_failure($code, $template_id = 0)
{
    return new WP_Seed_Content_Kit_Render_Result(false, '', $code, $template_id, array());
}
