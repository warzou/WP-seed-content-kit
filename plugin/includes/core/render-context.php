<?php
/**
 * Request-scoped render context used by internal collection renderers.
 *
 * @package WPSeedContentKit
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Internal bounded stack for nested card rendering.
 */
final class WP_Seed_Content_Render_Context
{
    const MAX_DEPTH = 16;

    /**
     * @var array
     */
    private static $stack = array();

    /**
     * Dynamic fields already resolved during this request, including Divi cache hits.
     *
     * @var array
     */
    private static $resolved_dynamic = array();

    /**
     * Push one validated render context.
     *
     * @param array $context Raw context.
     *
     * @return bool
     */
    public static function push($context)
    {
        $context = self::normalize($context);
        if (empty($context) || count(self::$stack) >= self::MAX_DEPTH) {
            return false;
        }

        foreach (self::$stack as $active_context) {
            if ($active_context['render_id'] === $context['render_id']) {
                return false;
            }
        }

        $context['depth'] = count(self::$stack) + 1;
        self::$stack[] = $context;

        return true;
    }

    /**
     * Pop the current context when it matches the expected render.
     *
     * @param string $render_id Expected render identifier.
     *
     * @return bool
     */
    public static function pop($render_id)
    {
        if (empty(self::$stack)) {
            return false;
        }

        $current = end(self::$stack);
        if (!is_array($current) || $current['render_id'] !== (string) $render_id) {
            return false;
        }

        array_pop(self::$stack);

        return true;
    }

    /**
     * Return the current context.
     *
     * @return array
     */
    public static function current()
    {
        if (empty(self::$stack)) {
            return array();
        }

        $current = end(self::$stack);

        return is_array($current) ? $current : array();
    }

    /**
     * Register one Content Kit Dynamic Content field expected by the current card.
     *
     * @param string $name Dynamic Content option name.
     *
     * @return bool
     */
    public static function expect_dynamic($name)
    {
        $index = count(self::$stack) - 1;
        $name = sanitize_key((string) $name);
        if ($index < 0 || '' === $name) {
            return false;
        }

        $identity = self::dynamic_identity(
            self::$stack[$index]['post_id'],
            $name
        );
        self::$stack[$index]['expected_dynamic'][$identity] = $name;
        if (isset(self::$resolved_dynamic[$identity])) {
            self::$stack[$index]['resolved_dynamic'][$identity] = true;
        }

        return true;
    }

    /**
     * Record one field resolved for the current card by the matching provider.
     *
     * @param string $name    Dynamic Content option name.
     * @param mixed  $post_id Provider post ID.
     *
     * @return bool
     */
    public static function resolve_dynamic($name, $post_id)
    {
        $index = count(self::$stack) - 1;
        $name = sanitize_key((string) $name);
        $post_id = absint($post_id);
        if (
            $index < 0
            || '' === $name
            || $post_id <= 0
            || self::$stack[$index]['post_id'] !== $post_id
        ) {
            return false;
        }

        $identity = self::dynamic_identity($post_id, $name);
        if (!isset(self::$stack[$index]['expected_dynamic'][$identity])) {
            return false;
        }

        self::$stack[$index]['resolved_dynamic'][$identity] = true;
        self::$resolved_dynamic[$identity] = true;

        return true;
    }

    /**
     * Confirm that every Dynamic Content field expected by this card resolved.
     *
     * A static Layout has no expected fields and is valid by this signal.
     *
     * @return bool
     */
    public static function dynamic_resolution_complete()
    {
        $index = count(self::$stack) - 1;
        if ($index < 0) {
            return true;
        }

        foreach (self::$stack[$index]['expected_dynamic'] as $identity => $name) {
            if (!isset(self::$stack[$index]['resolved_dynamic'][$identity])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Expose aggregate signal counts for tests and local render decisions.
     *
     * @return array
     */
    public static function dynamic_resolution_state()
    {
        $index = count(self::$stack) - 1;
        if ($index < 0) {
            return array('expected' => 0, 'resolved' => 0);
        }

        return array(
            'expected' => count(self::$stack[$index]['expected_dynamic']),
            'resolved' => count(self::$stack[$index]['resolved_dynamic']),
        );
    }

    /**
     * Return the active stack depth.
     *
     * @return int
     */
    public static function depth()
    {
        return count(self::$stack);
    }

    /**
     * Clear the request stack. Reserved for test and shutdown cleanup.
     *
     * @return void
     */
    public static function reset()
    {
        self::$stack = array();
        self::$resolved_dynamic = array();
    }

    /**
     * Normalize the public-data identifiers used during rendering.
     *
     * @param mixed $context Raw context.
     *
     * @return array
     */
    private static function normalize($context)
    {
        if (!is_array($context)) {
            return array();
        }

        $module = isset($context['module']) ? sanitize_key((string) $context['module']) : '';
        $post_id = isset($context['post_id']) ? absint($context['post_id']) : 0;
        $template_id = isset($context['template_id']) ? absint($context['template_id']) : 0;
        $layout_id = isset($context['layout_id']) ? absint($context['layout_id']) : 0;
        $render_id = isset($context['render_id'])
            ? sanitize_text_field((string) $context['render_id'])
            : '';

        if ('' === $module || $post_id <= 0 || '' === $render_id) {
            return array();
        }

        return array(
            'module' => $module,
            'post_id' => $post_id,
            'template_id' => $template_id,
            'layout_id' => $layout_id,
            'render_id' => $render_id,
            'depth' => 0,
            'expected_dynamic' => array(),
            'resolved_dynamic' => array(),
        );
    }

    /**
     * Build the identity shared with Divi's request-local Dynamic Data cache.
     *
     * @param int    $post_id Item post ID.
     * @param string $name    Dynamic Content option name.
     *
     * @return string
     */
    private static function dynamic_identity($post_id, $name)
    {
        return absint($post_id) . '|' . sanitize_key((string) $name);
    }
}

/**
 * Build a deterministic recursion identifier for one item render.
 *
 * @param string $module      Module name.
 * @param int    $post_id     Item post ID.
 * @param int    $template_id Template post ID.
 * @param int    $layout_id   Divi Layout ID.
 *
 * @return string
 */
function _wp_seed_content_render_context_id($module, $post_id, $template_id = 0, $layout_id = 0)
{
    return hash(
        'sha256',
        sanitize_key((string) $module)
        . '|' . absint($post_id)
        . '|' . absint($template_id)
        . '|' . absint($layout_id)
    );
}
