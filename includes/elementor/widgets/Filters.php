<?php
declare(strict_types=1);

namespace ImoGrifo\Elementor\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if ( ! defined('ABSPATH') ) { exit; }

final class Filters extends Widget_Base
{
    public function get_name() { return 'imo-filters'; }
    public function get_title() { return __('Imo Grifo: Filters (Archive)', 'imo-grifo'); }
    public function get_icon() { return 'eicon-filter'; }
    public function get_categories() { return ['imo-grifo']; }

    // >>> garante assets no editor e no frontend
    public function get_style_depends() { return ['imo-filters-css']; }
    public function get_script_depends() { return ['imo-filters-js']; }

    protected function register_controls() {
        $this->start_controls_section('section_content', [ 'label' => __('Conteúdo', 'imo-grifo') ]);

        $this->add_control('placeholder', [
            'label'   => __('Placeholder (busca)', 'imo-grifo'),
            'type'    => Controls_Manager::TEXT,
            'default' => __('Encontre um Empreendimento', 'imo-grifo'),
        ]);

        $this->add_control('show_cidade', [
            'label'        => __('Mostrar Localização (cidade)', 'imo-grifo'),
            'type'         => Controls_Manager::SWITCHER,
            'return_value' => 'yes',
            'default'      => 'yes',
        ]);

        $this->add_control('show_status', [
            'label'        => __('Mostrar Status (obra)', 'imo-grifo'),
            'type'         => Controls_Manager::SWITCHER,
            'return_value' => 'yes',
            'default'      => 'yes',
        ]);

        $this->end_controls_section();

        $this->start_controls_section('section_style', [
            'label' => __('Estilo', 'imo-grifo'),
            'tab'   => Controls_Manager::TAB_STYLE,
        ]);

        $this->add_control('bar_bg', [
            'label'     => __('Fundo da barra', 'imo-grifo'),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#f0ebf5',
            'selectors' => [ '{{WRAPPER}} .rn-search-wrapper' => 'background-color: {{VALUE}};' ],
        ]);

        $this->add_control('pill_radius', [
            'label'     => __('Raio da barra (px)', 'imo-grifo'),
            'type'      => Controls_Manager::SLIDER,
            'range'     => [ 'px' => [ 'min'=>0, 'max'=>64 ] ],
            'default'   => [ 'size' => 40 ],
            'selectors' => [ '{{WRAPPER}} .rn-search-wrapper' => 'border-radius: {{SIZE}}{{UNIT}};' ],
        ]);

        $this->end_controls_section();
    }

    public function render() {
        // Localiza script (ajax/nonce)
        wp_localize_script('imo-filters-js', 'imoFilters', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('imo_suggest'),
        ]);

        $s         = $this->get_settings_for_display();
        $action    = get_post_type_archive_link('empreendimento');
        if ( ! $action ) { $action = home_url('/'); }

        // Persistência (valores atuais)
        $q_val   = isset($_GET['q']) ? sanitize_text_field( wp_unslash($_GET['q']) ) : '';
        $cid_cur = isset($_GET['cidade']) ? sanitize_text_field( wp_unslash($_GET['cidade']) ) : '';
        $st_cur  = isset($_GET['status_obra']) ? sanitize_text_field( wp_unslash($_GET['status_obra']) )
                  : ( isset($_GET['status']) ? sanitize_text_field( wp_unslash($_GET['status']) ) : '' );

        // Taxonomias: compat -- usa status_obra se existir; senão cai pro status antigo
        $status_tax = taxonomy_exists('status_obra') ? 'status_obra' : ( taxonomy_exists('status') ? 'status' : 'status_obra' );

        // Opções de taxonomia (hide_empty = false p/ sempre mostrar)
        $cidades = get_terms([ 'taxonomy'=>'cidade',     'hide_empty'=>false ]);
        $status  = get_terms([ 'taxonomy'=>$status_tax,  'hide_empty'=>false ]);

        echo '<form class="imo-filters-form" action="'. esc_url($action) .'" method="get" role="search">';
        echo '<div class="rn-search-wrapper imo-rn-filter" data-scope="1">';

        // Search
        echo '<input type="text" class="rn-search" name="q" value="'. esc_attr($q_val) .'" placeholder="'. esc_attr($s['placeholder']) .'" aria-label="'. esc_attr__('Buscar por nome do empreendimento', 'imo-grifo') .'">';

        // Cidade
        if ( $s['show_cidade'] === 'yes' ) {
            echo '<select class="rn-localizacao" name="cidade" aria-label="'. esc_attr__('Filtrar por localização', 'imo-grifo') .'">';
            echo '<option value="">'. esc_html__('Localização', 'imo-grifo') .'</option>';
            if ( ! is_wp_error($cidades) && $cidades ) {
                foreach ( $cidades as $c ) {
                    printf(
                        '<option value="%s"%s>%s</option>',
                        esc_attr($c->slug),
                        selected($cid_cur, $c->slug, false),
                        esc_html($c->name)
                    );
                }
            }
            echo '</select>';
        }

        // Status (usa nome do parâmetro igual ao slug da taxonomia em uso)
        if ( $s['show_status'] === 'yes' ) {
            echo '<select class="rn-status" name="'. esc_attr($status_tax) .'" aria-label="'. esc_attr__('Filtrar por status da obra', 'imo-grifo') .'">';
            echo '<option value="">'. esc_html__('Status', 'imo-grifo') .'</option>';
            if ( ! is_wp_error($status) && $status ) {
                foreach ( $status as $st ) {
                    printf(
                        '<option value="%s"%s>%s</option>',
                        esc_attr($st->slug),
                        selected($st_cur, $st->slug, false),
                        esc_html($st->name)
                    );
                }
            }
            echo '</select>';
        }

        // Botão (ícone)
        echo '<button class="rn-search-btn" type="submit" aria-label="'. esc_attr__('Aplicar filtros', 'imo-grifo') .'">
                <svg width="22" height="22" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                    <path d="M16.6 18L10.3 11.7C9.8 12.1 9.225 12.4167 8.575 12.65C7.925 12.8833 7.23333 13 6.5 13C4.68333 13 3.14583 12.3708 1.8875 11.1125C0.629167 9.85417 0 8.31667 0 6.5C0 4.68333 0.629167 3.14583 1.8875 1.8875C3.14583 0.629167 4.68333 0 6.5 0C8.31667 0 9.85417 0.629167 11.1125 1.8875C12.3708 3.14583 13 4.68333 13 6.5C13 7.23333 12.8833 7.925 12.65 8.575C12.4167 9.225 12.1 9.8 11.7 10.3L18 16.6L16.6 18ZM6.5 11C7.75 11 8.8125 10.5625 9.6875 9.6875C10.5625 8.8125 11 7.75 11 6.5C11 5.25 10.5625 4.1875 9.6875 3.3125C8.8125 2.4375 7.75 2 6.5 2C5.25 2 4.1875 2.4375 3.3125 3.3125C2.4375 4.1875 2 5.25 2 6.5C2 7.75 2.4375 8.8125 3.3125 9.6875C4.1875 10.5625 5.25 11 6.5 11Z" fill="#EA932A"/>
                </svg>
              </button>';

        // Autocomplete
        echo '<ul class="rn-autocomplete" aria-label="'. esc_attr__('Sugestões', 'imo-grifo') .'" role="listbox"></ul>';

        echo '</div>'; // wrapper
        echo '</form>';
    }
}
