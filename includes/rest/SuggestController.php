<?php
declare(strict_types=1);

namespace ImoGrifo\Rest;

if ( ! defined('ABSPATH') ) { exit; }

final class SuggestController {

    public static function boot(): void {
        add_action('wp_ajax_imo_suggest',       [__CLASS__, 'ajax_suggest']);
        add_action('wp_ajax_nopriv_imo_suggest',[__CLASS__, 'ajax_suggest']);
    }

    public static function ajax_suggest(): void {
        check_ajax_referer('imo_suggest', 'nonce');

        $term = isset($_GET['term']) ? sanitize_text_field( wp_unslash($_GET['term']) ) : '';
        $max  = 5;

        $out = [];
        if ( strlen($term) >= 3 ) {
            $q = new \WP_Query([
                'post_type'      => 'empreendimento',
                's'              => $term,
                'posts_per_page' => $max,
                'no_found_rows'  => true,
                'fields'         => 'ids',
            ]);

            foreach ( $q->posts as $pid ) {
                $out[] = [
                    'label' => get_the_title($pid),
                    'link'  => get_permalink($pid),
                ];
            }
            wp_reset_postdata();
        }

        wp_send_json( $out );
    }
}
