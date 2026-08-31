<?php
/**
 * Plugin Name: Imo Grifo
 * Description: CPT Empreendimentos + Taxonomias + Widgets/Tags Elementor (sem Editar IMO).
 * Version: 0.3.8
 * Author: Grifo Agency
 * Text Domain: imo-grifo
 */

if (! defined('ABSPATH')) { exit; }

// Constantes seguras
if (! defined('IMOGRIFO_FILE')) { define('IMOGRIFO_FILE', __FILE__); }
if (! defined('IMOGRIFO_PATH')) { define('IMOGRIFO_PATH', plugin_dir_path(__FILE__)); }
if (! defined('IMOGRIFO_URL'))  { define('IMOGRIFO_URL',  plugin_dir_url(__FILE__)); }
if (! defined('IMOGRIFO_VER'))  { define('IMOGRIFO_VER',  '0.3.8'); }

// Seletor CSS do título automático do tema a ocultar nas páginas de empreendimento
// (DR-15). Vazio = desativado. Ajuste aqui se o tema mudar e a classe for outra.
if (! defined('IMOGRIFO_HIDE_TITLE_SELECTOR')) { define('IMOGRIFO_HIDE_TITLE_SELECTOR', '.entry-title'); }

// Auto-update via GitHub Releases (evita reenvio manual de ZIP)
$imogrifo_puc = IMOGRIFO_PATH . 'includes/libs/plugin-update-checker/plugin-update-checker.php';
if (is_readable($imogrifo_puc) && ! class_exists('YahnisElsts\PluginUpdateChecker\v5p7\PucFactory')) {
    require_once $imogrifo_puc;
}
if (class_exists('YahnisElsts\PluginUpdateChecker\v5\PucFactory')) {
    $imogrifo_update_checker = \YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
        'https://github.com/thalesvpacheco-sys/imogrifo-wp-plugin/',
        IMOGRIFO_FILE,
        'imo-grifo'
    );
    $imogrifo_update_checker->setBranch('main');
}

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
