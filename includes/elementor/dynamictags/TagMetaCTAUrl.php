<?php
namespace ImoGrifo\DynamicTags;

use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module;

if (! defined('ABSPATH')) { exit; }

/** @deprecated since 0.4.0 — ligado ao widget FormCTA depreciado (DR-11). Remover na F2-05. */
final class TagMetaCTAUrl extends Tag
{
    public function get_name(){ return 'imo-meta-cta-url'; }
    public function get_title(){ return __('Imo: CTA URL [deprecated]', 'imo-grifo'); }
    public function get_group(){ return 'post'; }
    public function get_categories()
    {
        $cat = defined('Elementor\Modules\DynamicTags\Module::URL_CATEGORY')
            ? Module::URL_CATEGORY
            : 'url';
        return [ $cat ];
    }

    protected function register_controls(){}

    public function render()
    {
        $post_id = get_the_ID();
        $url = get_post_meta($post_id, '_imo_cta_url', true);
        echo esc_url($url);
    }
}
