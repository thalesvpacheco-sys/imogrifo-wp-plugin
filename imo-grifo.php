<?php
/**
 * Plugin Name: Imo Grifo
 * Description: CPT Empreendimentos + Taxonomias + Widgets/Tags Elementor (sem Editar IMO).
 * Version: 0.3.0
 * Author: Grifo Agency
 * Text Domain: imo-grifo
 */

if (! defined('ABSPATH')) { exit; }

// Constantes seguras
if (! defined('IMOGRIFO_FILE')) { define('IMOGRIFO_FILE', __FILE__); }
if (! defined('IMOGRIFO_PATH')) { define('IMOGRIFO_PATH', plugin_dir_path(__FILE__)); }
if (! defined('IMOGRIFO_URL'))  { define('IMOGRIFO_URL',  plugin_dir_url(__FILE__)); }
if (! defined('IMOGRIFO_VER'))  { define('IMOGRIFO_VER',  '0.3.1'); }

// Carregar núcleo
$core = IMOGRIFO_PATH . 'includes/Plugin.php';
if (is_readable($core)) {
    require_once $core;
    if (class_exists('\ImoGrifo\Plugin')) {
        new \ImoGrifo\Plugin();
    } else {
        add_action('admin_notices', static function () {
            echo '<div class="notice notice-error"><p><strong>Imo Grifo:</strong> Classe núcleo não encontrada.</p></div>';
        });
    }
} else {
    add_action('admin_notices', static function () {
        echo '<div class="notice notice-error"><p><strong>Imo Grifo:</strong> includes/Plugin.php ausente.</p></div>';
    });
}
