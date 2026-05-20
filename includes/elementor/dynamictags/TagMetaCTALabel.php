<?php
namespace ImoGrifo\DynamicTags;

use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module;

if (! defined('ABSPATH')) { exit; }

/** @deprecated since 0.4.0 — ligado ao widget FormCTA depreciado (DR-11). Remover na F2-05. */
final class TagMetaCTALabel extends Tag
{
    public function get_name(){ return 'imo-meta-cta-label'; }
    public function get_title(){ return __('Imo: CTA Label [deprecated]', 'imo-grifo'); }
    public function get_group(){ return 'post'; }
    public function get_categories()
    {
        $cat = defined('Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY')
            ? Module::TEXT_CATEGORY
            : 'text';
        return [ $cat ];
    }

    protected function register_controls(){}

    public function render()
    {
        $post_id = get_the_ID();
        $label = get_post_meta($post_id, '_imo_cta_label', true);
        echo esc_html($label);
    }
}
