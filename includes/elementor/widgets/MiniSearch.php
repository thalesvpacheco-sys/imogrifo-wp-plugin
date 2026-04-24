<?php
namespace ImoGrifo\Elementor\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if (! defined('ABSPATH')) { exit; }

final class MiniSearch extends Widget_Base
{
    public function get_name() { return 'imo-mini-search'; }
    public function get_title() { return __('Imo Grifo: Mini Search', 'imo-grifo'); }
    public function get_icon() { return 'eicon-search'; }
    public function get_categories() { return [ 'imo-grifo' ]; }
    public function get_keywords() { return [ 'search', 'mini', 'empreendimento', 'imo' ]; }

    protected function register_controls()
    {
        $this->start_controls_section('section_content', [
            'label' => __('Conteúdo', 'imo-grifo'),
        ]);

        $this->add_control('placeholder', [
            'label' => __('Placeholder', 'imo-grifo'),
            'type'  => Controls_Manager::TEXT,
            'default' => __('Encontre um Empreendimento', 'imo-grifo'),
        ]);

        $this->add_control('limit', [
            'label' => __('Sugestões (máx.)', 'imo-grifo'),
            'type'  => Controls_Manager::NUMBER,
            'min' => 1, 'max' => 10, 'step' => 1,
            'default' => 5,
            'description' => __('Usado no autocomplete via REST.', 'imo-grifo'),
        ]);

        $this->add_control('show_button', [
            'label' => __('Mostrar botão', 'imo-grifo'),
            'type'  => Controls_Manager::SWITCHER,
            'return_value' => 'yes',
            'default' => 'yes',
        ]);

        $this->end_controls_section();
    }

    public function render()
    {
        $s = $this->get_settings_for_display();

        // Action = archive do CPT
        $action = get_post_type_archive_link('empreendimento');
        if (! $action) { $action = home_url('/'); }

        // Valor atual (persistência)
        $q = isset($_GET['q']) ? sanitize_text_field(wp_unslash($_GET['q'])) : '';

        // Endpoint REST para sugestão
        $endpoint = rest_url('imo-grifo/v1/suggest');
        $limit    = (int) ($s['limit'] ?: 5);

        // Registrar/enfileirar JS do autocomplete (se existir no plugin)
        if (! wp_script_is('imo-mini-search', 'registered')) {
            wp_register_script(
                'imo-mini-search',
                IMOGRIFO_URL . 'assets/js/mini-search.js',
                ['jquery'],
                '1.0.0',
                true
            );
        }
        wp_enqueue_script('imo-mini-search');

        ?>
        <form class="imo-mini-search" action="<?php echo esc_url($action); ?>" method="get" role="search">
            <div class="imo-mini-search__group">
                <input
                    class="imo-mini-search__input"
                    type="search"
                    name="q"
                    value="<?php echo esc_attr($q); ?>"
                    placeholder="<?php echo esc_attr($s['placeholder']); ?>"
                    autocomplete="off"
                    data-endpoint="<?php echo esc_url($endpoint); ?>"
                    data-limit="<?php echo esc_attr($limit); ?>"
                />
                <?php if ($s['show_button'] === 'yes'): ?>
                    <button class="imo-mini-search__submit" type="submit" aria-label="<?php esc_attr_e('Buscar', 'imo-grifo'); ?>">
                        <span class="screen-reader-text"><?php esc_html_e('Buscar', 'imo-grifo'); ?></span>
                        🔎
                    </button>
                <?php endif; ?>
            </div>

            <!-- container para sugestões -->
            <div class="imo-mini-search__suggestions" hidden></div>
        </form>
        <?php
    }
}
