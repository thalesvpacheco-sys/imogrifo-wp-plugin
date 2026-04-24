<?php
namespace ImoGrifo\Elementor\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if (! defined('ABSPATH')) { exit; }

final class CTA extends Widget_Base
{
    public function get_name(){ return 'imo-cta'; }
    public function get_title(){ return __('Imo Grifo: CTA', 'imo-grifo'); }
    public function get_icon(){ return 'eicon-button'; }
    public function get_categories(){ return ['imo-grifo']; }

    protected function register_controls()
    {
        $this->start_controls_section('section_source', [
            'label' => __('Fonte de dados', 'imo-grifo'),
        ]);
        $this->add_control('source', [
            'label' => __('Fonte', 'imo-grifo'),
            'type' => Controls_Manager::SELECT,
            'options' => [
                'meta'   => __('Editar IMO (padrão)', 'imo-grifo'),
                'manual' => __('Manual (override)', 'imo-grifo'),
            ],
            'default' => 'meta',
        ]);
        $this->end_controls_section();

        $this->start_controls_section('section_manual', [
            'label' => __('Dados (Manual)', 'imo-grifo'),
            'condition' => ['source' => 'manual'],
        ]);
        $this->add_control('manual_label', [
            'label' => __('Texto do botão', 'imo-grifo'),
            'type'  => Controls_Manager::TEXT,
            'default' => '',
        ]);
        $this->add_control('manual_url', [
            'label' => __('URL', 'imo-grifo'),
            'type'  => Controls_Manager::URL,
            'default' => ['url' => ''],
        ]);
        $this->end_controls_section();
    }

    public function render()
    {
        wp_enqueue_style('imo-grifo-frontend', IMOGRIFO_URL . '/assets/css/frontend.css', [], IMOGRIFO_VER);
        $post_id = get_the_ID();
        $s = $this->get_settings_for_display();

        if ($s['source'] === 'manual') {
            $label = $s['manual_label'] ?? '';
            $url   = is_array($s['manual_url']) ? ($s['manual_url']['url'] ?? '') : '';
        } else {
            $label = get_post_meta($post_id, '_imo_cta_label', true);
            $url   = get_post_meta($post_id, '_imo_cta_url', true);
        }
        if (! $label) { $label = __('Saiba mais', 'imo-grifo'); }

        $url_attr = $url ? ' href="'. esc_url($url) .'"' : '';
        ?>
        <div class="imo-cta">
            <a class="imo-cta__btn"<?php echo $url_attr; ?>>
                <?php echo esc_html($label); ?>
            </a>
        </div>
        <?php
    }
}
