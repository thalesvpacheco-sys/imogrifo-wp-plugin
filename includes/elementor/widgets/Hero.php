<?php
namespace ImoGrifo\Elementor\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if (! defined('ABSPATH')) { exit; }

final class Hero extends Widget_Base
{
    public function get_name(){ return 'imo-hero'; }
    public function get_title(){ return __('Imo Grifo: Hero', 'imo-grifo'); }
    public function get_icon(){ return 'eicon-slider-push'; }
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
                'meta' => __('Editar IMO (padrão)', 'imo-grifo'),
                'manual' => __('Manual (override)', 'imo-grifo'),
            ],
            'default' => 'meta',
        ]);
        $this->end_controls_section();

        $this->start_controls_section('section_manual', [
            'label' => __('Dados (Manual)', 'imo-grifo'),
            'condition' => ['source' => 'manual'],
        ]);
        $this->add_control('manual_bg', [
            'label' => __('Imagem de fundo', 'imo-grifo'),
            'type'  => Controls_Manager::MEDIA,
        ]);
        $this->add_control('manual_title', [
            'label' => __('Título', 'imo-grifo'),
            'type'  => Controls_Manager::TEXT,
            'default' => '',
        ]);
        $this->add_control('manual_subtitle', [
            'label' => __('Subtítulo', 'imo-grifo'),
            'type'  => Controls_Manager::TEXTAREA,
            'default' => '',
        ]);
        $this->end_controls_section();
    }

    public function render()
    {
        // Enfileira CSS do front
        wp_enqueue_style('imo-grifo-frontend', IMOGRIFO_URL . '/assets/css/frontend.css', [], IMOGRIFO_VER);

        $post_id = get_the_ID();
        $s = $this->get_settings_for_display();

        if ($s['source'] === 'manual') {
            $bg = isset($s['manual_bg']['url']) ? $s['manual_bg']['url'] : '';
            $title = $s['manual_title'] ?? '';
            $subtitle = $s['manual_subtitle'] ?? '';
        } else {
            $img_id = (int) get_post_meta($post_id, '_imo_hero_bg_id', true);
            $bg = $img_id ? wp_get_attachment_image_url($img_id, '1920x1080') : '';
            $title = get_post_meta($post_id, '_imo_title', true);
            $subtitle = get_post_meta($post_id, '_imo_subtitle', true);
        }

        $bg_style = $bg ? ' style="background-image:url('. esc_url($bg) .')"' : '';
        ?>
        <section class="imo-hero"<?php echo $bg_style; ?>>
            <div class="imo-hero__inner">
                <?php if ($title) : ?>
                    <h1 class="imo-hero__title"><?php echo esc_html($title); ?></h1>
                <?php endif; ?>
                <?php if ($subtitle) : ?>
                    <p class="imo-hero__subtitle"><?php echo wp_kses_post($subtitle); ?></p>
                <?php endif; ?>
            </div>
        </section>
        <?php
    }
}
