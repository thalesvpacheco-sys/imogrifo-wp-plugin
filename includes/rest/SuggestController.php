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

            $status_tax = taxonomy_exists('status_obra') ? 'status_obra' : 'status';

            foreach ( $q->posts as $pid ) {
                $cidade_terms = get_the_terms($pid, 'cidade');
                $status_terms = get_the_terms($pid, $status_tax);

                $out[] = [
                    'label'  => get_the_title($pid),
                    'link'   => get_permalink($pid),
                    'cidade' => (!is_wp_error($cidade_terms) && $cidade_terms) ? reset($cidade_terms)->name : '',
                    'status' => (!is_wp_error($status_terms) && $status_terms) ? reset($status_terms)->name : '',
                ];
            }
            wp_reset_postdata();
        }

        wp_send_json( $out );
    }
}
