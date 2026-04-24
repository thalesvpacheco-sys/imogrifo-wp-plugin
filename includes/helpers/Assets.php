<?php
namespace ImoGrifo\Helpers;

if (!defined('ABSPATH')) { exit; }

final class Assets {
    public static function boot(): void {
        add_action('wp_enqueue_scripts', [__CLASS__, 'front']);
        // carrega dentro do editor do Elementor também
        add_action('elementor/editor/before_enqueue_scripts', [__CLASS__, 'front']);
    }

    public static function front(): void {
        $base = trailingslashit(defined('IMOGRIFO_URL') ? IMOGRIFO_URL : plugin_dir_url(__FILE__) . '../../');
        $path = trailingslashit(defined('IMOGRIFO_PATH') ? IMOGRIFO_PATH : plugin_dir_path(__FILE__) . '../../');

        // CSS
        $css_file = $path . 'assets/css/frontend.css';
        $css_ver  = file_exists($css_file) ? filemtime($css_file) : (defined('IMOGRIFO_VERSION') ? IMOGRIFO_VERSION : time());
        wp_register_style('imo-grifo-frontend', $base . 'assets/css/frontend.css', [], $css_ver);
        wp_enqueue_style('imo-grifo-frontend');

        // JS (se tiver)
        $js_file = $path . 'assets/js/frontend.js';
        if (file_exists($js_file)) {
            $js_ver = filemtime($js_file);
            wp_register_script('imo-grifo-frontend', $base . 'assets/js/frontend.js', ['jquery'], $js_ver, true);
            wp_enqueue_script('imo-grifo-frontend');
        }
    }
}
